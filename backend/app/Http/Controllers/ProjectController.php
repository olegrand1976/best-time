<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(): AnonymousResourceCollection
    {
        $projects = Project::with('client')
            ->active()
            ->orderBy('name')
            ->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());

        // Log the creation
        ActivityLogService::logCreated($project, $request);

        return response()->json(new ProjectResource($project), 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): JsonResponse
    {
        return response()->json(new ProjectResource($project));
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        // Only admins can update projects
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $oldAttributes = $project->getAttributes();
        $project->update($request->validated());

        // Log the update
        ActivityLogService::logUpdated($project, $oldAttributes, $request);

        return response()->json(new ProjectResource($project));
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        // Only admins can delete projects
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        // Log the deletion before deleting
        ActivityLogService::logDeleted($project, $request);

        $project->delete();

        return response()->json(null, 204);
    }
}
