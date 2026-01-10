<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Get general statistics.
     */
    public function index(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $stats = [
            'total_users' => User::count(),
            'total_employees' => User::where('role', 'employee')->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'total_time_entries' => TimeEntry::whereBetween('start_time', [$startDate, $endDate])->count(),
            'total_hours' => round(
                TimeEntry::whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('end_time')
                    ->sum('duration') / 3600,
                2
            ),
            'hours_by_day' => $this->getHoursByDay($startDate, $endDate),
            'hours_by_user' => $this->getHoursByUser($startDate, $endDate),
            'hours_by_project' => $this->getHoursByProject($startDate, $endDate),
        ];

        return response()->json($stats);
    }

    /**
     * Get hours by day.
     */
    private function getHoursByDay(string $startDate, string $endDate): array
    {
        return TimeEntry::whereBetween('start_time', [$startDate, $endDate])
            ->whereNotNull('end_time')
            ->select(
                DB::raw('DATE(start_time) as date'),
                DB::raw('SUM(duration) / 3600 as hours')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($entry) => [
                'date' => $entry->date,
                'hours' => round((float) $entry->hours, 2),
            ])
            ->toArray();
    }

    /**
     * Get hours by user.
     */
    private function getHoursByUser(string $startDate, string $endDate): array
    {
        return User::where('role', 'employee')
            ->withSum([
                'timeEntries as total_hours' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_time', [$startDate, $endDate])
                        ->whereNotNull('end_time')
                        ->select(DB::raw('SUM(duration) / 3600'));
                },
            ], 'duration')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'hours' => round((float) ($user->total_hours ?? 0), 2),
            ])
            ->toArray();
    }

    /**
     * Get hours by project.
     */
    private function getHoursByProject(string $startDate, string $endDate): array
    {
        return Project::withSum([
            'timeEntries as total_hours' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('end_time')
                    ->select(DB::raw('SUM(duration) / 3600'));
            },
        ], 'duration')
            ->get()
            ->map(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'client' => $project->client,
                'hours' => round((float) ($project->total_hours ?? 0), 2),
            ])
            ->filter(fn ($project) => $project['hours'] > 0)
            ->values()
            ->toArray();
    }
}
