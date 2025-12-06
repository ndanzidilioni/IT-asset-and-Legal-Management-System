<?php

namespace App\Http\Controllers;

use App\Models\ITAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ICTDashboardController extends Controller
{
    /**
     * Get comprehensive dashboard data.
     */
    public function index(): JsonResponse
    {
        $dashboardData = [
            'overview' => $this->getOverviewStats(),
            'status_distribution' => $this->getStatusDistribution(),
            'condition_analysis' => $this->getConditionAnalysis(),
            'maintenance_alerts' => $this->getMaintenanceAlerts(),
            'department_distribution' => $this->getDepartmentDistribution(),
            'brand_analysis' => $this->getBrandAnalysis(),
            'recent_activities' => $this->getRecentActivities(),
            'critical_assets' => $this->getCriticalAssets(),
            'network_assets' => $this->getNetworkAssets(),
        ];

        return response()->json([
            'success' => true,
            'data' => $dashboardData
        ]);
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats()
    {
        $total = ITAsset::count();
        $active = ITAsset::where('status', 'active')->count();
        $maintenance = ITAsset::where('status', 'maintenance')->count();
        $disposed = ITAsset::where('status', 'disposed')->count();
        $critical = ITAsset::critical()->count();
        $maintenanceDue = ITAsset::maintenanceDue(30)->count();

        return [
            'total_assets' => $total,
            'active_assets' => $active,
            'maintenance_assets' => $maintenance,
            'disposed_assets' => $disposed,
            'critical_assets' => $critical,
            'maintenance_due' => $maintenanceDue,
            'active_percentage' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
        ];
    }


    /**
     * Get status distribution.
     */
    private function getStatusDistribution()
    {
        return ITAsset::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
    }

    /**
     * Get condition analysis.
     */
    private function getConditionAnalysis()
    {
        return ITAsset::selectRaw('condition, count(*) as count')
            ->groupBy('condition')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'condition' => ucfirst($item->condition),
                    'count' => $item->count,
                    'percentage' => round(($item->count / ITAsset::count()) * 100, 1)
                ];
            });
    }


    /**
     * Get maintenance alerts.
     */
    private function getMaintenanceAlerts()
    {
        $overdue = ITAsset::where('next_maintenance_date', '<', now())->count();
        $due_soon = ITAsset::maintenanceDue(30)->count();
        $never_maintained = ITAsset::whereNull('last_maintenance_date')->count();

        return [
            'overdue' => $overdue,
            'due_soon' => $due_soon,
            'never_maintained' => $never_maintained,
            'total_alerts' => $overdue + $due_soon + $never_maintained,
        ];
    }


    /**
     * Get department distribution.
     */
    private function getDepartmentDistribution()
    {
        return ITAsset::selectRaw('department, count(*) as count')
            ->groupBy('department')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'department' => $item->department,
                    'count' => $item->count,
                    'percentage' => round(($item->count / ITAsset::count()) * 100, 1)
                ];
            });
    }

    /**
     * Get brand analysis.
     */
    private function getBrandAnalysis()
    {
        return ITAsset::selectRaw('brand, count(*) as count')
            ->whereNotNull('brand')
            ->groupBy('brand')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'brand' => $item->brand,
                    'count' => $item->count,
                    'percentage' => round(($item->count / ITAsset::whereNotNull('brand')->count()) * 100, 1)
                ];
            });
    }

    /**
     * Get recent activities.
     */
    private function getRecentActivities()
    {
        return ITAsset::with('creator')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'asset_number' => $asset->asset_number,
                    'description' => $asset->asset_description,
                    'action' => $asset->updated_at > $asset->created_at ? 'Updated' : 'Created',
                    'updated_at' => $asset->updated_at,
                    'updated_by' => $asset->creator->name ?? 'Unknown',
                ];
            });
    }

    /**
     * Get critical assets.
     */
    private function getCriticalAssets()
    {
        return ITAsset::critical()
            ->with('creator')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'asset_number' => $asset->asset_number,
                    'description' => $asset->asset_description,
                    'location' => "{$asset->building}, {$asset->floor}, {$asset->room}",
                    'status' => $asset->status,
                    'condition' => $asset->condition,
                    'assigned_to' => $asset->assigned_to,
                ];
            });
    }

    /**
     * Get network assets.
     */
    private function getNetworkAssets()
    {
        return ITAsset::whereNotNull('ip_address')
            ->orWhereNotNull('mac_address')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'asset_number' => $asset->asset_number,
                    'description' => $asset->asset_description,
                    'ip_address' => $asset->ip_address,
                    'mac_address' => $asset->mac_address,
                    'network_zone' => $asset->network_zone,
                    'status' => $asset->status,
                ];
            });
    }

    /**
     * Get detailed reports.
     */
    public function getReports(Request $request): JsonResponse
    {
        $reportType = $request->get('type', 'summary');
        
        switch ($reportType) {
            case 'maintenance':
                return $this->getMaintenanceReport($request);
            case 'department':
                return $this->getDepartmentReport($request);
            default:
                return $this->getSummaryReport($request);
        }
    }


    /**
     * Get maintenance report.
     */
    private function getMaintenanceReport(Request $request)
    {
        $overdue = ITAsset::where('next_maintenance_date', '<', now())->get();
        $dueSoon = ITAsset::maintenanceDue(30)->get();
        $neverMaintained = ITAsset::whereNull('last_maintenance_date')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overdue' => $overdue,
                'due_soon' => $dueSoon,
                'never_maintained' => $neverMaintained,
                'summary' => [
                    'overdue_count' => $overdue->count(),
                    'due_soon_count' => $dueSoon->count(),
                    'never_maintained_count' => $neverMaintained->count(),
                ]
            ]
        ]);
    }


    /**
     * Get department report.
     */
    private function getDepartmentReport(Request $request)
    {
        $departments = ITAsset::selectRaw('department, count(*) as asset_count')
            ->groupBy('department')
            ->orderBy('asset_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }


    /**
     * Get summary report.
     */
    private function getSummaryReport(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->index()->getData()->data
        ]);
    }
}
