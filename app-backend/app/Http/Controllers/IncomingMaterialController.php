<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use Illuminate\Http\Request;

class IncomingMaterialController extends Controller
{
    /**
     * Quick Scanner: look up a GRN number and redirect to the inspection form
     * with all fields pre-synced (part, lot, quantity).
     * If the GRN does not exist → redirect back with a "GRN not found" alert.
     */
    public function scanGrn(Request $request)
    {
        $code = strtoupper(trim($request->input('grn', '')));

        if (empty($code)) {
            return redirect()->route('dashboard')->with('error', 'Please enter or scan a GRN number.');
        }

        $material = IncomingMaterial::with('part')->where('grn_number', $code)->first();

        if (!$material) {
            // Check if scanned code matches a known part directly
            $partDirect = \App\Models\Part::where('part_number', $code)->first();
            if ($partDirect && (
                str_starts_with(strtoupper($partDirect->part_number), 'WH-') ||
                str_starts_with(strtoupper($partDirect->part_number), 'AUTO-') ||
                str_contains(strtoupper($partDirect->part_name), 'HARNESS') ||
                str_contains(strtoupper($partDirect->part_name), 'AUTOMOTIVE')
            )) {
                return redirect()->route('poka-yoke.denied', [
                    'part_number' => $partDirect->part_number,
                    'part_name'   => $partDirect->part_name,
                ]);
            }

            // GRN not found → flash a special key so the dashboard can show the alert modal
            return redirect()->route('dashboard')->with('grn_not_found', $code);
        }

        // POKA-YOKE MISTAKE-PROOFING: Reject Wire Harness & Automotive parts scanned by EE Part inspectors
        $part = $material->part;
        if ($part && (
            str_starts_with(strtoupper($part->part_number), 'WH-') ||
            str_starts_with(strtoupper($part->part_number), 'AUTO-') ||
            str_contains(strtoupper($part->part_name), 'HARNESS') ||
            str_contains(strtoupper($part->part_name), 'AUTOMOTIVE')
        )) {
            return redirect()->route('poka-yoke.denied', [
                'part_number' => $part->part_number,
                'part_name'   => $part->part_name,
                'grn'         => $material->grn_number,
            ]);
        }

        if ($material->status !== 'waiting') {
            return redirect()->route('dashboard')
                ->with('info', "GRN {$code} has already been inspected (status: {$material->status}).");
        }

        // GRN found & waiting → redirect directly to inspection form, synced with part & GRN data
        return redirect()->route('inspections.create', [
            'incoming_id' => $material->id,
            'grn'         => $material->grn_number,
            'part_id'     => $material->part_id,
            'qty'         => $material->quantity,
            'lot'         => $material->lot_number,
            'locked'      => 1,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grn_number' => 'required|string|max:50|unique:incoming_materials,grn_number',
            'part_id'    => 'required|exists:parts,id',
            'lot_number' => 'nullable|string|max:50',
            'quantity'   => 'required|integer|min:1',
            'arrived_at' => 'required|date',
        ]);

        $validated['grn_number'] = strtoupper(trim($validated['grn_number']));
        $validated['status'] = 'waiting';
        $validated['registered_by'] = auth()->id();

        IncomingMaterial::create($validated);

        return redirect()->route('dashboard')
            ->with('success', "GRN {$validated['grn_number']} registered to the IQC inspection queue.");
    }

    public function destroy(IncomingMaterial $incomingMaterial)
    {
        if ($incomingMaterial->status !== 'waiting') {
            return back()->with('error', 'Only GRNs still waiting in the queue can be removed.');
        }

        $grn = $incomingMaterial->grn_number;
        $incomingMaterial->delete();

        return redirect()->route('dashboard')->with('success', "GRN {$grn} removed from the queue.");
    }
}
