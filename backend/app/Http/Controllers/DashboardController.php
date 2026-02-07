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

        if ($user->isResponsable()) {
            return $this->responsableDashboard($user);
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
     * Responsable dashboard data - team overview and own clocking.
     */
    private function responsableDashboard(User $user): JsonResponse
    {
        // Get team member IDs (gestionnaires managed by this responsable)
        $teamIds = $user->managedGestionnaires()->pluck('users.id')->toArray();
        
        // Include the responsable themselves
        $allUserIds = array_merge($teamIds, [$user->id]);

        // Projects (all active projects)
        $projects = \App\Http\Resources\ProjectResource::collection(
            \App\Models\Project::active()->orderBy('name')->get()
        );

        // Current active entry for the responsable
        $activeEntry = TimeEntry::with(['project'])
            ->where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        // Team active entries (gestionnaires currently working)
        $teamActiveEntries = TimeEntry::with(['user', 'project'])
            ->whereIn('user_id', $teamIds)
            ->whereNull('end_time')
            ->get();

        // Team statistics today
        $teamStatsToday = User::whereIn('id', $teamIds)
            ->with(['timeEntries' => function ($query) {
                $query->today()->whereNotNull('end_time');
            }])
            ->get()
            ->map(function ($member) {
                $totalSeconds = $member->timeEntries->sum('duration');
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'is_active' => $member->is_active,
                    'hours_today' => round($totalSeconds / 3600, 2),
                ];
            });

        // Responsable own stats
        $todayEntries = TimeEntry::where('user_id', $user->id)
            ->today()
            ->whereNotNull('end_time')
            ->select(DB::raw('SUM(duration) as total_seconds'))
            ->first();
        $todayHours = $todayEntries->total_seconds ? round($todayEntries->total_seconds / 3600, 2) : 0;

        $weekEntries = TimeEntry::where('user_id', $user->id)
            ->thisWeek()
            ->whereNotNull('end_time')
            ->select(DB::raw('SUM(duration) as total_seconds'))
            ->first();
        $weekHours = $weekEntries->total_seconds ? round($weekEntries->total_seconds / 3600, 2) : 0;

        // Team total hours today
        $teamTodayTotal = TimeEntry::whereIn('user_id', $teamIds)
            ->today()
            ->whereNotNull('end_time')
            ->select(DB::raw('SUM(duration) as total_seconds'))
            ->first();
        $teamTodayHours = $teamTodayTotal->total_seconds ? round($teamTodayTotal->total_seconds / 3600, 2) : 0;

        // Project stats today (hours per project with users who worked on it)
        $projectStats = TimeEntry::whereIn('user_id', $allUserIds)
            ->today()
            ->whereNotNull('project_id')
            ->with(['project', 'user'])
            ->get()
            ->groupBy('project_id')
            ->map(function ($entries) {
                $project = $entries->first()->project;
                $totalSeconds = $entries->sum('duration');
                $users = $entries->groupBy('user_id')->map(function ($userEntries) {
                    $user = $userEntries->first()->user;
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'hours' => round($userEntries->sum('duration') / 3600, 2),
                    ];
                })->values();

                return [
                    'project_id' => $project?->id,
                    'project_name' => $project?->name ?? 'Sans projet',
                    'total_hours' => round($totalSeconds / 3600, 2),
                    'users' => $users,
                ];
            })->values();

        // Daily trend for the last 7 days (Team)
        $dailyTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyTrend->put($date, 0);
        }

        $trendEntries = TimeEntry::whereIn('user_id', $teamIds)
            ->whereBetween('start_time', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->whereNotNull('end_time')
            ->select(DB::raw('DATE(start_time) as date'), DB::raw('SUM(duration) as total_seconds'))
            ->groupBy('date')
            ->get();

        $trendEntries->each(function ($entry) use ($dailyTrend) {
            $dailyTrend->put($entry->date, round($entry->total_seconds / 3600, 2));
        });

        $formattedTrend = $dailyTrend->map(function ($hours, $date) {
            return [
                'date' => $date,
                'hours' => $hours,
                'label' => \Carbon\Carbon::parse($date)->isoFormat('dd D'),
            ];
        })->values();

        return response()->json([
            'role' => 'responsable',
            'projects' => $projects,
            'active_entry' => $activeEntry ? new TimeEntryResource($activeEntry) : null,
            'today_hours' => $todayHours,
            'week_hours' => $weekHours,
            'team_active_entries' => TimeEntryResource::collection($teamActiveEntries),
            'team_stats' => $teamStatsToday,
            'team_today_hours' => $teamTodayHours,
            'team_count' => count($teamIds),
            'project_stats' => $projectStats,
            'daily_trend' => $formattedTrend,
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
