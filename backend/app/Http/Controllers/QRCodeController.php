<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QRCodeController extends Controller
{
    /**
     * Validate a QR code and return project information.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code_token' => 'required|string',
        ]);

        $project = Project::where('qr_code_token', $request->qr_code_token)
            ->where('status', 'active')
            ->first();

        if (!$project) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired QR code',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'client' => $project->client,
                'latitude' => $project->latitude,
                'longitude' => $project->longitude,
                'geofence_radius' => $project->geofence_radius,
            ],
        ]);
    }

    /**
     * Generate or regenerate QR code token for a project.
     */
    public function generateToken(Project $project): JsonResponse
    {
        // Generate a unique token
        $token = Str::random(32);
        
        $project->update([
            'qr_code_token' => $token,
        ]);

        return response()->json([
            'qr_code_token' => $token,
            'qr_code_data' => json_encode([
                'type' => 'best_time_project',
                'token' => $token,
                'project_id' => $project->id,
                'project_name' => $project->name,
            ]),
        ]);
    }

    /**
     * Get QR code data for a project.
     */
    public function getQRCode(Project $project): JsonResponse
    {
        if (!$project->qr_code_token) {
            return response()->json([
                'message' => 'No QR code generated for this project. Generate one first.',
            ], 404);
        }

        return response()->json([
            'qr_code_token' => $project->qr_code_token,
            'qr_code_data' => json_encode([
                'type' => 'best_time_project',
                'token' => $project->qr_code_token,
                'project_id' => $project->id,
                'project_name' => $project->name,
            ]),
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'client' => $project->client,
            ],
        ]);
    }
}
