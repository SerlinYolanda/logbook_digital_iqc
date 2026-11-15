<?php

namespace App\Http\Controllers;

use App\Models\Receiving;
use App\Models\Supplier;
use App\Models\Part;
use Illuminate\Http\Request;

class MaterialTraceabilityController extends Controller
{
    /**
     * UC-08: Cari Riwayat Material (Material Traceability Portal)
     * Actors: Inspector IQC, Supervisor IQC, Master Data / Logistik
     */
    public function index(Request $request)
    {
        $query = Receiving::with([
            'supplier',
            'part',
            'logbooks.inspector',
            'logbooks.measurements.partParameter',
            'logbooks.defects.defectCategory',
            'logbooks.nestProcess',
            'logbooks.mrbDispositions.supervisor',
        ]);

        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('no_lot', 'like', $s)
                  ->orWhere('no_grn_iqa', 'like', $s)
                  ->orWhere('no_po', 'like', $s)
                  ->orWhereHas('part', function ($pq) use ($s) {
                      $pq->where('kode_part', 'like', $s)
                         ->orWhere('nama_part', 'like', $s);
                  })
                  ->orWhereHas('supplier', function ($sq) use ($s) {
                      $sq->where('nama_supplier', 'like', $s)
                         ->orWhere('negara_asal', 'like', $s);
                  });
            });
        }

        if ($request->filled('komoditas')) {
            $query->whereHas('part', function ($q) use ($request) {
                $q->where('kategori_komoditas', $request->komoditas);
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('negara_asal')) {
            $query->whereHas('supplier', function ($q) use ($request) {
                $q->where('negara_asal', $request->negara_asal);
            });
        }

        if ($request->filled('status')) {
            $st = $request->status;
            if (in_array($st, ['menunggu_inspeksi', 'dalam_inspeksi'])) {
                $query->where('status_antrean', $st);
            } else {
                $query->whereHas('logbooks', function ($lq) use ($st) {
                    $lq->where('status', $st);
                });
            }
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_terima', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_terima', '<=', $request->tanggal_sampai);
        }

        $results = $query->orderBy('tanggal_terima', 'desc')->paginate(15)->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $countries = Supplier::select('negara_asal')->distinct()->pluck('negara_asal');

        return view('traceability.index', compact('results', 'suppliers', 'countries'));
    }

    /**
     * Detailed Material Traceability Journey
     */
    public function show($id)
    {
        $receiving = Receiving::with([
            'supplier',
            'part.parameters',
            'logbooks.inspector',
            'logbooks.measurements.partParameter',
            'logbooks.defects.defectCategory',
            'logbooks.nestProcess.inspector',
            'logbooks.mrbDispositions.supervisor',
        ])->findOrFail($id);

        return view('traceability.show', compact('receiving'));
    }
}

