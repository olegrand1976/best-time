<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    /**
     * Get application logs (laravel.log).
     * Access: Admin only
     */
    public function getApplicationLogs(Request $request): JsonResponse
    {
        // Authorization: Admin only for technical logs
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $lines = (int) $request->input('lines', 500); // Par défaut 500 lignes
        $level = $request->input('level'); // 'error', 'warning', 'info', etc.

        $logPath = storage_path('logs/laravel.log');

        if (!File::exists($logPath)) {
            return response()->json([
                'logs' => [],
                'total_lines' => 0,
                'message' => 'Log file does not exist yet.',
            ]);
        }

        $logContent = File::get($logPath);
        $logLines = explode("\n", $logContent);

        // Filtrer par niveau si spécifié
        if ($level) {
            $logLines = array_filter($logLines, function ($line) use ($level) {
                return stripos($line, $level) !== false;
            });
        }

        // Prendre les N dernières lignes
        $logLines = array_slice($logLines, -$lines);

        // Formater les logs
        $formattedLogs = array_map(function ($line) {
            return [
                'line' => $line,
                'level' => $this->extractLogLevel($line),
                'timestamp' => $this->extractTimestamp($line),
            ];
        }, array_values($logLines));

        return response()->json([
            'logs' => array_reverse($formattedLogs), // Plus récent en premier
            'total_lines' => count($formattedLogs),
        ]);
    }

    /**
     * Get activity logs (audit trail).
     * Access: Admin + Responsable only
     */
    public function getActivityLogs(Request $request): AnonymousResourceCollection
    {
        // Authorization: Admin + Responsable only for activity logs
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && $user->role !== 'responsable')) {
            abort(403, 'Unauthorized. Admin or Responsable access required.');
        }

        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model
        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
            if ($request->has('model_id')) {
                $query->where('model_id', $request->model_id);
            }
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search in description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50);

        return ActivityLogResource::collection($logs);
    }

    /**
     * Get statistics about logs.
     * Access: Admin + Responsable only
     */
    public function getLogStatistics(Request $request): JsonResponse
    {
        // Authorization: Admin + Responsable only
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && $user->role !== 'responsable')) {
            return response()->json(['message' => 'Unauthorized. Admin or Responsable access required.'], 403);
        }

        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $stats = [
            'total_activities' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])->count(),
            'activities_by_action' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'activities_by_user' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('user_id')
                ->selectRaw('user_id, count(*) as count')
                ->groupBy('user_id')
                ->with('user:id,name,email')
                ->get()
                ->map(function ($item) {
                    return [
                        'user' => $item->user,
                        'count' => $item->count,
                    ];
                }),
            'most_active_users' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('user_id')
                ->selectRaw('user_id, count(*) as count')
                ->groupBy('user_id')
                ->orderByDesc('count')
                ->limit(10)
                ->with('user:id,name,email')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Clear application logs (truncate laravel.log).
     * Access: Admin only
     */
    public function clearApplicationLogs(Request $request): JsonResponse
    {
        // Authorization: Admin only for technical logs
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $logPath = storage_path('logs/laravel.log');

        if (File::exists($logPath)) {
            File::put($logPath, '');
            return response()->json(['message' => 'Logs cleared successfully.']);
        }

        return response()->json(['message' => 'Log file does not exist.'], 404);
    }

    /**
     * Extract log level from log line.
     */
    private function extractLogLevel(string $line): ?string
    {
        if (preg_match('/\.(ERROR|WARNING|INFO|DEBUG|CRITICAL|ALERT|EMERGENCY):/i', $line, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }

    /**
     * Extract timestamp from log line.
     */
    private function extractTimestamp(string $line): ?string
    {
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
