<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\Receiving;
use App\Models\Part;
use App\Models\Defect;
use App\Models\DefectCategory;
use App\Models\User;
use App\Models\AuditTrail;
use App\Models\MrbDisposition;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * UC-11: Pantau Dashboard (Multi-actor adaptive)
     */
    public function index()
    {
        $user = auth()->user();

        // 1. Common KPI Stats
        $totalInbound = Receiving::count();
        $waitingQueue = Receiving::where('status_antrean', 'menunggu_inspeksi')->count();
        $inProgress = Receiving::where('status_antrean', 'dalam_inspeksi')->count();
        $totalLogbooks = Logbook::count();
        $acceptedCount = Logbook::where('status', 'accept')->count();
        $mrbPendingCount = Logbook::where('status', 'menunggu_penetapan')->count();
        $revisionsCount = Logbook::where('status', 'perlu_revisi')->count();
        $rejectedCount = Logbook::whereIn('status', ['return_to_vendor', 'scrap', 'rework'])->count();

        $passRate = $totalLogbooks > 0 ? round(($acceptedCount / $totalLogbooks) * 100, 1) : 100.0;

        // 2. Queue Aging Breakdown (UC-02 & Logistik)
        $normalAgingCount = Receiving::where('status_antrean', 'menunggu_inspeksi')
            ->where('tanggal_terima', '>', now()->subHours(12))
            ->count();
        $prioritasAgingCount = Receiving::where('status_antrean', 'menunggu_inspeksi')
            ->whereBetween('tanggal_terima', [now()->subHours(24), now()->subHours(12)])
            ->count();
        $overdueAgingCount = Receiving::where('status_antrean', 'menunggu_inspeksi')
            ->where('tanggal_terima', '<=', now()->subHours(24))
            ->count();

        // 3. Pareto Chart Data (Defect frequency & cumulative percentage)
        $defectsByCategory = Defect::select('defect_category_id', DB::raw('SUM(qty_defect) as total_qty'))
            ->groupBy('defect_category_id')
            ->orderBy('total_qty', 'desc')
            ->with('defectCategory')
            ->get();

        $totalDefectQty = $defectsByCategory->sum('total_qty');
        $paretoLabels = [];
        $paretoBarData = [];
        $paretoLineData = [];
        $runningSum = 0;

        foreach ($defectsByCategory as $item) {
            $catName = $item->defectCategory->nama_defect ?? 'Lain-lain';
            $qty = (int) $item->total_qty;
            $runningSum += $qty;
            $cumPct = $totalDefectQty > 0 ? round(($runningSum / $totalDefectQty) * 100, 1) : 0;

            $paretoLabels[] = $catName;
            $paretoBarData[] = $qty;
            $paretoLineData[] = $cumPct;
        }

        // 4. 7-Day Trend of Inspection Volume & Pass/Fail
        $trendDates = [];
        $trendAccepted = [];
        $trendRejected = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trendDates[] = $date->format('d M');

            $acc = Logbook::whereDate('tanggal_inspeksi', $date)
                ->where('status', 'accept')
                ->count();
            $rej = Logbook::whereDate('tanggal_inspeksi', $date)
                ->whereIn('status', ['return_to_vendor', 'scrap', 'rework', 'menunggu_penetapan'])
                ->count();

            $trendAccepted[] = $acc;
            $trendRejected[] = $rej;
        }

        // 5. Pass Rate per Commodity
        $commodityStats = [];
        $commodities = [
            'ee_part' => 'EE Part',
            'automotive' => 'Automotive',
            'wire_harness' => 'Wire Harness',
        ];
        foreach ($commodities as $key => $label) {
            $totalComm = Logbook::whereHas('receiving.part', function ($q) use ($key) {
                $q->where('kategori_komoditas', $key);
            })->count();

            $accComm = Logbook::where('status', 'accept')
                ->whereHas('receiving.part', function ($q) use ($key) {
                    $q->where('kategori_komoditas', $key);
                })->count();

            $pct = $totalComm > 0 ? round(($accComm / $totalComm) * 100, 1) : 100.0;
            $commodityStats[] = [
                'key' => $key,
                'label' => $label,
                'total' => $totalComm,
                'accepted' => $accComm,
                'pass_rate' => $pct,
            ];
        }

        // 6. Role-Specific Queues & Tables
        $urgentMrbQueue = Logbook::with(['receiving.supplier', 'receiving.part', 'inspector', 'nestProcess'])
            ->where('status', 'menunggu_penetapan')
            ->orderBy('tanggal_inspeksi', 'asc')
            ->take(5)
            ->get();

        $myRevisionQueue = [];
        $myTodayInspections = 0;
        $myTodaySamples = 0;
        if ($user->isInspector()) {
            $myRevisionQueue = Logbook::with(['receiving.part', 'receiving.supplier', 'latestMrbDisposition'])
                ->where('inspector_id', $user->id)
                ->where('status', 'perlu_revisi')
                ->get();

            $myTodayInspections = Logbook::where('inspector_id', $user->id)
                ->whereDate('tanggal_inspeksi', Carbon::today())
                ->count();

            $myTodaySamples = (int) Logbook::where('inspector_id', $user->id)
                ->whereDate('tanggal_inspeksi', Carbon::today())
                ->sum('qty_sampel');
        }

        // Recent Audit Activity (Admin)
        $recentAudits = AuditTrail::with('user')
            ->orderBy('waktu', 'desc')
            ->take(8)
            ->get();

        $activeUsersCount = User::where('status_aktif', true)->count();
        $totalUsersCount = User::count();

        // Queue waiting for inspector's commodity
        $commodityQueue = Receiving::with(['supplier', 'part'])
            ->where('status_antrean', 'menunggu_inspeksi');
        if ($user->getInspectorCommodity()) {
            $commodityQueue->whereHas('part', function ($q) use ($user) {
                $q->where('kategori_komoditas', $user->getInspectorCommodity());
            });
        }
        $pendingCommodityReceivings = $commodityQueue->orderBy('tanggal_terima', 'asc')->take(5)->get();

        return view('dashboard.index', compact(
            'user',
            'totalInbound',
            'waitingQueue',
            'inProgress',
            'totalLogbooks',
            'acceptedCount',
            'mrbPendingCount',
            'revisionsCount',
            'rejectedCount',
            'passRate',
            'normalAgingCount',
            'prioritasAgingCount',
            'overdueAgingCount',
            'paretoLabels',
            'paretoBarData',
            'paretoLineData',
            'trendDates',
            'trendAccepted',
            'trendRejected',
            'commodityStats',
            'urgentMrbQueue',
            'myRevisionQueue',
            'myTodayInspections',
            'myTodaySamples',
            'recentAudits',
            'activeUsersCount',
            'totalUsersCount',
            'pendingCommodityReceivings'
        ));
    }
}
