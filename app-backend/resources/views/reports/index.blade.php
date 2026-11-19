@extends('layouts.app')
@section('title', 'IQC Quality Recapitulation Report - IQC Digital Logbook')
@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs no-print">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">bar_chart</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">IQC Quality Recapitulation Report</h1>
                <p class="text-xs text-slate-500 mt-0.5">UC-12: Recapitulation of incoming material inspections, pass rates, and MRB disposition records.</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'xls'])) }}" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl transition flex items-center gap-1.5 shadow-md shadow-emerald-600/20 cursor-pointer" 
                title="Download neat Microsoft Excel spreadsheet (.xls)">
                <span class="material-symbols-outlined text-[16px]">table_chart</span>
                <span>Export Excel (.xls)</span>
            </a>
            <a href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}" 
                class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-2xl transition flex items-center gap-1 border border-slate-200 shadow-xs cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">download</span>
                <span>CSV</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-2xl transition flex items-center gap-1.5 shadow-md shadow-indigo-600/20 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>Print Sheet</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs no-print">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Start Date:</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs font-mono rounded-xl border-slate-200 py-2">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">End Date:</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs font-mono rounded-xl border-slate-200 py-2">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Decision Status:</label>
                <select name="status" class="w-full text-xs font-semibold rounded-xl border-slate-200 py-2">
                    <option value="">All Statuses</option>
                    <option value="accept" {{ $status === 'accept' ? 'selected' : '' }}>Accept / Passed</option>
                    <option value="return_to_vendor" {{ $status === 'return_to_vendor' ? 'selected' : '' }}>Return to Vendor (RTV)</option>
                    <option value="scrap" {{ $status === 'scrap' ? 'selected' : '' }}>Scrap</option>
                    <option value="rework" {{ $status === 'rework' ? 'selected' : '' }}>Rework</option>
                    <option value="menunggu_penetapan" {{ $status === 'menunggu_penetapan' ? 'selected' : '' }}>Pending MRB</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Commodity:</label>
                <select name="komoditas" class="w-full text-xs font-semibold rounded-xl border-slate-200 py-2">
                    <option value="">All Commodities</option>
                    <option value="ee_part" {{ $komoditas === 'ee_part' ? 'selected' : '' }}>EE Part</option>
                    <option value="automotive" {{ $komoditas === 'automotive' ? 'selected' : '' }}>Automotive</option>
                    <option value="wire_harness" {{ $komoditas === 'wire_harness' ? 'selected' : '' }}>Wire Harness</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Supplier / Vendor:</label>
                <select name="supplier_id" class="w-full text-xs font-semibold rounded-xl border-slate-200 py-2">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ $supplierId == $s->id ? 'selected' : '' }}>{{ $s->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex-1 transition">
                    Apply Filter
                </button>
                <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Official Report Document Paper (Printable) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">

        <!-- Printable Document ISO Header -->
        <div class="border-b-2 border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-xl">
                    OSI
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">PT OSI ELECTRONICS BATAM</h2>
                    <p class="text-xs text-slate-600 font-semibold">INCOMING QUALITY CONTROL (IQC) DIVISION</p>
                    <p class="text-[10px] text-slate-400">Batamindo Industrial Park, Mukakuning, Batam - Kepulauan Riau</p>
                </div>
            </div>

            <div class="text-right text-xs">
                <div class="font-bold text-slate-900 text-sm">QUALITY INSPECTION RECAPITULATION REPORT</div>
                <div class="text-slate-500 font-mono mt-0.5">Period: {{ date('d/m/Y', strtotime($startDate)) }} to {{ date('d/m/Y', strtotime($endDate)) }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Document No: IQC-REP-{{ date('Ymd') }} | ISO 9001:2015</div>
            </div>
        </div>

        <!-- Summary KPI Cards in Report -->
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-center">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-[10px] uppercase font-bold text-slate-500">Total Lots Inspected</span>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $totalLots }}</div>
            </div>
            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200">
                <span class="text-[10px] uppercase font-bold text-emerald-700">Accept / Passed</span>
                <div class="text-2xl font-black text-emerald-700 mt-0.5">{{ $totalAccepted }}</div>
            </div>
            <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200">
                <span class="text-[10px] uppercase font-bold text-rose-700">Return to Vendor</span>
                <div class="text-2xl font-black text-rose-700 mt-0.5">{{ $totalRtv }}</div>
            </div>
            <div class="p-3 rounded-2xl bg-red-50 border border-red-200">
                <span class="text-[10px] uppercase font-bold text-red-700">Scrap</span>
                <div class="text-2xl font-black text-red-700 mt-0.5">{{ $totalScrap }}</div>
            </div>
            <div class="p-3 rounded-2xl bg-purple-50 border border-purple-200">
                <span class="text-[10px] uppercase font-bold text-purple-700">Rework</span>
                <div class="text-2xl font-black text-purple-700 mt-0.5">{{ $totalRework }}</div>
            </div>
            <div class="p-3 rounded-2xl bg-indigo-50 border border-indigo-200">
                <span class="text-[10px] uppercase font-bold text-indigo-700">Pass Rate (%)</span>
                <div class="text-2xl font-black text-indigo-700 mt-0.5">{{ $passRate }}%</div>
            </div>
        </div>

        <!-- Defect Summary Breakdown -->
        @if($defectsSummary->count() > 0)
        <div class="space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Top Defect Findings Recapitulation (Pareto)</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase">
                        <tr>
                            <th class="py-2.5 px-3">Defect Code</th>
                            <th class="py-2.5 px-3">Defect Category Name</th>
                            <th class="py-2.5 px-3 text-center">Occurrences (Lots)</th>
                            <th class="py-2.5 px-3 text-right">Total Defect Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($defectsSummary as $def)
                        <tr>
                            <td class="py-2 px-3 font-mono text-indigo-600 font-bold">{{ $def->defectCategory->kode_defect ?? '-' }}</td>
                            <td class="py-2 px-3 font-bold text-slate-800">{{ $def->defectCategory->nama_defect ?? '-' }}</td>
                            <td class="py-2 px-3 text-center font-mono font-bold">{{ $def->occurrences }} lot</td>
                            <td class="py-2 px-3 text-right font-mono font-bold text-rose-600">{{ number_format($def->total_qty) }} pcs</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Detailed Lot Inspections Table -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Material Lot Inspection Detailed Results</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[11px]">
                        <tr>
                            <th class="py-2.5 px-3">No</th>
                            <th class="py-2.5 px-3">GRN & Lot No</th>
                            <th class="py-2.5 px-3">GR & Lot No</th>
                            <th class="py-2.5 px-3">Component / Part</th>
                            <th class="py-2.5 px-3">Commodity</th>
                            <th class="py-2.5 px-3">Supplier</th>
                            <th class="py-2.5 px-3 text-right">Inbound Qty</th>
                            <th class="py-2.5 px-3 text-center">Sample</th>
                            <th class="py-2.5 px-3">Inspector & Date</th>
                            <th class="py-2.5 px-3">Decision</th>
                            <th class="py-2.5 px-3">Supervisor Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logbooks as $idx => $log)
                        @php 
                            $rcv = $log->receiving; 
                            $disp = $log->latestMrbDisposition;
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2 px-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 font-mono">
                                <span class="font-bold text-indigo-600">{{ $rcv->no_grn_iqa ?? '-' }}</span>
                                <div class="text-[10px] text-slate-400 font-normal">Lot: {{ $rcv->no_lot ?? '-' }}</div>
                            </td>
                            <td class="py-2 px-3">
                                <div class="font-bold text-slate-800">{{ $rcv->part->kode_part ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $rcv->part->nama_part ?? '-' }}</div>
                            </td>
                            <td class="py-2 px-3 font-semibold text-slate-700">
                                {{ $rcv->part->komoditas_label ?? '-' }}
                            </td>
                            <td class="py-2 px-3 text-slate-600">
                                {{ $rcv->supplier->nama_supplier ?? '-' }}
                            </td>
                            <td class="py-2 px-3 text-right font-mono font-bold text-slate-800">
                                {{ number_format($rcv->qty_datang ?? 0) }}
                            </td>
                            <td class="py-2 px-3 text-center font-mono">
                                {{ $log->qty_sampel }}
                            </td>
                            <td class="py-2 px-3 text-slate-600 text-[11px]">
                                <span class="font-bold text-slate-800">{{ $log->inspector->name ?? '-' }}</span>
                                <div class="text-slate-400">{{ $log->tanggal_inspeksi ? $log->tanggal_inspeksi->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td class="py-2 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $log->status_badge_class }}">
                                    {{ $log->status_label }}
                                </span>
                            </td>
                            <td class="py-2 px-3 font-mono text-[10px] text-slate-600 max-w-[200px] truncate" title="{{ $disp->catatan ?? '-' }}">
                                {{ $disp->catatan ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-slate-400">No inspection data found for this date range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ISO 9001 Sign-off Approval Block -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-2 sm:grid-cols-3 gap-6 text-center text-xs">
            <div>
                <span class="text-slate-400 block mb-1">Prepared By:</span>
                <span class="font-bold text-slate-800 block">IQC Inspector</span>
                <div class="h-16 flex items-end justify-center font-mono text-[11px] text-slate-400">
                    ( Digital Signature )
                </div>
                <div class="border-t border-slate-300 pt-1 font-bold text-slate-700">
                    @php
                        $preparedInspector = 'Serlin Yolanda';
                        if (auth()->check() && str_starts_with(auth()->user()->role, 'inspector')) {
                            $preparedInspector = auth()->user()->name;
                        }
                    @endphp
                    {{ $preparedInspector }}
                </div>
            </div>

            <div>
                <span class="text-slate-400 block mb-1">Verified & Approved:</span>
                <span class="font-bold text-slate-800 block">IQC Quality Supervisor</span>
                <div class="h-16 flex items-end justify-center font-mono text-[11px] text-slate-400">
                    ( Digital Signature )
                </div>
                <div class="border-t border-slate-300 pt-1 font-bold text-slate-700">
                    Kislistiyono
                </div>
            </div>

            <div class="col-span-2 sm:col-span-1 text-left sm:text-right text-[11px] text-slate-400 flex flex-col justify-end">
                <div>Printed on: <b>{{ now()->format('d M Y H:i') }} WIB</b></div>
                <div>Document Status: <b>ISO 9001:2015 Controlled Record</b></div>
                <div>PT OSI Electronics Batam</div>
            </div>
        </div>

    </div>

</div>
@endsection
