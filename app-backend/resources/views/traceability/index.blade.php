@extends('layouts.app')
@section('title', 'Material Quality Traceability - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">search_check</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Material Quality History & Traceability</h1>
                <p class="text-xs text-slate-500 mt-0.5">Trace material quality history from dock inbound to production release.</p>
            </div>
        </div>
    </div>

    <!-- Multi-criteria Search Form -->
    <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center gap-2 text-blue-600 text-xs font-bold uppercase tracking-wider">
            <span class="material-symbols-outlined text-[16px]">filter_alt</span>
            <span>Quality History Traceability Filter</span>
        </div>

        <form method="GET" action="{{ route('traceability.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-slate-600 font-bold text-xs mb-1">General Search (Lot / GRN / PO / Part No):</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="e.g. LOT-STM-2610A or GRN-202611-001..." 
                    class="w-full text-xs font-mono rounded-xl border-slate-200 py-2.5">
            </div>

            <div>
                <label class="block text-slate-600 font-bold text-xs mb-1">Commodity:</label>
                <select name="komoditas" class="w-full text-xs rounded-xl border-slate-200 py-2.5">
                    <option value="">All Commodities</option>
                    <option value="ee_part" {{ request('komoditas') === 'ee_part' ? 'selected' : '' }}>EE Part</option>
                    <option value="automotive" {{ request('komoditas') === 'automotive' ? 'selected' : '' }}>Automotive</option>
                    <option value="wire_harness" {{ request('komoditas') === 'wire_harness' ? 'selected' : '' }}>Wire Harness</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-600 font-bold text-xs mb-1">Quality Status:</label>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 py-2.5">
                    <option value="">All Statuses</option>
                    <option value="accept" {{ request('status') === 'accept' ? 'selected' : '' }}>Accept / Passed</option>
                    <option value="return_to_vendor" {{ request('status') === 'return_to_vendor' ? 'selected' : '' }}>Return to Vendor (RTV)</option>
                    <option value="scrap" {{ request('status') === 'scrap' ? 'selected' : '' }}>Scrap</option>
                    <option value="rework" {{ request('status') === 'rework' ? 'selected' : '' }}>Rework</option>
                    <option value="menunggu_penetapan" {{ request('status') === 'menunggu_penetapan' ? 'selected' : '' }}>Pending MRB</option>
                    <option value="menunggu_inspeksi" {{ request('status') === 'menunggu_inspeksi' ? 'selected' : '' }}>Waiting for Inspection</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-600 font-bold text-xs mb-1">Supplier / Vendor:</label>
                <select name="supplier_id" class="w-full text-xs rounded-xl border-slate-200 py-2.5">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-600 font-bold text-xs mb-1">Country of Origin:</label>
                <select name="negara_asal" class="w-full text-xs rounded-xl border-slate-200 py-2.5">
                    <option value="">All Countries</option>
                    @foreach($countries as $cnt)
                        <option value="{{ $cnt }}" {{ request('negara_asal') === $cnt ? 'selected' : '' }}>{{ $cnt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-600 font-bold text-xs mb-1">Date From:</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full text-xs rounded-xl border-slate-200 py-2.5">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex-1 transition">
                    Search History
                </button>
                <a href="{{ route('traceability.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Traceability Results Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Lot & GRN No</th>
                        <th class="py-3.5 px-4">Component & Commodity</th>
                        <th class="py-3.5 px-4">Supplier & Country</th>
                        <th class="py-3.5 px-4 text-right">Inbound Qty</th>
                        <th class="py-3.5 px-4">Status & Tgl Proses</th>
                        <th class="py-3.5 px-4">MRB Disposition</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($results as $item)
                    @php 
                        $log = $item->latest_logbook;
                        $disp = $log ? $log->latestMrbDisposition : null;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-blue-600 text-sm">Lot: {{ $item->no_lot }}</span>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $item->no_grn_iqa }} | PO: {{ $item->no_po }}</div>
                            <div class="text-[10px] text-slate-500 font-sans mt-0.5">
                                <span class="text-slate-400">Masuk:</span> <b class="text-slate-700 font-mono">{{ $item->tanggal_terima ? $item->tanggal_terima->format('d/m/Y') : '-' }}</b>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $item->part->kode_part ?? '-' }}</div>
                            <div class="text-[10px] text-slate-500">{{ $item->part->nama_part ?? '-' }}</div>
                            <span class="inline-block mt-0.5 px-2 py-0.2 rounded text-[10px] font-bold 
                                @if($item->part->kategori_komoditas === 'ee_part') bg-emerald-50 text-emerald-700
                                @elseif($item->part->kategori_komoditas === 'automotive') bg-teal-50 text-teal-700
                                @else bg-purple-50 text-purple-700 @endif">
                                {{ $item->part->komoditas_label ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $item->supplier->nama_supplier ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->supplier->negara_asal ?? 'Indonesia' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800">
                            {{ number_format($item->qty_datang) }} pcs
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $log->status_badge_class }}">
                                    {{ $log->status_label }}
                                </span>
                                @if($log->tanggal_inspeksi)
                                    <div class="text-[10px] text-slate-400 font-mono mt-1">Selesai: {{ $log->tanggal_inspeksi->format('d/m/Y') }}</div>
                                @endif
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                    GRN Inbound Queue
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($disp)
                                <span class="font-bold text-slate-800">{{ $disp->decision_label }}</span>
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('traceability.show', $item->id) }}" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-blue-600 hover:text-white font-bold text-xs transition">
                                <span class="material-symbols-outlined text-[14px]">timeline</span>
                                <span>Track Journey</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            No materials matched the search criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($results->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $results->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

