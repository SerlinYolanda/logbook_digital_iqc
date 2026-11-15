<?php

namespace App\Http\Controllers;

use App\Models\Receiving;
use App\Models\Supplier;
use App\Models\Part;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReceivingController extends Controller
{
    /**
     * UC-02: Daftarkan Kedatangan Material (GRN) & Pantau Antrean
     */
    public function index(Request $request)
    {
        $query = Receiving::with(['supplier', 'part', 'logbooks.inspector']);

        if ($request->filled('status_antrean')) {
            $query->where('status_antrean', $request->status_antrean);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('komoditas')) {
            $query->whereHas('part', function ($q) use ($request) {
                $q->where('kategori_komoditas', $request->komoditas);
            });
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('no_grn_iqa', 'like', $search)
                  ->orWhere('no_po', 'like', $search)
                  ->orWhere('no_lot', 'like', $search)
                  ->orWhereHas('part', function ($pq) use ($search) {
                      $pq->where('kode_part', 'like', $search)
                         ->orWhere('nama_part', 'like', $search);
                  });
            });
        }

        $receivings = $query->orderByRaw("
            CASE 
                WHEN status_antrean = 'menunggu_inspeksi' THEN 1
                WHEN status_antrean = 'dalam_inspeksi' THEN 2
                ELSE 3
            END, tanggal_terima ASC
        ")->paginate(15)->withQueryString();

        // Queue Statistics
        $totalArrivals = Receiving::count();
        $waitingCount = Receiving::where('status_antrean', 'menunggu_inspeksi')->count();
        $inProgressCount = Receiving::where('status_antrean', 'dalam_inspeksi')->count();
        $completedCount = Receiving::where('status_antrean', 'selesai')->count();

        // Overdue count (> 24 hours waiting)
        $overdueCount = Receiving::where('status_antrean', 'menunggu_inspeksi')
            ->where('tanggal_terima', '<=', now()->subHours(24))
            ->count();

        $suppliers = Supplier::where('status_aktif', true)->orderBy('nama_supplier')->get();
        $parts = Part::orderBy('nama_part')->get();

        return view('receivings.index', compact(
            'receivings',
            'totalArrivals',
            'waitingCount',
            'inProgressCount',
            'completedCount',
            'overdueCount',
            'suppliers',
            'parts'
        ));
    }

    /**
     * UC-02: Register Inbound Material
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_grn_iqa'     => 'required|string|unique:receivings,no_grn_iqa|max:50',
            'tanggal_terima' => 'required|date',
            'supplier_id'    => 'required|exists:suppliers,id',
            'part_id'        => 'required|exists:parts,id',
            'no_po'          => 'required|string|max:50',
            'no_lot'         => 'required|string|max:50',
            'qty_datang'     => 'required|integer|min:1',
        ]);

        $validated['status_antrean'] = 'menunggu_inspeksi';

        $receiving = Receiving::create($validated);

        AuditTrail::log(
            'CREATE_GRN',
            'Inbound GRN: ' . $receiving->no_grn_iqa,
            null,
            "Lot: {$receiving->no_lot}, Part ID: {$receiving->part_id}, Qty: {$receiving->qty_datang}"
        );

        return redirect()->route('receivings.index')
            ->with('success', "Inbound Material GRN #{$receiving->no_grn_iqa} has been registered to the inspection queue!");
    }

    /**
     * Delete inbound material if not yet inspected
     */
    public function destroy(Receiving $receiving)
    {
        if ($receiving->status_antrean !== 'menunggu_inspeksi') {
            return back()->with('error', 'Materials currently undergoing or already inspected cannot be deleted.');
        }

        $grnNum = $receiving->no_grn_iqa;
        $receiving->delete();

        AuditTrail::log(
            'DELETE_GRN',
            'Deleted GRN: ' . $grnNum,
            "Lot: {$receiving->no_lot}, Part ID: {$receiving->part_id}",
            'Deleted from queue'
        );

        return redirect()->route('receivings.index')
            ->with('success', "Inbound material #{$grnNum} has been removed from the queue.");
    }
}

