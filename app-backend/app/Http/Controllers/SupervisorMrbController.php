<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\MrbDisposition;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class SupervisorMrbController extends Controller
{
    /**
     * UC-09: Verifikasi Logbook & Antrean Pending MRB
     */
    public function index(Request $request)
    {
        $pendingQuery = Logbook::with([
            'receiving.supplier',
            'receiving.part',
            'inspector',
            'defects.defectCategory',
            'nestProcess'
        ])->where('status', 'menunggu_penetapan');

        if ($request->filled('komoditas')) {
            $pendingQuery->whereHas('receiving.part', function ($q) use ($request) {
                $q->where('kategori_komoditas', $request->komoditas);
            });
        }

        $pendingReviews = $pendingQuery->orderBy('tanggal_inspeksi', 'asc')->get();

        // Recent MRB Decisions
        $recentDecisions = MrbDisposition::with([
            'logbook.receiving.supplier',
            'logbook.receiving.part',
            'supervisor'
        ])->orderBy('diputuskan_pada', 'desc')->paginate(10);

        $pendingCount = $pendingReviews->count();
        $totalDecided = MrbDisposition::count();

        return view('supervisor.mrb.index', compact('pendingReviews', 'recentDecisions', 'pendingCount', 'totalDecided'));
    }

    /**
     * UC-09 & UC-10: Detailed Verification & MRB Disposition Form
     */
    public function show(Logbook $logbook)
    {
        $logbook->load([
            'receiving.supplier',
            'receiving.part.parameters',
            'inspector',
            'measurements.partParameter',
            'defects.defectCategory',
            'nestProcess.inspector',
            'mrbDispositions.supervisor'
        ]);

        return view('supervisor.mrb.show', compact('logbook'));
    }

    /**
     * UC-10: Tetapkan Disposisi MRB (Accept, Return to Vendor, Scrap, Rework, Perlu Revisi)
     */
    public function decide(Request $request, Logbook $logbook)
    {
        $validated = $request->validate([
            'status_keputusan' => 'required|in:accept,return_to_vendor,scrap,rework,perlu_revisi',
            'catatan'          => 'required|string|min:5|max:1000',
        ]);

        $prevStatus = $logbook->status;

        $disposition = MrbDisposition::create([
            'logbook_id'       => $logbook->id,
            'supervisor_id'    => auth()->id(),
            'status_keputusan' => $validated['status_keputusan'],
            'catatan'          => $validated['catatan'],
            'diputuskan_pada'  => now(),
        ]);

        // Update Logbook status
        $logbook->update(['status' => $validated['status_keputusan']]);

        // Update Receiving queue status
        if (in_array($validated['status_keputusan'], ['accept', 'return_to_vendor', 'scrap', 'rework'])) {
            $logbook->receiving->update(['status_antrean' => 'selesai']);
        } elseif ($validated['status_keputusan'] === 'perlu_revisi') {
            $logbook->receiving->update(['status_antrean' => 'dalam_inspeksi']);
        }

        AuditTrail::log(
            'MRB_DISPOSITION',
            "Logbook #{$logbook->id} (GRN: {$logbook->receiving->no_grn_iqa}, Lot: {$logbook->receiving->no_lot})",
            "Previous Status: {$prevStatus}",
            "Decision: {$disposition->decision_label}. Notes: {$validated['catatan']}"
        );

        $msg = match ($validated['status_keputusan']) {
            'perlu_revisi' => "Logbook returned to Inspector ({$logbook->inspector->name}) for measurement revision.",
            'accept' => "MRB Disposition: Material approved (ACCEPT / Use As Is) for release to production.",
            'return_to_vendor' => "MRB Disposition: RETURN TO VENDOR (RTV) assigned. Documentation prepared for logistics/purchasing.",
            'scrap' => "MRB Disposition: SCRAP assigned. Material transferred to scrap quarantine.",
            'rework' => "MRB Disposition: REWORK assigned. Material scheduled for rework.",
        };

        return redirect()->route('supervisor.mrb.index')->with('success', $msg);
    }
}

