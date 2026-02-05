<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\TimeEntryResource;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->employeeDashboard($user);
    }

    /**
     * Admin dashboard data.
     */
    private function adminDashboard(): JsonResponse
    {
        // Total hours today for all employees
        $todayEntries = TimeEntry::today()
            ->whereNotNull('end_time')
            ->select(DB::raw('SUM(duration) as total_seconds'))
            ->first();

        $todayHours = $todayEntries->total_seconds ? round($todayEntries->total_seconds / 3600, 2) : 0;

        // Active entries (currently clocked in)
        $activeEntries = TimeEntry::with(['user', 'project'])
            ->whereNull('end_time')
            ->get();

        // Total employees count
        $totalEmployees = User::where('role', 'ouvrier')->count();
        $totalManagers = User::whereIn('role', ['admin', 'responsable', 'team_leader'])->count();

        // Total projects
        $totalProjects = \App\Models\Project::count();
        $activeProjects = \App\Models\Project::where('status', 'active')->count();

        // Hours this week
        $weekEntries = TimeEntry::thisWeek()
            ->whereNotNull('end_time')
            ->select(DB::raw('SUM(duration) as total_seconds'))
            ->first();

        $weekHours = $weekEntries->total_seconds ? round($weekEntries->total_seconds / 3600, 2) : 0;

        // Recent activity (last 10 entries)
        $recentActivity = TimeEntry::with(['user', 'project'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Stats by project today
        $projectStats = TimeEntry::today()
            ->whereNotNull('end_time')
            ->select('project_id', DB::raw('SUM(duration) as total_seconds'))
            ->groupBy('project_id')
            ->with('project')
            ->get()
            ->map(function ($stat) {
                return [
                    'project_name' => $stat->project->name ?? 'N/A',
                    'hours' => round($stat->total_seconds / 3600, 2),
                ];
            });

        return response()->json([
            'today_hours' => $todayHours,
            'week_hours' => $weekHours,
            'total_employees' => $totalEmployees,
            'total_managers' => $totalManagers,
            'total_projects' => $totalProjects,
            'active_projects_count' => $activeProjects,
            'active_entries' => TimeEntryResource::collection($activeEntries),
            'recent_activity' => TimeEntryResource::collection($recentActivity),
            'project_stats_today' => $projectStats,
        ]);
    }

    /**
     * Employee dashboard data.
     */
    private function employeeDashboard(User $user): JsonResponse
    {
        // Current active entry
        $activeEntry = TimeEntry::with(['project'])
            ->where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        // Hours this week
        $weekEntries = TimeEntry::where('user_id', $user->id)
            ->thisWeek()
            ->whereNotNull('end_time')
            ->select(DB::raw('SUM(duration) as total_seconds'))
            ->first();

        $weekHours = $weekEntries->total_seconds ? round($weekEntries->total_seconds / 3600, 2) : 0;

        // Hours today
        $todayEntries = TimeEntry::where('user_id', $user->id)
            ->today()
            ->whereNotNull('end_time')
            ->select(DB::raw('SUM(duration) as total_seconds'))
            ->first();

        $todayHours = $todayEntries->total_seconds ? round($todayEntries->total_seconds / 3600, 2) : 0;

        return response()->json([
            'active_entry' => $activeEntry ? new TimeEntryResource($activeEntry) : null,
            'today_hours' => $todayHours,
            'week_hours' => $weekHours,
        ]);
    }
}
