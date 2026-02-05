<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TimeEntry\StoreTimeEntryRequest;
use App\Http\Requests\TimeEntry\UpdateTimeEntryRequest;
use App\Http\Resources\TimeEntryResource;
use App\Models\TimeEntry;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TimeEntryController extends Controller
{
    /**
     * Display a listing of time entries.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = TimeEntry::with(['user', 'project'])
            ->orderBy('start_time', 'desc');

        // Admin can see all entries, others only their own
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Filter by date if provided
        if ($request->has('date')) {
            $query->whereDate('start_time', $request->date);
        }

        // Filter by week if provided
        if ($request->has('week')) {
            $query->thisWeek();
        }

        $timeEntries = $query->paginate(50);

        return TimeEntryResource::collection($timeEntries);
    }

    /**
     * Store a newly created time entry.
     */
    public function store(StoreTimeEntryRequest $request): JsonResponse
    {
        $data = [
            'user_id' => $request->user()->id,
            'project_id' => $request->project_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'description' => $request->description,
            'qr_code_scanned' => $request->boolean('qr_code_scanned', false),
        ];

        // Add geolocation data if provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
            $data['location_accuracy'] = $request->location_accuracy;
            $data['location_captured_at'] = now();
        }

        $timeEntry = TimeEntry::create($data);
        $timeEntry->load(['user', 'project']);

        // Log the creation
        ActivityLogService::logCreated($timeEntry, $request);

        return response()->json(new TimeEntryResource($timeEntry), 201);
    }

    /**
     * Display the specified time entry.
     */
    public function show(Request $request, TimeEntry $timeEntry): JsonResponse
    {
        $user = $request->user();

        // Non-admins can only see their own entries
        if (!$user->isAdmin() && $timeEntry->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $timeEntry->load(['user', 'project']);

        return response()->json(new TimeEntryResource($timeEntry));
    }

    /**
     * Update the specified time entry.
     */
    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry): JsonResponse
    {
        $user = $request->user();

        // Non-admins can only update their own entries
        if (!$user->isAdmin() && $timeEntry->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $oldAttributes = $timeEntry->getAttributes();
        $timeEntry->update($request->validated());
        $timeEntry->load(['user', 'project']);

        // Log the update
        ActivityLogService::logUpdated($timeEntry, $oldAttributes, $request);

        return response()->json(new TimeEntryResource($timeEntry));
    }

    /**
     * Remove the specified time entry.
     */
    public function destroy(Request $request, TimeEntry $timeEntry): JsonResponse
    {
        $user = $request->user();

        // Non-admins can only delete their own entries
        if (!$user->isAdmin() && $timeEntry->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Log the deletion before deleting
        ActivityLogService::logDeleted($timeEntry, $request);

        $timeEntry->delete();

        return response()->json(['message' => 'Time entry deleted successfully']);
    }

    /**
     * Start a new time entry (clock in).
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user already has an active entry
        $activeEntry = TimeEntry::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        if ($activeEntry) {
            return response()->json([
                'message' => 'You already have an active time entry',
                'time_entry' => new TimeEntryResource($activeEntry),
            ], 409);
        }

        // Prepare time entry data
        $data = [
            'user_id' => $user->id,
            'project_id' => $request->project_id,
            'start_time' => now(),
            'description' => $request->description,
            'qr_code_scanned' => $request->boolean('qr_code_scanned', false),
        ];

        // Add geolocation data if provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
            $data['location_accuracy'] = $request->location_accuracy;
            $data['location_captured_at'] = now();
        }

        $timeEntry = TimeEntry::create($data);
        $timeEntry->load(['user', 'project']);

        // Log the clock in
        $logMessage = "User clocked in: {$timeEntry->description}";
        if ($timeEntry->qr_code_scanned) {
            $logMessage .= ' (via QR code)';
        }
        ActivityLogService::log('clock_in', $timeEntry, null, null, $logMessage, $request);

        return response()->json(new TimeEntryResource($timeEntry), 201);
    }

    /**
     * Stop the current time entry (clock out).
     */
    public function stop(Request $request): JsonResponse
    {
        $user = $request->user();

        $timeEntry = TimeEntry::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        if (!$timeEntry) {
            return response()->json([
                'message' => 'No active time entry found',
            ], 404);
        }

        $timeEntry->update([
            'end_time' => now(),
        ]);

        $timeEntry->load(['user', 'project']);

        // Log the clock out
        ActivityLogService::log('clock_out', $timeEntry, null, null, "User clocked out: Duration {$timeEntry->duration_formatted}", $request);

        return response()->json(new TimeEntryResource($timeEntry));
    }
}
