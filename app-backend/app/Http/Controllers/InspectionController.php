<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\Inspection;
use App\Models\InspectionMeasurement;
use App\Models\InspectionPhoto;
use App\Models\Instrument;
use App\Models\Part;
use App\Models\PartInspectionParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $query = Inspection::with(['part', 'inspector', 'supervisor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('lot_number', 'like', "%{$search}%")
                  ->orWhereHas('part', function ($pq) use ($search) {
                      $pq->where('part_name', 'like', "%{$search}%")
                         ->orWhere('part_number', 'like', "%{$search}%")
                         ->orWhere('supplier_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('disposition') && $request->disposition !== 'all') {
            $query->where('disposition', $request->disposition);
        }

        if ($request->filled('part_id') && $request->part_id !== 'all') {
            $query->where('part_id', $request->part_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('inspection_date', $request->date);
        }

        if ($request->filled('period')) {
            if ($request->period === 'today') {
                $query->whereDate('inspection_date', now()->today());
            } elseif ($request->period === 'this_week') {
                $query->whereBetween('inspection_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($request->period === 'this_month') {
                $query->whereMonth('inspection_date', now()->month)->whereYear('inspection_date', now()->year);
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $inspections = $query->latest('inspection_date')->latest('id')->paginate(15)->withQueryString();
        $parts = Part::orderBy('part_number')->get();

        $statusCounts = [
            'all' => Inspection::count(),
            'pending_approval' => Inspection::where('status', 'pending_approval')->count(),
            'approved' => Inspection::where('status', 'approved')->count(),
            'rejected' => Inspection::where('status', 'rejected')->count(),
        ];

        return view('inspections.index', compact('inspections', 'parts', 'statusCounts'));
    }

    public function printArchive(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $inspections = $query->latest('inspection_date')->latest('id')->get();
        $supervisor = \App\Models\User::where('role', 'supervisor')->first();

        $summary = [
            'totalLots' => $inspections->count(),
            'passedLots' => $inspections->where('status', 'approved')->count(),
            'rejectedLots' => $inspections->where('status', 'rejected')->count(),
            'pendingLots' => $inspections->where('status', 'pending_approval')->count(),
            'totalSamples' => $inspections->sum('sample_size'),
            'totalLotQty' => $inspections->sum('lot_quantity'),
        ];

        return view('inspections.print_archive', compact('inspections', 'summary', 'supervisor'));
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $inspections = $query->latest('inspection_date')->latest('id')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="IQC_Logbook_Archive_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($inspections) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'No',
                'Inspection Date',
                'PO / GRN Number',
                'Vendor Lot Number',
                'Part Number',
                'Part Name',
                'Supplier / Manufacturer',
                'Inspection Mode',
                'Lot Quantity (pcs)',
                'Sample Size (pcs)',
                'Quality Status',
                'Final Disposition',
                'Inspector',
                'Supervisor Authorized',
                'Disposition Notes',
            ]);

            foreach ($inspections as $idx => $ins) {
                fputcsv($file, [
                    $idx + 1,
                    $ins->inspection_date ? $ins->inspection_date->format('Y-m-d') : '-',
                    $ins->po_number,
                    $ins->lot_number,
                    $ins->part->part_number ?? '-',
                    $ins->part->part_name ?? '-',
                    $ins->part->supplier_name ?? '-',
                    $ins->inspection_type === 'visual' ? 'Visual & Packaging (JEDEC)' : 'Physical Dimensional Measurement',
                    $ins->lot_quantity,
                    $ins->sample_size,
                    strtoupper($ins->status),
                    strtoupper(str_replace('_', ' ', $ins->disposition ?? '-')),
                    $ins->inspector->name ?? '-',
                    $ins->supervisor->name ?? '-',
                    $ins->disposition_notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create(Request $request)
    {
        $parts = Part::with(['parameters.instrument'])->get();
        $selectedPart = null;
        $incomingMaterial = null;

        if ($request->filled('incoming_id')) {
            $incomingMaterial = IncomingMaterial::with(['part.parameters.instrument'])->find($request->incoming_id);
            if ($incomingMaterial && !$request->filled('part_id')) {
                $selectedPart = $incomingMaterial->part;
            }
        }

        if (!$selectedPart && $request->filled('part_id')) {
            $selectedPart = Part::with(['parameters.instrument'])->find($request->part_id);
        }

        // POKA-YOKE MISTAKE-PROOFING: EE Part inspector cross-section defect prevention
        if ($selectedPart && (
            str_starts_with(strtoupper($selectedPart->part_number), 'WH-') ||
            str_starts_with(strtoupper($selectedPart->part_number), 'AUTO-') ||
            str_contains(strtoupper($selectedPart->part_name), 'HARNESS') ||
            str_contains(strtoupper($selectedPart->part_name), 'AUTOMOTIVE')
        )) {
            return redirect()->route('poka-yoke.denied', [
                'part_number' => $selectedPart->part_number,
                'part_name'   => $selectedPart->part_name,
                'grn'         => $incomingMaterial->grn_number ?? $request->input('grn'),
            ]);
        }

        $isGrnLocked = $request->boolean('locked') || $request->filled('incoming_id') || $request->filled('grn');
        $instruments = Instrument::orderBy('instrument_code')->get();

        // Pre-format instruments list as a plain array so Blade @json() receives
        // a simple collection instead of a multi-line closure (avoids ParseError).
        $instrumentsList = $instruments->map(function ($ins) {
            return [
                'id'    => $ins->id,
                'code'  => $ins->instrument_code,
                'name'  => $ins->name,
                'label' => $ins->name . ' (' . $ins->instrument_code . ')',
            ];
        })->values()->toArray();

        // Pre-format parts payload keyed by part ID for instant client-side inspection parameter loading
        $partsPayload = $parts->keyBy('id')->map(function ($p) {
            return [
                'id' => $p->id,
                'part_number' => $p->part_number,
                'part_name' => $p->part_name,
                'supplier_name' => $p->supplier_name,
                'aql_level' => $p->aql_level,
                'sampling_level' => $p->sampling_level,
                'nominal_dimension' => (float) $p->nominal_dimension,
                'tolerance_min' => (float) $p->tolerance_min,
                'tolerance_max' => (float) $p->tolerance_max,
                'unit' => $p->unit ?? 'mm',
                'parameters' => $p->parameters->map(function ($param) {
                    return [
                        'id' => $param->id,
                        'parameter_name' => $param->parameter_name,
                        'nominal_dimension' => (float) $param->nominal_dimension,
                        'tolerance_min' => (float) $param->tolerance_min,
                        'tolerance_max' => (float) $param->tolerance_max,
                        'unit' => $param->unit ?? 'mm',
                        'is_critical' => (bool) $param->is_critical,
                        'instrument' => $param->instrument ? [
                            'id' => $param->instrument->id,
                            'instrument_code' => $param->instrument->instrument_code,
                            'name' => $param->instrument->name,
                            'measuring_range' => $param->instrument->measuring_range,
                        ] : null,
                    ];
                })->values()->toArray(),
            ];
        })->toArray();

        return view('inspections.create', compact('parts', 'selectedPart', 'incomingMaterial', 'isGrnLocked', 'instruments', 'instrumentsList', 'partsPayload'));
    }

    public function store(Request $request)
    {
        $isVisualOnly = ($request->input('inspection_type') === 'visual') || (!$request->has('measurements') || empty($request->input('measurements')));

        $rules = [
            'part_id' => 'required|exists:parts,id',
            'po_number' => 'required|string|max:50',
            'lot_number' => 'required|string|max:50',
            'lot_quantity' => 'required|integer|min:1',
            'sample_size' => 'required|integer|min:1',
            'inspection_date' => 'required|date',
            'inspection_type' => 'nullable|string|in:visual,dimensional',
            'incoming_id' => 'nullable|integer|exists:incoming_materials,id',
        ];

        if (!$isVisualOnly) {
            $rules['measurements'] = 'required|array|min:1';
            $rules['measurements.*'] = 'required|numeric';
            $rules['parameter_id'] = 'nullable|exists:part_inspection_parameters,id';
            $rules['parameter_name'] = 'nullable|string|max:150';
            $rules['measurement_tool'] = 'nullable|string|max:150';
            $rules['parameter_names'] = 'nullable|array';
            $rules['measurement_tools'] = 'nullable|array';
        }

        $validated = $request->validate($rules);

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

        $part = Part::with('parameters.instrument')->findOrFail($validated['part_id']);

        // POKA-YOKE MISTAKE-PROOFING: Reject submission if part belongs to Wire Harness / Automotive
        if (
            str_starts_with(strtoupper($part->part_number), 'WH-') ||
            str_starts_with(strtoupper($part->part_number), 'AUTO-') ||
            str_contains(strtoupper($part->part_name), 'HARNESS') ||
            str_contains(strtoupper($part->part_name), 'AUTOMOTIVE')
        ) {
            return redirect()->route('poka-yoke.denied', [
                'part_number' => $part->part_number,
                'part_name'   => $part->part_name,
                'grn'         => $validated['po_number'] ?? null,
            ]);
        }

        $hasFailure = false;
        $measurementsData = [];

        if ($isVisualOnly) {
            // Visual & Packaging Verification Checkpoints (Ideal for IC, Capacitors, Resistors, Diodes)
            $checkpoints = [
                [
                    'name' => 'Part Number & Marking Verification',
                    'tool' => 'Visual Inspection (10x Loupe)',
                    'is_pass' => $request->input('visual_marking_pass', '1') === '1',
                    'notes' => $request->input('visual_marking_notes') ?: 'Reel label & component marking match PO/Data Sheet',
                ],
                [
                    'name' => 'Packaging & Moisture/ESD Protection',
                    'tool' => 'Visual & Vacuum Seal Inspection',
                    'is_pass' => $request->input('visual_packaging_pass', '1') === '1',
                    'notes' => $request->input('visual_packaging_notes') ?: 'Tape/Reel intact, MBB vacuum sealed, HIC < 10% valid',
                ],
                [
                    'name' => 'Physical Appearance & Lead/Pin Integrity',
                    'tool' => 'Visual / Microscope (40x)',
                    'is_pass' => $request->input('visual_pins_pass', '1') === '1',
                    'notes' => $request->input('visual_pins_notes') ?: 'Lead coplanarity verified, zero oxidation, package intact',
                ],
                [
                    'name' => 'Manufacturer CoC & RoHS Compliance',
                    'tool' => 'Document Verification',
                    'is_pass' => $request->input('visual_coc_pass', '1') === '1',
                    'notes' => $request->input('visual_coc_notes') ?: 'Manufacturer CoC & RoHS compliance certificates verified valid',
                ],
            ];

            $sampleNum = 1;
            foreach ($checkpoints as $cp) {
                if (!$cp['is_pass']) {
                    $hasFailure = true;
                }

                $measurementsData[] = [
                    'sample_number' => $sampleNum++,
                    'parameter_name' => $cp['name'],
                    'measurement_tool' => $cp['tool'],
                    'actual_value' => $cp['is_pass'] ? 1.000 : 0.000,
                    'nominal_value' => 1.000,
                    'tolerance_min' => 1.000,
                    'tolerance_max' => 1.000,
                    'deviation' => $cp['is_pass'] ? 0.000 : -1.000,
                    'is_pass' => $cp['is_pass'],
                    'notes' => $cp['notes'],
                ];
            }
        } else {
            // Dimensional precision sample measurements
            $selectedParam = null;
            if (!empty($validated['parameter_id'])) {
                $selectedParam = PartInspectionParameter::with('instrument')->find($validated['parameter_id']);
            } elseif (!empty($validated['parameter_name'])) {
                $selectedParam = $part->parameters->firstWhere('parameter_name', $validated['parameter_name']);
            }
            if (!$selectedParam && $part->parameters->isNotEmpty()) {
                $selectedParam = $part->parameters->first();
            }

            $defaultNominal = $selectedParam ? $selectedParam->nominal_dimension : $part->nominal_dimension;
            $defaultMin = $selectedParam ? $selectedParam->tolerance_min : $part->tolerance_min;
            $defaultMax = $selectedParam ? $selectedParam->tolerance_max : $part->tolerance_max;
            $defaultTool = $validated['measurement_tool']
                ?? ($selectedParam && $selectedParam->instrument ? $selectedParam->instrument->name . ' (' . $selectedParam->instrument->instrument_code . ')' : 'Digital Caliper');
            $defaultParamName = $selectedParam ? $selectedParam->parameter_name : ($validated['parameter_name'] ?? 'Dimensi Kritis');

            foreach ($validated['measurements'] as $index => $actualVal) {
                $actual = floatval($actualVal);
                $sampleNum = intval($request->input("sample_numbers.$index", $index + 1));
                $rowParamName = $request->input("parameter_names.$index") ?? $defaultParamName;
                $rowToolName = $request->input("measurement_tools.$index") ?? $defaultTool;
                $rowNominal = floatval($request->input("nominal_values.$index", $defaultNominal));
                $rowMin = floatval($request->input("tolerance_mins.$index", $defaultMin));
                $rowMax = floatval($request->input("tolerance_maxs.$index", $defaultMax));

                $deviation = round($actual - $rowNominal, 3);
                $isPass = ($actual >= $rowMin && $actual <= $rowMax);

                if (!$isPass) {
                    $hasFailure = true;
                }

                $measurementsData[] = [
                    'sample_number' => $sampleNum,
                    'parameter_name' => $rowParamName,
                    'measurement_tool' => $rowToolName,
                    'actual_value' => $actual,
                    'nominal_value' => $rowNominal,
                    'tolerance_min' => $rowMin,
                    'tolerance_max' => $rowMax,
                    'deviation' => $deviation,
                    'is_pass' => $isPass,
                    'notes' => $isPass ? 'OK' : "Out of spec ({$rowMin} - {$rowMax})",
                ];
            }
        }

        $inspection = Inspection::create([
            'part_id' => $part->id,
            'inspector_id' => Auth::id(),
            'po_number' => $validated['po_number'],
            'lot_number' => $validated['lot_number'],
            'lot_quantity' => $validated['lot_quantity'],
            'sample_size' => $validated['sample_size'],
            'inspection_date' => $validated['inspection_date'],
            // If there are failures → save as 'draft' so inspector must upload photos before submitting to SPV
            // If all pass → auto-approve immediately (no photo mandatory, but still allowed)
            'status' => $hasFailure ? 'draft' : 'approved',
            'disposition' => $hasFailure ? null : 'accept',
            'disposition_notes' => $hasFailure ? null : ($isVisualOnly ? 'Auto-Accepted: All visual, packaging, marking, and lead integrity criteria comply with JEDEC/EIA manufacturing standards.' : 'Auto-Accepted: All sample dimensions meet drawing specification tolerances.'),
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
            ? '⚠️ Inspection saved with Out-of-Spec / Defect findings. Please upload photo evidence (defect & pass photos) then submit to Supervisor.'
            : ($isVisualOnly 
                ? 'Visual & Packaging Inspection PASSED. Component lot complies with industrial standards.'
                : 'Inspection PASSED. All dimensional sample measurements comply with blueprint tolerances.');

        return redirect()->route('inspections.show', $inspection->id)->with('success', $msg);
    }

    /**
     * Inspector submits inspection to SPV after uploading required photos.
     */
    public function submitToSpv(Request $request, $id)
    {
        $inspection = Inspection::with('photos')->findOrFail($id);

        // Poka-Yoke: only the owning inspector can submit
        if ($inspection->inspector_id !== Auth::id()) {
            abort(403, 'Only the assigned inspector can submit this inspection.');
        }

        if ($inspection->status !== 'draft') {
            return back()->withErrors(['submit' => 'This inspection has already been submitted or finalized.']);
        }

        // Enforce: at least 1 pass photo AND 1 defect photo required
        $passCount   = $inspection->photos->where('photo_type', 'pass')->count();
        $defectCount = $inspection->photos->where('photo_type', 'defect')->count();

        if ($passCount < 1) {
            return back()->withErrors(['photos' => 'At least 1 PASS/OK photo evidence is required before submitting to Supervisor.']);
        }
        if ($defectCount < 1) {
            return back()->withErrors(['photos' => 'At least 1 DEFECT photo evidence is required before submitting to Supervisor.']);
        }

        $inspection->update(['status' => 'pending_approval']);

        return redirect()->route('inspections.show', $inspection->id)
            ->with('success', '✅ Inspection submitted to Supervisor for disposition. ' . $passCount . ' pass + ' . $defectCount . ' defect photo(s) attached.');
    }


    public function show($id)
    {
        $inspection = Inspection::with(['part', 'inspector', 'supervisor', 'measurements', 'defects', 'photos.uploader'])->findOrFail($id);
        return view('inspections.show', compact('inspection'));
    }
}
