<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    /**
     * Get audit logs with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can view audit logs
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = AuditLog::with('user:id,username,email,fname,lname');

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Filter by IP address
        if ($request->has('ip_address') && $request->ip_address) {
            $query->where('ip_address', $request->ip_address);
        }

        // Search in description
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('action', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        // Paginate
        $perPage = $request->get('per_page', 50);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ]
        ]);
    }

    /**
     * Get statistics for audit logs.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get date range (default last 30 days)
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $stats = [
            'total_activities' => AuditLog::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->count(),
            'unique_users' => AuditLog::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->distinct('user_id')->count('user_id'),
            'total_logins' => AuditLog::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->where('action', 'login')->count(),
            'activities_by_action' => AuditLog::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'activities_by_user' => AuditLog::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->selectRaw('username, COUNT(*) as count')
                ->whereNotNull('username')
                ->groupBy('username')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'recent_logins' => AuditLog::where('action', 'login')
                ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['username', 'ip_address', 'created_at']),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get activity details.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $user = request()->user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $auditLog->load('user:id,username,email,fname,lname');

        return response()->json([
            'success' => true,
            'data' => $auditLog
        ]);
    }

    /**
     * Export audit logs to CSV.
     */
    public function exportCsv(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = AuditLog::with('user:id,username,email');

        // Apply same filters as index
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && $request->action) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['ID', 'Username', 'Action', 'Description', 'IP Address', 'Timestamp']);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->username,
                    $log->action,
                    $log->description,
                    $log->ip_address,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
