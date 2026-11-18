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

        // 4. Material Flow & Throughput: Barang Masuk (Inbound) vs Barang Keluar (Outbound/Released) vs Backlog
        $flowDates = [];
        $flowInboundLots = [];
        $flowInboundQty = [];
        $flowOutboundLots = [];
        $flowOutboundQty = [];
        $flowBacklogLots = [];

        // Trend 7 Hari Terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $flowDates[] = $date->format('d M');

            // 1. Barang Masuk dari Logistik (Inbound Arrived)
            $inLots = Receiving::whereDate('tanggal_terima', $date)->count();
            $inQty = (int) Receiving::whereDate('tanggal_terima', $date)->sum('qty_datang');

            // 2. Barang Keluar dari IQC (Inspected & Released / Closed)
            $outLots = Logbook::whereDate('tanggal_inspeksi', $date)
                ->whereIn('status', ['accept', 'return_to_vendor', 'scrap', 'rework'])
                ->count();
            
            $outQty = (int) Receiving::whereHas('logbooks', function ($q) use ($date) {
                $q->whereDate('tanggal_inspeksi', $date)
                  ->whereIn('status', ['accept', 'return_to_vendor', 'scrap', 'rework']);
            })->sum('qty_datang');

            // 3. Backlog / Sisa Antrean yang Mengendap
            // (Akumulasi barang yang sudah masuk s/d tanggal tersebut tapi belum selesai pada tanggal itu)
            $backlog = Receiving::whereDate('tanggal_terima', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->where('status_antrean', '!=', 'selesai')
                      ->orWhereDoesntHave('logbooks')
                      ->orWhereHas('logbooks', function ($lq) use ($date) {
                          $lq->whereDate('tanggal_inspeksi', '>', $date)
                             ->orWhere('status', 'menunggu_penetapan');
                      });
                })->count();

            $flowInboundLots[] = $inLots;
            $flowInboundQty[] = $inQty;
            $flowOutboundLots[] = $outLots;
            $flowOutboundQty[] = $outQty;
            $flowBacklogLots[] = $backlog;
        }

        // Summary Throughput Metrics 7 Hari
        $sumInboundLots = array_sum($flowInboundLots);
        $sumOutboundLots = array_sum($flowOutboundLots);
        $sumInboundQty = array_sum($flowInboundQty);
        $sumOutboundQty = array_sum($flowOutboundQty);
        $flowClearanceRate = $sumInboundLots > 0 ? round(($sumOutboundLots / $sumInboundLots) * 100, 1) : 100.0;

        // Distribusi Status Peninjauan Kedatangan Material (Untuk Donut Chart)
        $statusBreakdown = [
            'released' => Logbook::where('status', 'accept')->count(),
            'in_inspection' => Receiving::where('status_antrean', 'dalam_inspeksi')->whereDoesntHave('logbooks', function($q) { $q->where('status', 'menunggu_penetapan'); })->count(),
            'waiting' => Receiving::where('status_antrean', 'menunggu_inspeksi')->count(),
            'mrb_hold' => Logbook::where('status', 'menunggu_penetapan')->count(),
            'rejected' => Logbook::whereIn('status', ['return_to_vendor', 'scrap', 'rework'])->count(),
        ];

        // 7-Day Trend of Inspection Volume & Pass/Fail (Backward compatibility)
        $trendDates = $flowDates;
        $trendAccepted = [];
        $trendRejected = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $acc = Logbook::whereDate('tanggal_inspeksi', $date)
                ->where('status', 'accept')
                ->count();
            $rej = Logbook::whereDate('tanggal_inspeksi', $date)
                ->whereIn('status', ['return_to_vendor', 'scrap', 'rework', 'menunggu_penetapan'])
                ->count();

            $trendAccepted[] = $acc;
            $trendRejected[] = $rej;
        }

        // 5. Commodity Workload & Quality Yield Summary
        $commodityStats = [];
        $commodities = [
            'ee_part' => 'EE Part',
            'automotive' => 'Automotive',
            'wire_harness' => 'Wire Harness',
        ];
        foreach ($commodities as $key => $label) {
            $inboundComm = Receiving::whereHas('part', function ($q) use ($key) {
                $q->where('kategori_komoditas', $key);
            })->count();

            $completedComm = Logbook::whereHas('receiving.part', function ($q) use ($key) {
                $q->where('kategori_komoditas', $key);
            })->whereIn('status', ['accept', 'return_to_vendor', 'scrap', 'rework'])->count();

            $waitingComm = Receiving::where('status_antrean', '!=', 'selesai')
                ->whereHas('part', function ($q) use ($key) {
                    $q->where('kategori_komoditas', $key);
                })->count();

            $totalLogbookComm = Logbook::whereHas('receiving.part', function ($q) use ($key) {
                $q->where('kategori_komoditas', $key);
            })->count();

            $accComm = Logbook::where('status', 'accept')
                ->whereHas('receiving.part', function ($q) use ($key) {
                    $q->where('kategori_komoditas', $key);
                })->count();

            $pct = $totalLogbookComm > 0 ? round(($accComm / $totalLogbookComm) * 100, 1) : 100.0;
            $commodityStats[] = [
                'key' => $key,
                'label' => $label,
                'inbound' => $inboundComm,
                'completed' => $completedComm,
                'waiting' => $waitingComm,
                'total' => $totalLogbookComm,
                'accepted' => $accComm,
                'pass_rate' => $pct,
            ];
        }

        // 6. Incoming Materials Ready for Inspection (FIFO Priority Queue)
        $inboundQueueQuery = Receiving::with(['supplier', 'part.parameters', 'logbooks'])
            ->whereIn('status_antrean', ['menunggu_inspeksi', 'dalam_inspeksi']);

        if ($user->isInspector() && $user->getInspectorCommodity()) {
            $inboundQueueQuery->whereHas('part', function ($q) use ($user) {
                $q->where('kategori_komoditas', $user->getInspectorCommodity());
            });
        }
        $incomingBacklogQueue = $inboundQueueQuery->orderBy('tanggal_terima', 'asc')->take(8)->get();

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
            'flowDates',
            'flowInboundLots',
            'flowInboundQty',
            'flowOutboundLots',
            'flowOutboundQty',
            'flowBacklogLots',
            'sumInboundLots',
            'sumOutboundLots',
            'sumInboundQty',
            'sumOutboundQty',
            'flowClearanceRate',
            'statusBreakdown',
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
            'pendingCommodityReceivings',
            'incomingBacklogQueue'
        ));
    }
}
