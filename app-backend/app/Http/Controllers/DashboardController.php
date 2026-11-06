<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\Inspection;
use App\Models\Part;
use App\Models\Defect;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role ?? 'inspector';

        // Route supervisor to their own dashboard
        if ($role === 'supervisor') {
            return $this->supervisorDashboard();
        }

        // Route admin to their own dashboard
        if ($role === 'admin') {
            return $this->adminDashboard();
        }

        // --- INSPECTOR DASHBOARD ---
        // 1. Metric Counts
        $totalInspections = Inspection::count();
        $pendingCount = Inspection::where('status', 'pending_approval')->count();
        $approvedCount = Inspection::where('status', 'approved')->count();
        $rejectedCount = Inspection::where('status', 'rejected')->count();

        // PPM calculation
        $totalDefectQty  = Defect::sum('quantity');
        $totalSampleSize = Inspection::sum('sample_size') ?: 1;
        $defectPpm = (int) round(($totalDefectQty / $totalSampleSize) * 1_000_000);

        // Daily / real-time stats
        $inspectedTodayCount = Inspection::whereDate('created_at', now()->today())
            ->orWhereDate('inspection_date', now()->today())
            ->count();
        $pendingDispositionCount = $pendingCount;
        $passRate = $totalInspections > 0 ? round(($approvedCount / $totalInspections) * 100, 1) : 100.0;

        // Recent inspections for table
        $recentInspections = Inspection::with(['part', 'inspector', 'supervisor'])
            ->latest('inspection_date')
            ->latest('id')
            ->take(10)
            ->get();

        // Parts for GRN modal
        $parts = Part::orderBy('part_number')->get();

        // Incoming materials queue (dock arrivals waiting for inspection)
        $queue = IncomingMaterial::with('part')
            ->where('status', 'waiting')
            ->orderBy('arrived_at', 'desc')
            ->get();
        $queueCount = $queue->count();
        $arrivedTodayCount = IncomingMaterial::whereDate('arrived_at', now()->toDateString())->count();
        if ($arrivedTodayCount === 0 && $queueCount > 0) {
            $arrivedTodayCount = $queueCount;
        }

        // Generate next sequential GRN number
        $todayPrefix = 'GRN-' . now()->format('Ymd');
        $todayGrnCount = IncomingMaterial::where('grn_number', 'like', "{$todayPrefix}-%")->count();
        $nextGrnNumber = sprintf('%s-%03d', $todayPrefix, $todayGrnCount + 1);

        // Additional variables for template flexibility
        $totalLotsArrived = IncomingMaterial::count();
        $pendingReview = $pendingCount;
        $pendingReviewCount = $pendingCount;
        $inspectedToday = $inspectedTodayCount;
        $passedToday = Inspection::where('status', 'approved')
            ->where(function ($q) {
                $q->whereDate('created_at', now()->today())
                  ->orWhereDate('inspection_date', now()->today());
            })->count();
        $rejectedToday = Inspection::where('status', 'rejected')
            ->where(function ($q) {
                $q->whereDate('created_at', now()->today())
                  ->orWhereDate('inspection_date', now()->today());
            })->count();
        $inspectionRate = ($totalLotsArrived > 0) ? round(($totalInspections / $totalLotsArrived) * 100) . '%' : '100%';

        $topParts = Part::withCount('inspections')
            ->orderBy('inspections_count', 'desc')
            ->take(5)
            ->get();

        $lots = $recentInspections;

        // Chart data: last 7 days inspection counts by status
        $chartDays = collect(range(6, 0))->map(function ($d) {
            $date = now()->subDays($d)->toDateString();
            return [
                'label'    => now()->subDays($d)->format('d/m'),
                'approved' => Inspection::where('status', 'approved')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)
                                      ->orWhereDate('created_at', $date);
                                })->count(),
                'pending'  => Inspection::where('status', 'pending_approval')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)
                                      ->orWhereDate('created_at', $date);
                                })->count(),
                'rejected' => Inspection::where('status', 'rejected')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)
                                      ->orWhereDate('created_at', $date);
                                })->count(),
            ];
        });
        $chartLabels   = $chartDays->pluck('label');
        $chartApproved = $chartDays->pluck('approved');
        $chartPending  = $chartDays->pluck('pending');
        $chartRejected = $chartDays->pluck('rejected');

        return view('dashboard.index', compact(
            'totalInspections',
            'pendingCount',
            'pendingReview',
            'pendingReviewCount',
            'approvedCount',
            'rejectedCount',
            'defectPpm',
            'passRate',
            'recentInspections',
            'parts',
            'inspectedToday',
            'inspectedTodayCount',
            'pendingDispositionCount',
            'queue',
            'queueCount',
            'arrivedTodayCount',
            'nextGrnNumber',
            'role',
            'totalLotsArrived',
            'passedToday',
            'rejectedToday',
            'inspectionRate',
            'topParts',
            'lots',
            'chartLabels',
            'chartApproved',
            'chartPending',
            'chartRejected'
        ));
    }

    /**
     * Supervisor Dashboard: real-time KPI overview + pending MRB queue.
     */
    private function supervisorDashboard()
    {
        $pendingCount    = Inspection::where('status', 'pending_approval')->count();
        $approvedCount   = Inspection::where('status', 'approved')->count();
        $rejectedCount   = Inspection::where('status', 'rejected')->count();
        $totalInspections = Inspection::count();

        // Pass rate MTD (month-to-date)
        $mtdTotal    = Inspection::whereMonth('inspection_date', now()->month)->count();
        $mtdApproved = Inspection::where('status', 'approved')->whereMonth('inspection_date', now()->month)->count();
        $passRate    = $mtdTotal > 0 ? round(($mtdApproved / $mtdTotal) * 100, 1) : 100.0;

        // Today's throughput
        $inspectedTodayCount = Inspection::whereDate('created_at', now()->today())
            ->orWhereDate('inspection_date', now()->today())
            ->count();

        // Pending MRB queue for table
        $pendingInspections = Inspection::with(['part', 'inspector', 'measurements', 'defects'])
            ->where('status', 'pending_approval')
            ->latest()
            ->take(10)
            ->get();

        // Recent disposition decisions (last 5)
        $recentValidations = Inspection::with(['part', 'inspector'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        // Chart: 7-day inspection throughput for supervisor
        $spvChartDays = collect(range(6, 0))->map(function ($d) {
            $date = now()->subDays($d)->toDateString();
            return [
                'label'    => now()->subDays($d)->format('d/m'),
                'approved' => Inspection::where('status', 'approved')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)->orWhereDate('created_at', $date);
                                })->count(),
                'pending'  => Inspection::where('status', 'pending_approval')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)->orWhereDate('created_at', $date);
                                })->count(),
                'rejected' => Inspection::where('status', 'rejected')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)->orWhereDate('created_at', $date);
                                })->count(),
            ];
        });
        $chartLabels   = $spvChartDays->pluck('label');
        $chartApproved = $spvChartDays->pluck('approved');
        $chartPending  = $spvChartDays->pluck('pending');
        $chartRejected = $spvChartDays->pluck('rejected');

        return view('dashboard.supervisor', compact(
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalInspections',
            'passRate',
            'inspectedTodayCount',
            'pendingInspections',
            'recentValidations',
            'chartLabels',
            'chartApproved',
            'chartPending',
            'chartRejected'
        ));
    }

    /**
     * Admin Dashboard: system overview + user management shortcut.
     */
    private function adminDashboard()
    {
        $totalUsers      = User::count();
        $inspectorCount  = User::where('role', 'inspector')->count();
        $supervisorCount = User::where('role', 'supervisor')->count();

        $totalInspections = Inspection::count();
        $pendingCount     = Inspection::where('status', 'pending_approval')->count();
        $approvedCount    = Inspection::where('status', 'approved')->count();
        $rejectedCount    = Inspection::where('status', 'rejected')->count();

        $totalLotsArrived = IncomingMaterial::count();

        $recentInspections = Inspection::with(['part', 'inspector', 'supervisor'])
            ->latest('inspection_date')
            ->latest('id')
            ->take(10)
            ->get();

        // Chart: inspection status donut for admin overview
        $adminChartDays = collect(range(6, 0))->map(function ($d) {
            $date = now()->subDays($d)->toDateString();
            return [
                'label'    => now()->subDays($d)->format('d/m'),
                'approved' => Inspection::where('status', 'approved')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)->orWhereDate('created_at', $date);
                                })->count(),
                'pending'  => Inspection::where('status', 'pending_approval')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)->orWhereDate('created_at', $date);
                                })->count(),
                'rejected' => Inspection::where('status', 'rejected')
                                ->where(function ($q) use ($date) {
                                    $q->whereDate('inspection_date', $date)->orWhereDate('created_at', $date);
                                })->count(),
            ];
        });
        $chartLabels   = $adminChartDays->pluck('label');
        $chartApproved = $adminChartDays->pluck('approved');
        $chartPending  = $adminChartDays->pluck('pending');
        $chartRejected = $adminChartDays->pluck('rejected');

        return view('dashboard.admin', compact(
            'totalUsers',
            'inspectorCount',
            'supervisorCount',
            'totalInspections',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalLotsArrived',
            'recentInspections',
            'chartLabels',
            'chartApproved',
            'chartPending',
            'chartRejected'
        ));
    }

    public function validation()
    {
        return redirect()->route('supervisor.validation');
    }
}