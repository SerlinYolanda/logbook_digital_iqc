<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\Inspection;
use App\Models\Part;
use App\Models\Defect;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalInspections = Inspection::count();
        $pendingCount = Inspection::where('status', 'pending_approval')->count();
        $approvedCount = Inspection::where('status', 'approved')->count();
        $rejectedCount = Inspection::where('status', 'rejected')->count();
        
        $totalDefectQty  = Defect::sum('quantity');
        $totalSampleSize = Inspection::sum('sample_size') ?: 1;
        // PPM = (jumlah defek / total sampel) × 1.000.000
        $defectPpm = (int) round(($totalDefectQty / $totalSampleSize) * 1_000_000);

        // Calculate stats purely dynamically from database
        $inspectedTodayCount = Inspection::whereDate('created_at', now()->today())->count();
        $pendingDispositionCount = $pendingCount;
        $passRate = $totalInspections > 0 ? round(($approvedCount / $totalInspections) * 100, 1) : 100.0;

        $recentInspections = Inspection::with(['part', 'inspector', 'supervisor'])
            ->latest()
            ->take(10)
            ->get();

        $parts = Part::orderBy('part_number')->get();

        // Real incoming material (GRN) queue
        $queue = IncomingMaterial::with('part')
            ->where('status', 'waiting')
            ->orderBy('arrived_at')
            ->get();
        $queueCount = $queue->count();
        $arrivedTodayCount = IncomingMaterial::whereDate('arrived_at', now()->toDateString())->count();

        $todayPrefix = 'GRN-' . now()->format('Ymd');
        $todayGrnCount = IncomingMaterial::where('grn_number', 'like', "{$todayPrefix}-%")->count();
        $nextGrnNumber = sprintf('%s-%03d', $todayPrefix, $todayGrnCount + 1);

        return view('dashboard.index', compact(
            'totalInspections',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'defectPpm',
            'passRate',
            'recentInspections',
            'parts',
            'inspectedTodayCount',
            'pendingDispositionCount',
            'queue',
            'queueCount',
            'arrivedTodayCount',
            'nextGrnNumber'
        ));
    }
}
