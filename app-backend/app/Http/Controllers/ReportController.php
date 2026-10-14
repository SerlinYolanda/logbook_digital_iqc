<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Part;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with(['part', 'inspector', 'supervisor']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('disposition') && $request->disposition !== 'all') {
            $query->where('disposition', $request->disposition);
        }

        if ($request->filled('part_id') && $request->part_id !== 'all') {
            $query->where('part_id', $request->part_id);
        }

        if ($request->filled('period')) {
            if ($request->period === 'today') {
                $query->whereDate('inspection_date', now()->toDateString());
            } elseif ($request->period === 'this_week') {
                $query->whereBetween('inspection_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
            } elseif ($request->period === 'this_month') {
                $query->whereMonth('inspection_date', now()->month)
                      ->whereYear('inspection_date', now()->year);
            }
        }

        $inspections = $query->latest()->get();
        $parts = Part::all();

        return view('reports.index', compact('inspections', 'parts'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Inspection::with(['part', 'inspector', 'supervisor']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('disposition') && $request->disposition !== 'all') {
            $query->where('disposition', $request->disposition);
        }

        $inspections = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="IQC_Inspection_Summary_Report_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($inspections) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'No',
                'PO / GRN Number',
                'Lot Number',
                'Part Code',
                'Component Name',
                'Supplier / Vendor',
                'Lot Qty',
                'Sample Size',
                'Inspection Date',
                'Inspector',
                'Supervisor',
                'Final Status',
                'Disposition',
                'Evaluation Notes'
            ]);

            foreach ($inspections as $index => $ins) {
                fputcsv($file, [
                    $index + 1,
                    $ins->po_number,
                    $ins->lot_number,
                    $ins->part->part_number ?? '-',
                    $ins->part->part_name ?? '-',
                    $ins->part->supplier_name ?? '-',
                    $ins->lot_quantity,
                    $ins->sample_size,
                    $ins->inspection_date,
                    $ins->inspector->name ?? '-',
                    $ins->supervisor->name ?? '-',
                    strtoupper($ins->status),
                    strtoupper(str_replace('_', ' ', $ins->disposition ?? '-')),
                    $ins->disposition_notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
