<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\Inspection;
use App\Models\InspectionMeasurement;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with(['part', 'inspector', 'supervisor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('lot_number', 'like', "%{$search}%")
                  ->orWhereHas('part', function ($pq) use ($search) {
                      $pq->where('part_name', 'like', "%{$search}%")
                         ->orWhere('part_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('part_id')) {
            $query->where('part_id', $request->part_id);
        }

        $inspections = $query->latest()->paginate(15);
        $parts = Part::all();

        return view('inspections.index', compact('inspections', 'parts'));
    }

    public function create(Request $request)
    {
        $parts = Part::all();
        $selectedPart = null;
        $incomingMaterial = null;

        if ($request->filled('incoming_id')) {
            $incomingMaterial = IncomingMaterial::with('part')->find($request->incoming_id);
            if ($incomingMaterial && !$request->filled('part_id')) {
                $selectedPart = $incomingMaterial->part;
            }
        }

        if (!$selectedPart && $request->filled('part_id')) {
            $selectedPart = Part::find($request->part_id);
        }

        $isGrnLocked = $request->boolean('locked') || $request->filled('incoming_id') || $request->filled('grn');

        return view('inspections.create', compact('parts', 'selectedPart', 'incomingMaterial', 'isGrnLocked'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:parts,id',
            'po_number' => 'required|string|max:50',
            'lot_number' => 'required|string|max:50',
            'lot_quantity' => 'required|integer|min:1',
            'sample_size' => 'required|integer|min:1',
            'inspection_date' => 'required|date',
            'measurements' => 'required|array|min:1',
            'measurements.*' => 'required|numeric',
            'incoming_id' => 'nullable|integer|exists:incoming_materials,id',
        ]);

        if (!empty($validated['incoming_id'])) {
            $incoming = IncomingMaterial::find($validated['incoming_id']);
            if ($incoming) {
                $validated['part_id'] = $incoming->part_id;
                $validated['po_number'] = $incoming->grn_number;
                if ($incoming->lot_number) {
                    $validated['lot_number'] = $incoming->lot_number;
                }
            }
        }

        $part = Part::findOrFail($validated['part_id']);

        $hasFailure = false;
        $measurementsData = [];

        foreach ($validated['measurements'] as $index => $actualVal) {
            $actual = floatval($actualVal);
            $deviation = round($actual - $part->nominal_dimension, 3);
            $isPass = ($actual >= $part->tolerance_min && $actual <= $part->tolerance_max);

            if (!$isPass) {
                $hasFailure = true;
            }

            $measurementsData[] = [
                'sample_number' => $index + 1,
                'actual_value' => $actual,
                'deviation' => $deviation,
                'is_pass' => $isPass,
                'notes' => $isPass ? 'OK' : 'Dimensi di luar batas toleransi spesifikasi',
            ];
        }

        $inspection = Inspection::create([
            'part_id' => $part->id,
            'inspector_id' => Auth::id(),
            'po_number' => $validated['po_number'],
            'lot_number' => $validated['lot_number'],
            'lot_quantity' => $validated['lot_quantity'],
            'sample_size' => $validated['sample_size'],
            'inspection_date' => $validated['inspection_date'],
            'status' => $hasFailure ? 'pending_approval' : 'approved',
            'disposition' => $hasFailure ? null : 'accept',
            'disposition_notes' => $hasFailure ? null : 'Lolos otomatis - seluruh sampel memenuhi batas toleransi drawing.',
        ]);

        foreach ($measurementsData as $mData) {
            $mData['inspection_id'] = $inspection->id;
            InspectionMeasurement::create($mData);
        }

        if (!empty($validated['incoming_id'])) {
            IncomingMaterial::where('id', $validated['incoming_id'])
                ->where('status', 'waiting')
                ->update(['status' => 'inspected', 'inspection_id' => $inspection->id]);
        }

        $msg = $hasFailure
            ? 'Inspeksi tersimpan! Terdapat sampel Out of Spec / Cacat. Status diteruskan ke Panel Supervisor untuk evaluasi disposisi penolakan.'
            : 'Inspeksi berhasil (PASS)! Seluruh sampel memenuhi batas toleransi spesifikasi.';

        return redirect()->route('inspections.show', $inspection->id)->with('success', $msg);
    }


    public function show($id)
    {
        $inspection = Inspection::with(['part', 'inspector', 'supervisor', 'measurements', 'defects'])->findOrFail($id);
        return view('inspections.show', compact('inspection'));
    }
}
