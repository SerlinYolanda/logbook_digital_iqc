<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\Supplier;
use App\Models\Part;
use App\Models\Defect;
use App\Models\DefectCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * UC-12: Buat Laporan Rekapitulasi (Supervisor IQC)
     */
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->filled('end_date')   ? $request->end_date   : Carbon::now()->toDateString();
        $status    = $request->input('status');
        $komoditas = $request->input('komoditas');
        $supplierId = $request->input('supplier_id');
        $search    = $request->input('search');

        $query = Logbook::with([
            'receiving.supplier',
            'receiving.part',
            'inspector',
            'defects.defectCategory',
            'latestMrbDisposition.supervisor'
        ]);

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_inspeksi', '>=', $startDate);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_inspeksi', '<=', $endDate);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($komoditas)) {
            $query->whereHas('receiving.part', function ($q) use ($komoditas) {
                $q->where('kategori_komoditas', $komoditas);
            });
        }

        if (!empty($supplierId)) {
            $query->whereHas('receiving', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
        }

        if (!empty($search)) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('receiving', function ($rq) use ($term) {
                    $rq->where('no_grn_iqa', 'like', $term)
                       ->orWhere('no_lot', 'like', $term)
                       ->orWhere('no_po', 'like', $term)
                       ->orWhereHas('part', fn($pq) => $pq->where('kode_part', 'like', $term)->orWhere('nama_part', 'like', $term))
                       ->orWhereHas('supplier', fn($sq) => $sq->where('nama_supplier', 'like', $term));
                })
                ->orWhereHas('inspector', fn($iq) => $iq->where('name', 'like', $term));
            });
        }

        $logbooks = $query->orderBy('tanggal_inspeksi', 'desc')->get();

        // Summary Calculations
        $totalLots = $logbooks->count();
        $totalAccepted = $logbooks->where('status', 'accept')->count();
        $totalRtv = $logbooks->where('status', 'return_to_vendor')->count();
        $totalScrap = $logbooks->where('status', 'scrap')->count();
        $totalRework = $logbooks->where('status', 'rework')->count();
        $totalPending = $logbooks->where('status', 'menunggu_penetapan')->count();

        $passRate = $totalLots > 0 ? round(($totalAccepted / $totalLots) * 100, 1) : 100.0;

        // Defect Summary in this period
        $logbookIds = $logbooks->pluck('id');
        $defectsSummary = Defect::whereIn('logbook_id', $logbookIds)
            ->select('defect_category_id', DB::raw('SUM(qty_defect) as total_qty'), DB::raw('COUNT(*) as occurrences'))
            ->groupBy('defect_category_id')
            ->with('defectCategory')
            ->orderBy('total_qty', 'desc')
            ->get();

        $suppliers = Supplier::where('status_aktif', true)->orderBy('nama_supplier')->get();

        return view('reports.index', compact(
            'logbooks',
            'totalLots',
            'totalAccepted',
            'totalRtv',
            'totalScrap',
            'totalRework',
            'totalPending',
            'passRate',
            'defectsSummary',
            'suppliers',
            'startDate',
            'endDate',
            'status',
            'komoditas',
            'supplierId',
            'search'
        ));
    }

    /**
     * Export styled Excel (.xls / CSV) report (UC-12)
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->filled('end_date')   ? $request->end_date   : Carbon::now()->toDateString();
        $status    = $request->input('status');
        $komoditas = $request->input('komoditas');
        $supplierId = $request->input('supplier_id');
        $search    = $request->input('search');

        $query = Logbook::with([
            'receiving.supplier',
            'receiving.part',
            'inspector',
            'defects.defectCategory',
            'latestMrbDisposition.supervisor'
        ]);

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_inspeksi', '>=', $startDate);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_inspeksi', '<=', $endDate);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }
        if (!empty($komoditas)) {
            $query->whereHas('receiving.part', function ($q) use ($komoditas) {
                $q->where('kategori_komoditas', $komoditas);
            });
        }
        if (!empty($supplierId)) {
            $query->whereHas('receiving', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
        }
        if (!empty($search)) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('receiving', function ($rq) use ($term) {
                    $rq->where('no_grn_iqa', 'like', $term)
                       ->orWhere('no_lot', 'like', $term)
                       ->orWhere('no_po', 'like', $term)
                       ->orWhereHas('part', fn($pq) => $pq->where('kode_part', 'like', $term)->orWhere('nama_part', 'like', $term))
                       ->orWhereHas('supplier', fn($sq) => $sq->where('nama_supplier', 'like', $term));
                })
                ->orWhereHas('inspector', fn($iq) => $iq->where('name', 'like', $term));
            });
        }

        $logbooks = $query->orderBy('tanggal_inspeksi', 'asc')->get();

        $format = $request->input('format', 'xls');
        $filename = 'Laporan_Rekapitulasi_IQC_' . Carbon::now()->format('Ymd_His');

        if ($format === 'xls') {
            return response()
                ->view('reports.excel', [
                    'logbooks'  => $logbooks,
                    'startDate' => $startDate,
                    'endDate'   => $endDate,
                    'generatedAt' => Carbon::now()->translatedFormat('d F Y H:i:s'),
                ])
                ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xls"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        // CSV export with UTF-8 BOM
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($logbooks) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fwrite($file, "sep=,\r\n");

            fputcsv($file, [
                'No',
                'No GRN IQA',
                'No PO',
                'No Lot',
                'Kode Part',
                'Nama Komponen',
                'Komoditas',
                'Supplier / Vendor',
                'Negara Asal',
                'Qty Datang',
                'Qty Sampel',
                'Tanggal Inspeksi',
                'Inspektur IQC',
                'Status Keputusan',
                'Disposisi MRB',
                'Catatan Supervisor',
            ]);

            $no = 1;
            foreach ($logbooks as $log) {
                $rcv = $log->receiving;
                $mrb = $log->latestMrbDisposition;

                fputcsv($file, [
                    $no++,
                    $rcv->no_grn_iqa ?? '-',
                    $rcv->no_po ?? '-',
                    $rcv->no_lot ?? '-',
                    $rcv->part->kode_part ?? '-',
                    $rcv->part->nama_part ?? '-',
                    $rcv->part->komoditas_label ?? '-',
                    $rcv->supplier->nama_supplier ?? '-',
                    $rcv->supplier->negara_asal ?? '-',
                    $rcv->qty_datang ?? 0,
                    $log->qty_sampel ?? 0,
                    Carbon::parse($log->tanggal_inspeksi)->format('Y-m-d H:i'),
                    $log->inspector->name ?? '-',
                    $log->status_label,
                    $mrb ? $mrb->decision_label : '-',
                    $mrb ? $mrb->catatan : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
