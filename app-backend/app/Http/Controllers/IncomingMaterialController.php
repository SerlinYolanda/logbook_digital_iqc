<?php

namespace App\Http\Controllers;

use App\Models\IncomingMaterial;
use App\Models\Part;
use Illuminate\Http\Request;

class IncomingMaterialController extends Controller
{
    /**
     * Backlog view: all physical incoming material shipments across all statuses,
     * with aging analysis, supplier filters, and operational receiving actions.
     * Distinct from static Master Data (which stores engineering drawings & tolerances).
     */
    public function index(Request $request)
    {
        $query = IncomingMaterial::with(['part', 'registrar', 'inspection.inspector', 'inspection.supervisor'])
            ->orderBy('arrived_at', 'desc');

        // Filter by status or aging
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('status', 'waiting')->where('arrived_at', '<=', now()->subDay());
            } elseif ($request->status === 'fresh') {
                $query->where('status', 'waiting')->where('arrived_at', '>', now()->subDay());
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by supplier
        if ($request->filled('supplier')) {
            $query->whereHas('part', fn($p) => $p->where('supplier_name', $request->supplier));
        }

        // Filter by search (GRN, part name, lot, part number)
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('grn_number', 'like', "%{$term}%")
                  ->orWhere('lot_number', 'like', "%{$term}%")
                  ->orWhereHas('part', fn($p) => $p->where('part_name', 'like', "%{$term}%")
                      ->orWhere('part_number', 'like', "%{$term}%")
                      ->orWhere('supplier_name', 'like', "%{$term}%"));
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('arrived_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('arrived_at', '<=', $request->date_to);
        }

        $materials = $query->paginate(15)->withQueryString();

        // Operational metrics
        $allCount        = IncomingMaterial::count();
        $waitingCount    = IncomingMaterial::where('status', 'waiting')->count();
        $inspectedCount  = IncomingMaterial::where('status', 'inspected')->count();
        $overdueCount    = IncomingMaterial::where('status', 'waiting')->where('arrived_at', '<=', now()->subDay())->count();
        $todayCount      = IncomingMaterial::whereDate('arrived_at', now()->today())->count();
        $totalQuantity   = (int) IncomingMaterial::sum('quantity');
        $waitingQuantity = (int) IncomingMaterial::where('status', 'waiting')->sum('quantity');

        $counts = [
            'all'         => $allCount,
            'waiting'     => $waitingCount,
            'inspected'   => $inspectedCount,
            'overdue'     => $overdueCount,
            'today'       => $todayCount,
            'total_qty'   => $totalQuantity,
            'waiting_qty' => $waitingQuantity,
        ];

        // Suppliers list for dropdown filter
        $suppliers = Part::whereNotNull('supplier_name')
            ->distinct()
            ->orderBy('supplier_name')
            ->pluck('supplier_name');

        // Parts for Register GRN modal
        $parts = Part::orderBy('part_number')->get();

        // Sequential next GRN suggestion
        $todayPrefix   = 'GRN-' . now()->format('Ymd');
        $todayGrnCount = IncomingMaterial::where('grn_number', 'like', "{$todayPrefix}-%")->count();
        $nextGrnNumber = sprintf('%s-%03d', $todayPrefix, $todayGrnCount + 1);

        // 7-day arrival trend for mini chart
        $trend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            return [
                'date'    => now()->subDays($daysAgo)->format('d/m'),
                'waiting' => IncomingMaterial::where('status', 'waiting')->whereDate('arrived_at', $date)->count(),
                'cleared' => IncomingMaterial::where('status', 'inspected')->whereDate('arrived_at', $date)->count(),
                'total'   => IncomingMaterial::whereDate('arrived_at', $date)->count(),
            ];
        });

        return view('incoming-materials.index', compact(
            'materials',
            'counts',
            'trend',
            'suppliers',
            'parts',
            'nextGrnNumber'
        ));
    }

    /**
     * Export the backlog list of arrived materials as a UTF-8 CSV spreadsheet.
     */
    public function exportCsv(Request $request)
    {
        $query = IncomingMaterial::with(['part', 'registrar', 'inspection'])
            ->orderBy('arrived_at', 'desc');

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('status', 'waiting')->where('arrived_at', '<=', now()->subDay());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('grn_number', 'like', "%{$term}%")
                  ->orWhere('lot_number', 'like', "%{$term}%")
                  ->orWhereHas('part', fn($p) => $p->where('part_name', 'like', "%{$term}%")
                      ->orWhere('part_number', 'like', "%{$term}%")
                      ->orWhere('supplier_name', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('supplier')) {
            $query->whereHas('part', fn($p) => $p->where('supplier_name', $request->supplier));
        }

        $items = $query->get();

        $filename = 'IQC_Dock_Backlog_Arrivals_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'GRN Barcode',
                'Arrival Date',
                'Arrival Time',
                'Part Number',
                'Component Name',
                'Supplier / Vendor',
                'Lot Batch Number',
                'Quantity (Pcs)',
                'Backlog Status',
                'Aging SLA',
                'Inspection Number',
                'Disposition Outcome',
                'Receiving Officer',
            ]);

            foreach ($items as $item) {
                $isWaiting = $item->status === 'waiting';
                $hoursAgo = $item->arrived_at ? (int) $item->arrived_at->diffInHours(now()) : 0;
                $sla = $isWaiting
                    ? ($hoursAgo > 24 ? "Overdue ({$hoursAgo}h)" : ($hoursAgo > 12 ? "Priority ({$hoursAgo}h)" : "Normal ({$hoursAgo}h)"))
                    : 'Cleared';

                fputcsv($handle, [
                    $item->grn_number,
                    $item->arrived_at ? $item->arrived_at->format('Y-m-d') : '-',
                    $item->arrived_at ? $item->arrived_at->format('H:i:s') : '-',
                    $item->part->part_number ?? '-',
                    $item->part->part_name ?? '-',
                    $item->part->supplier_name ?? '-',
                    $item->lot_number ?? '-',
                    $item->quantity,
                    ucfirst($item->status),
                    $sla,
                    $item->inspection_id ? 'INS-' . str_pad($item->inspection_id, 4, '0', STR_PAD_LEFT) : 'Not Inspected',
                    $item->inspection ? ucfirst(str_replace('_', ' ', $item->inspection->status)) : 'Pending Inspection',
                    $item->registrar->name ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Quick Scanner: look up a GRN number and redirect to the inspection form
     * with all fields pre-synced (part, lot, quantity).
     * If the GRN does not exist → redirect back with a "GRN not found" alert.
     */
    public function scanGrn(Request $request)
    {
        $code = strtoupper(trim($request->input('grn', '')));

        if (empty($code)) {
            return redirect()->back()->with('error', 'Please enter or scan a GRN number.');
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

            return redirect()->back()->with('grn_not_found', $code);
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
            return redirect()->back()
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

        return redirect()->back()
            ->with('success', "GRN {$validated['grn_number']} successfully registered to the IQC incoming backlog.");
    }

    public function destroy(IncomingMaterial $incomingMaterial)
    {
        if ($incomingMaterial->status !== 'waiting') {
            return back()->with('error', 'Only GRNs still waiting in the queue can be removed.');
        }

        $grn = $incomingMaterial->grn_number;
        $incomingMaterial->delete();

        return redirect()->back()->with('success', "GRN {$grn} removed from the backlog queue.");
    }
}
