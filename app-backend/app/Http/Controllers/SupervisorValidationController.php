<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorValidationController extends Controller
{
    public function index()
    {
        // Poka-Yoke Logic: Only show inspections requiring Supervisor Disposition (Reject / Out of Spec / Defects)
        $pendingInspections = Inspection::with(['part', 'inspector', 'measurements', 'defects'])
            ->where('status', 'pending_approval')
            ->where(function ($q) {
                $q->whereHas('measurements', function ($mq) {
                    $mq->where('is_pass', false);
                })
                ->orWhereHas('defects')
                ->orWhereNotNull('id'); // Ensure all pending approval items are reviewed
            })
            ->latest()
            ->paginate(10);

        $recentValidations = Inspection::with(['part', 'inspector', 'supervisor'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        return view('supervisor.validation', compact('pendingInspections', 'recentValidations'));
    }

    public function show($id)
    {
        if (Auth::user()->role !== 'supervisor') {
            return response()->view('poka-yoke.denied', [
                'rule_violated' => 'Sistem Poka-Yoke: Inspektur dan Administrator dilarang mengakses form evaluasi disposisi penolakan material. Wewenang mutlak milik IQC Supervisor.',
                'user_role' => Auth::user()->role,
                'target_resource' => 'Detail Evaluasi Disposisi SPV',
            ], 403);
        }

        $inspection = Inspection::with(['part', 'inspector', 'measurements', 'defects'])->findOrFail($id);
        
        $totalMeasurements = $inspection->measurements->count();
        $failedMeasurements = $inspection->measurements->where('is_pass', false)->count();
        $fatalityRate = $totalMeasurements > 0 ? round(($failedMeasurements / $totalMeasurements) * 100, 1) : 0;
        $isFatalNCR = ($totalMeasurements > 0 && $failedMeasurements === $totalMeasurements) || ($inspection->defects->sum('quantity') >= $inspection->sample_size && $inspection->sample_size > 0);

        return view('supervisor.review', compact('inspection', 'fatalityRate', 'isFatalNCR'));
    }

    public function decide(Request $request, $id)
    {
        // Strict Poka-Yoke Authority Check: Only SPV can approve or decide
        if (Auth::user()->role !== 'supervisor') {
            return response()->view('poka-yoke.denied', [
                'rule_violated' => 'Poka-Yoke System: Inspectors and Administrators are prohibited from approving material rejection dispositions. This authority belongs exclusively to the IQC Supervisor.',
                'user_role' => Auth::user()->role,
                'target_resource' => 'Supervisor Approval & Disposition',
            ], 403);
        }

        $inspection = Inspection::with(['measurements', 'defects'])->findOrFail($id);
        
        $totalMeasurements = $inspection->measurements->count();
        $failedMeasurements = $inspection->measurements->where('is_pass', false)->count();
        $isFatalNCR = ($totalMeasurements > 0 && $failedMeasurements === $totalMeasurements) || ($inspection->defects->sum('quantity') >= $inspection->sample_size && $inspection->sample_size > 0);

        if ($isFatalNCR && !in_array($request->disposition, ['return_to_vendor', 'scrap'])) {
            return back()->withErrors([
                'disposition' => 'Poka-Yoke Enforced: 100% Fatality Rate detected! Material with total sample failure CANNOT be accepted or sent to MRB/Rework. It MUST be dispositioned as Return to Vendor (RTV) or Internal Scrap.'
            ])->withInput();
        }

        $request->validate([
            'disposition' => 'required|in:accept,return_to_vendor,scrap,mrb,rework',
            'disposition_notes' => 'required|string|max:1000',
        ]);

        $dispositionNotes = $request->disposition_notes;
        if ($isFatalNCR && !str_contains($dispositionNotes, '100% Fatality')) {
            $dispositionNotes = "[CRITICAL NCR - 100% FATALITY RATE]: " . $dispositionNotes;
        }

        $status = ($request->disposition === 'accept') ? 'approved' : 'rejected';

        $inspection->update([
            'supervisor_id' => Auth::id(),
            'disposition' => $request->disposition,
            'disposition_notes' => $dispositionNotes,
            'status' => $status,
        ]);

        $statusText = strtoupper(str_replace('_', ' ', $request->disposition));
        return redirect()->route('supervisor.validation')
            ->with('success', "Disposition for Lot #{$inspection->lot_number} successfully authorized by Supervisor as [{$statusText}].");
    }
}

