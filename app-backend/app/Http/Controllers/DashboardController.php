<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\Inspection;
use App\Models\Part;
use App\Models\Defect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

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
        $role = $user->role ?? 'inspector';
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
            'lots'
        ));
    }

    public function validation()
    {
        return redirect()->route('supervisor.validation');
    }
}