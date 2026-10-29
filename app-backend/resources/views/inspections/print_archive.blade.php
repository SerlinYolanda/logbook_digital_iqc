<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IQC Logbook Archive Report — PT Elektronika Indonesia</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; color: #0f172a; background: #f8fafc; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            body { background: #ffffff !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .page-break-inside-avoid { page-break-inside: avoid; }
            @page {
                size: A4 landscape;
                margin: 10mm 8mm;
            }
            table { width: 100% !important; border-collapse: collapse !important; font-size: 10px !important; }
            th, td { padding: 4px 6px !important; border-color: #cbd5e1 !important; }
        }
    </style>
</head>
<body class="p-6 md:p-8">

    <!-- Top Non-Printable Action Bar -->
    <div class="no-print max-w-7xl mx-auto mb-6 bg-white border border-slate-200 rounded-2xl p-4 shadow-md flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="p-2.5 rounded-xl bg-indigo-50 text-indigo-700">
                <span class="material-symbols-outlined text-[24px]">print</span>
            </span>
            <div>
                <h2 class="text-sm font-bold text-slate-900">Official IQC Logbook Archive Print Preview</h2>
                <p class="text-xs text-slate-500">
                    Showing all <strong>{{ $summary['totalLots'] }}</strong> matching lot records in audit-compliant A4 Landscape format.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('inspections.index', request()->query()) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-300 transition flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Back to History</span>
            </a>
            <a href="{{ route('inspections.export', request()->query()) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/30 transition flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">download</span>
                <span>Export CSV (Excel)</span>
            </a>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable Official Report Container -->
    <div class="max-w-7xl mx-auto bg-white border border-slate-300 rounded-2xl p-6 sm:p-8 shadow-sm">
        
        <!-- Corporate Letterhead Header -->
        <div class="border-b-2 border-slate-900 pb-4 mb-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xl tracking-tight shadow">
                        EI
                    </div>
                    <div>
                        <h1 class="text-base font-extrabold text-slate-900 tracking-tight uppercase">PT Elektronika Indonesia</h1>
                        <p class="text-xs font-semibold text-slate-600 tracking-wider uppercase">Quality Assurance &amp; Incoming Quality Control (IQC) Department</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Industrial Zone Block C-14, Cikarang Barat, Bekasi — ISO 9001:2015 &amp; IATF 16949 Certified Facility</p>
                    </div>
                </div>

                <!-- Document Control Box -->
                <div class="border border-slate-300 rounded-xl p-2.5 text-[10px] font-mono bg-slate-50 text-slate-700 shrink-0 self-start sm:self-auto space-y-0.5">
                    <div><span class="font-bold text-slate-900">DOC NO:</span> FR-IQC-LOG-004</div>
                    <div><span class="font-bold text-slate-900">REVISION:</span> 04 (Electronic Component Standard)</div>
                    <div><span class="font-bold text-slate-900">SAMPLING:</span> MIL-STD-105E / ANSI ASQ Z1.4</div>
                    <div><span class="font-bold text-slate-900">DATE PRINTED:</span> {{ now()->translatedFormat('d M Y, H:i') }} WIB</div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 text-center">
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    Official Incoming Inspection Logbook &amp; Lot Archive Register
                </h2>
                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center justify-center gap-3 flex-wrap">
                    <span><strong>Active Filter Status:</strong> {{ strtoupper(request('status', 'ALL RECORDS')) }}</span>
                    <span>•</span>
                    <span><strong>Printed By:</strong> {{ auth()->user()->name ?? 'Inspector' }} (NIK: {{ auth()->user()->nik ?? '-' }})</span>
                    <span>•</span>
                    <span><strong>Total Filtered Lots:</strong> {{ $summary['totalLots'] }} Lots</span>
                </div>
            </div>
        </div>

        <!-- Summary KPI Metrics Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5 text-xs">
            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Lots Tested</span>
                <span class="text-lg font-extrabold text-slate-900 font-mono">{{ $summary['totalLots'] }} Lots</span>
            </div>
            <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/60">
                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Passed / Quality In-Spec</span>
                <span class="text-lg font-extrabold text-emerald-800 font-mono">{{ $summary['passedLots'] }} Lots</span>
            </div>
            <div class="p-3 rounded-xl border border-rose-200 bg-rose-50/60">
                <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Defect / Out of Spec</span>
                <span class="text-lg font-extrabold text-rose-800 font-mono">{{ $summary['rejectedLots'] }} Lots</span>
            </div>
            <div class="p-3 rounded-xl border border-indigo-200 bg-indigo-50/60">
                <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider block">Total Sampling Quantity</span>
                <span class="text-lg font-extrabold text-indigo-900 font-mono">{{ number_format($summary['totalSamples']) }} Pcs</span>
            </div>
        </div>

        <!-- Official History Logbook Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-300">
                <thead class="bg-slate-100 text-slate-800 uppercase text-[10px] tracking-wider border-b border-slate-300 font-extrabold">
                    <tr>
                        <th class="py-2.5 px-2 border-r border-slate-300 text-center w-10">No</th>
                        <th class="py-2.5 px-2.5 border-r border-slate-300 w-24">Date</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">PO / GRN No</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">Vendor Lot No</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">Component Part &amp; Spec</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">Supplier / Vendor</th>
                        <th class="py-2.5 px-2.5 border-r border-slate-300 text-center">Sample / Qty</th>
                        <th class="py-2.5 px-2.5 border-r border-slate-300 text-center">Method</th>
                        <th class="py-2.5 px-2.5 border-r border-slate-300 text-center">Status</th>
                        <th class="py-2.5 px-2.5 border-r border-slate-300 text-center">Disposition</th>
                        <th class="py-2.5 px-3 text-center">Authorization</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-[11px]">
                    @forelse($inspections as $index => $ins)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/70' }} hover:bg-slate-100/60 transition page-break-inside-avoid">
                            <td class="py-2 px-2 border-r border-slate-200 text-center font-mono font-bold text-slate-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-2 px-2.5 border-r border-slate-200 font-mono text-slate-700 whitespace-nowrap">
                                {{ $ins->inspection_date ? $ins->inspection_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2 px-3 border-r border-slate-200 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $ins->po_number }}
                            </td>
                            <td class="py-2 px-3 border-r border-slate-200 font-mono text-slate-700 whitespace-nowrap">
                                {{ $ins->lot_number }}
                            </td>
                            <td class="py-2 px-3 border-r border-slate-200">
                                <div class="font-bold text-slate-900">{{ $ins->part->part_name ?? '-' }}</div>
                                <div class="text-[10px] text-indigo-700 font-mono">{{ $ins->part->part_number ?? '-' }}</div>
                            </td>
                            <td class="py-2 px-3 border-r border-slate-200 text-slate-700">
                                {{ $ins->part->supplier_name ?? '-' }}
                            </td>
                            <td class="py-2 px-2.5 border-r border-slate-200 text-center font-mono whitespace-nowrap">
                                <span class="font-bold text-slate-900">{{ $ins->sample_size }}</span> / {{ number_format($ins->lot_quantity) }}
                            </td>
                            <td class="py-2 px-2.5 border-r border-slate-200 text-center">
                                @if($ins->inspection_type === 'visual')
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Visual / MSL
                                    </span>
                                @else
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        Dimensional
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-2.5 border-r border-slate-200 text-center whitespace-nowrap">
                                @if($ins->status === 'approved')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        APPROVED
                                    </span>
                                @elseif($ins->status === 'rejected')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                        REJECTED
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-2.5 border-r border-slate-200 text-center whitespace-nowrap font-bold text-[10px]">
                                @if($ins->disposition === 'accept')
                                    <span class="text-emerald-700">ACCEPT</span>
                                @elseif($ins->disposition === 'return_to_vendor')
                                    <span class="text-rose-700">RTV</span>
                                @elseif($ins->disposition === 'scrap')
                                    <span class="text-purple-700">SCRAP</span>
                                @elseif($ins->disposition === 'mrb')
                                    <span class="text-amber-700">MRB</span>
                                @elseif($ins->disposition === 'rework')
                                    <span class="text-blue-700">REWORK</span>
                                @else
                                    <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-center text-[10px] whitespace-nowrap">
                                <div>Ins: <strong>{{ $ins->inspector->name ?? 'Inspector' }}</strong></div>
                                @if($ins->supervisor)
                                    <div class="text-amber-800 font-semibold">Spv: {{ $ins->supervisor->name }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-8 text-slate-400">
                                No inspection records found matching the applied filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Corporate Authorization & Sign-Off Section -->
        <div class="mt-8 pt-6 border-t-2 border-slate-800 grid grid-cols-3 gap-6 text-center text-xs page-break-inside-avoid">
            <!-- Inspector Block -->
            <div class="border border-slate-300 rounded-xl p-4 bg-slate-50">
                <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block mb-2">
                    Tested &amp; Logged By (IQC Inspector)
                </span>
                <div class="h-16 flex items-center justify-center">
                    <div class="border border-indigo-300 bg-indigo-50/70 rounded-lg px-3 py-1 text-indigo-700 text-[10px] font-bold">
                        DIGITALLY VERIFIED
                    </div>
                </div>
                <div class="mt-2 border-t border-slate-200 pt-1.5">
                    <div class="font-extrabold text-slate-900">{{ auth()->user()->name ?? 'Inspector' }}</div>
                    <div class="text-[10px] text-slate-500">IQC Quality Inspector (NIK: {{ auth()->user()->nik ?? '-' }})</div>
                </div>
            </div>

            <!-- Supervisor Block -->
            <div class="border border-slate-300 rounded-xl p-4 bg-slate-50">
                <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block mb-2">
                    Reviewed &amp; Authorized By (Quality SPV)
                </span>
                <div class="h-16 flex items-center justify-center">
                    <div class="border border-emerald-300 bg-emerald-50/70 rounded-lg px-3 py-1 text-emerald-800 text-[10px] font-bold">
                        SPV QUALITY AUTHORIZED
                    </div>
                </div>
                <div class="mt-2 border-t border-slate-200 pt-1.5">
                    <div class="font-extrabold text-slate-900">{{ $supervisor->name ?? 'Kislistiyono' }}</div>
                    <div class="text-[10px] text-slate-500">IQC Section Supervisor (NIK: {{ $supervisor->nik ?? 'SPV-001' }})</div>
                </div>
            </div>

            <!-- QA Manager Block -->
            <div class="border border-slate-300 rounded-xl p-4 bg-slate-50">
                <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block mb-2">
                    Acknowledged By (QA Section Head)
                </span>
                <div class="h-16 flex items-center justify-center">
                    <div class="border border-slate-300 bg-white rounded-lg px-3 py-1 text-slate-500 text-[10px] font-bold">
                        QA COMPLIANCE AUDITED
                    </div>
                </div>
                <div class="mt-2 border-t border-slate-200 pt-1.5">
                    <div class="font-extrabold text-slate-900">Ir. H. Budi Santoso, M.T.</div>
                    <div class="text-[10px] text-slate-500">Quality Assurance Department Head</div>
                </div>
            </div>
        </div>

        <!-- Footer Audit Note -->
        <div class="mt-6 pt-3 border-t border-slate-200 text-center text-[9px] text-slate-400 font-mono">
            This document is an official digital record generated from the Logbook Digital IQC Portal PT Elektronika Indonesia. Valid for internal quality control, ISO 9001:2015 / IATF 16949 audit compliance.
        </div>
    </div>

</body>
</html>
