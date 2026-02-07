<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Get current user's team (gestionnaires for responsable).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isResponsable() && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $gestionnaires = $user->managedGestionnaires()
            ->select('users.id', 'users.name', 'users.email', 'users.phone', 'users.is_active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'gestionnaires' => $gestionnaires,
            'count' => $gestionnaires->count(),
        ]);
    }

    /**
     * Get available gestionnaires (not yet assigned to current responsable).
     */
    public function available(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isResponsable() && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignedIds = $user->managedGestionnaires()->pluck('users.id')->toArray();

        $available = User::where('role', 'gestionnaire')
            ->where('is_active', true)
            ->whereNotIn('id', $assignedIds)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'gestionnaires' => $available,
        ]);
    }

    /**
     * Attach a gestionnaire to current user's team.
     */
    public function attach(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser->isResponsable() && !$currentUser->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->role !== 'gestionnaire') {
            return response()->json(['message' => 'User is not a gestionnaire'], 422);
        }

        // Check if already attached
        if ($currentUser->managedGestionnaires()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'Gestionnaire already in team'], 422);
        }

        $currentUser->managedGestionnaires()->attach($user->id);

        return response()->json([
            'message' => 'Gestionnaire added to team',
            'gestionnaire' => $user->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Detach a gestionnaire from current user's team.
     */
    public function detach(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser->isResponsable() && !$currentUser->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $currentUser->managedGestionnaires()->detach($user->id);

        return response()->json([
            'message' => 'Gestionnaire removed from team',
        ]);
    }

    /**
     * Toggle a gestionnaire's active status.
     */
    public function toggleActive(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser->isResponsable() && !$currentUser->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify the user is in current user's team (unless admin)
        if (!$currentUser->isAdmin() && !$currentUser->managedGestionnaires()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'User not in your team'], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'is_active' => $user->is_active,
        ]);
    }
}
