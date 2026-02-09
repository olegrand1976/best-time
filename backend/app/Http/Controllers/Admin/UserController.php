<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query()->orderBy('created_at', 'desc');

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(50);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'ouvrier',
            'phone' => $request->phone,
            'address' => $request->address,
            'box' => $request->box,
            'zip_code' => $request->zip_code,
            'city' => $request->city,
            'project_id' => $request->project_id,
        ]);
        
        if ($user->email) {
            try {
                Mail::to($user)->send(new WelcomeEmail($user, $request->password));
            } catch (\Exception $e) {
                // Log email error?
            }
        }

        // Log the creation
        ActivityLogService::logCreated($user, $request);

        return response()->json(new UserResource($user), 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->loadCount(['timeEntries as total_entries'])
            ->loadSum('timeEntries as total_hours', 'duration');

        return response()->json(new UserResource($user));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $oldAttributes = $user->getAttributes();

        $data = $request->validated();
        
        // Update name if first/last name changed
        if (isset($data['first_name']) || isset($data['last_name'])) {
            $firstName = $data['first_name'] ?? $user->first_name;
            $lastName = $data['last_name'] ?? $user->last_name;
            $data['name'] = $firstName . ' ' . $lastName;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Log the update
        ActivityLogService::logUpdated($user, $oldAttributes, $request);

        return response()->json(new UserResource($user));
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        // Log the deletion before deleting
        ActivityLogService::logDeleted($user, request());

        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Get user statistics.
     */
    public function statistics(User $user, Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $stats = [
            'total_entries' => $user->timeEntries()
                ->whereBetween('start_time', [$startDate, $endDate])
                ->count(),
            'total_hours' => round(
                $user->timeEntries()
                    ->whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('end_time')
                    ->sum('duration') / 3600,
                2
            ),
            'active_projects' => $user->timeEntries()
                ->whereBetween('start_time', [$startDate, $endDate])
                ->whereNotNull('project_id')
                ->distinct('project_id')
                ->count('project_id'),
        ];

        return response()->json($stats);
    }
}
