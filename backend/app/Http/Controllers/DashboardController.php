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
            return $this->responsableDashboard($user, $request);
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
    private function responsableDashboard(User $user, Request $request): JsonResponse
    {
        $period = $request->input('period', 'week'); // Default to week
        $now = now();

        switch ($period) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $trendGranularity = 'day';
                break;
            case 'month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $trendGranularity = 'day';
                break;
            case 'quarter':
                $startDate = $now->copy()->startOfQuarter();
                $endDate = $now->copy()->endOfQuarter();
                $trendGranularity = 'month';
                break;
            case 'semester':
                // First or second half of year
                $startDate = $now->month <= 6 ? $now->copy()->startOfYear() : $now->copy()->startOfYear()->addMonths(6);
                $endDate = $now->month <= 6 ? $now->copy()->startOfYear()->addMonths(5)->endOfMonth() : $now->copy()->endOfYear();
                $trendGranularity = 'month';
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $trendGranularity = 'month';
                break;
            case 'week':
            default:
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $trendGranularity = 'day';
                break;
        }

        // Get team member IDs (gestionnaires managed by this responsable)
        $teamIds = $user->managedGestionnaires()->pluck('users.id')->toArray();
        
        // Include the responsable themselves
        $allUserIds = array_merge($teamIds, [$user->id]);

        // Projects (all active projects)
        $projects = \App\Http\Resources\ProjectResource::collection(
            \App\Models\Project::active()->orderBy('name')->get()
        );

        // Current active entry for the responsable (Always Real-time)
        $activeEntry = TimeEntry::with(['project'])
            ->where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        // Team active entries (Always Real-time)
        $teamActiveEntries = TimeEntry::with(['user', 'project'])
            ->whereIn('user_id', $teamIds)
            ->whereNull('end_time')
            ->get();

        // Stats Logic Helper
        $getDurationSum = function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('start_time', [$startDate, $endDate])
                ->whereNotNull('end_time')
                ->sum('duration');
        };

        // Team statistics for period
        $teamStats = User::whereIn('id', $teamIds)
            ->with(['timeEntries' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('end_time');
            }])
            ->get()
            ->map(function ($member) {
                $totalSeconds = $member->timeEntries->sum('duration');
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'is_active' => $member->is_active,
                    'hours_period' => round($totalSeconds / 3600, 2),
                ];
            });

        // Responsable own stats for period
        $ownSeconds = TimeEntry::where('user_id', $user->id)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereNotNull('end_time')
            ->sum('duration');
        $ownHours = round($ownSeconds / 3600, 2);

        // Team total hours for period
        $teamTotalSeconds = TimeEntry::whereIn('user_id', $teamIds)
             ->whereBetween('start_time', [$startDate, $endDate])
            ->whereNotNull('end_time')
            ->sum('duration');
        $teamPeriodHours = round($teamTotalSeconds / 3600, 2);

        // Project stats for period
        $projectStats = TimeEntry::whereIn('user_id', $allUserIds)
            ->whereBetween('start_time', [$startDate, $endDate])
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

        // Trend logic
        $trendEntries = TimeEntry::whereIn('user_id', $teamIds)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereNotNull('end_time');
        
        if ($trendGranularity === 'day') {
            $trendData = $trendEntries->select(DB::raw('start_time::date as date'), DB::raw('SUM(duration) as total_seconds'))
                ->groupBy(DB::raw('start_time::date'))
                ->get()
                ->map(fn($item) => [
                    'label' => \Carbon\Carbon::parse($item->date)->isoFormat('dd D'),
                    'hours' => round($item->total_seconds / 3600, 2)
                ]);
        } else {
             $trendData = $trendEntries->select(DB::raw("TO_CHAR(start_time, 'YYYY-MM') as month"), DB::raw('SUM(duration) as total_seconds'))
                ->groupBy(DB::raw("TO_CHAR(start_time, 'YYYY-MM')"))
                ->get()
                ->map(fn($item) => [
                    'label' => \Carbon\Carbon::parse($item->month . '-01')->isoFormat('MMMM YYYY'),
                    'hours' => round($item->total_seconds / 3600, 2)
                ]);
        }

        return response()->json([
            'role' => 'responsable',
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'projects' => $projects,
            'active_entry' => $activeEntry ? new TimeEntryResource($activeEntry) : null,
            'my_period_hours' => $ownHours,
            'team_active_entries' => TimeEntryResource::collection($teamActiveEntries),
            'team_stats' => $teamStats,
            'team_period_hours' => $teamPeriodHours,
            'team_count' => count($teamIds),
            'project_stats' => $projectStats,
            'period_trend' => $trendData,
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
