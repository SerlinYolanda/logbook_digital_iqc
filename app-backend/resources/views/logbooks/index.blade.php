@extends('layouts.app')
@section('title', 'Inspection Logbook History - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">history_edu</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Inspection Logbook History & Status</h1>
                <p class="text-xs text-slate-500 mt-0.5">UC-07: Monitoring logbook lifecycle, quality sampling records, and revision loop handling.</p>
            </div>
        </div>

        @if(auth()->user()->isInspector())
        <a href="{{ route('logbooks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition">
            <span class="material-symbols-outlined text-[18px]">add</span>
            <span>New Inspection Logbook</span>
        </a>
        @endif
    </div>

    <!-- KPI Cards for Statuses -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-center">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-400">Total Lots</span>
            <div class="text-xl font-black text-slate-800 mt-1">{{ $totalLogbooks }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-500">Draft</span>
            <div class="text-xl font-black text-slate-700 mt-1">{{ $draftCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-amber-200 shadow-xs bg-amber-50/30">
            <span class="text-[10px] font-bold uppercase text-amber-700">Pending MRB</span>
            <div class="text-xl font-black text-amber-600 mt-1">{{ $waitingMrbCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-orange-200 shadow-xs bg-orange-50/30">
            <span class="text-[10px] font-bold uppercase text-orange-700">Needs Revision</span>
            <div class="text-xl font-black text-orange-600 mt-1 {{ $revisionCount > 0 ? 'animate-pulse' : '' }}">{{ $revisionCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-200 shadow-xs bg-emerald-50/30">
            <span class="text-[10px] font-bold uppercase text-emerald-700">Accepted</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $acceptedCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-rose-200 shadow-xs bg-rose-50/30">
            <span class="text-[10px] font-bold uppercase text-rose-700">RTV / Scrap</span>
            <div class="text-xl font-black text-rose-600 mt-1">{{ $rejectedCount }}</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search GRN, Lot, PO, Part..." 
                    class="w-full text-xs rounded-xl border-slate-200 px-3.5 py-2.5">
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 px-3.5 py-2.5">
                    <option value="">All Logbook Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="menunggu_penetapan" {{ request('status') === 'menunggu_penetapan' ? 'selected' : '' }}>Pending MRB Review</option>
                    <option value="perlu_revisi" {{ request('status') === 'perlu_revisi' ? 'selected' : '' }}>Needs Revision</option>
                    <option value="accept" {{ request('status') === 'accept' ? 'selected' : '' }}>Accepted / Passed</option>
                    <option value="return_to_vendor" {{ request('status') === 'return_to_vendor' ? 'selected' : '' }}>Return to Vendor (RTV)</option>
                    <option value="scrap" {{ request('status') === 'scrap' ? 'selected' : '' }}>Scrap</option>
                    <option value="rework" {{ request('status') === 'rework' ? 'selected' : '' }}>Rework</option>
                </select>
            </div>
            <div>
                <select name="komoditas" class="w-full text-xs rounded-xl border-slate-200 px-3.5 py-2.5">
                    <option value="">All Commodities</option>
                    <option value="ee_part" {{ request('komoditas') === 'ee_part' ? 'selected' : '' }}>EE Part</option>
                    <option value="automotive" {{ request('komoditas') === 'automotive' ? 'selected' : '' }}>Automotive</option>
                    <option value="wire_harness" {{ request('komoditas') === 'wire_harness' ? 'selected' : '' }}>Wire Harness</option>
                </select>
            </div>
            <div>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full text-xs rounded-xl border-slate-200 px-3.5 py-2.5">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex-1">
                    Filter
                </button>
                <a href="{{ route('logbooks.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table of Logbooks -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">GRN & Lot No</th>
                        <th class="py-3.5 px-4">Component & Commodity</th>
                        <th class="py-3.5 px-4">Supplier / Vendor</th>
                        <th class="py-3.5 px-4 text-center">Sample</th>
                        <th class="py-3.5 px-4">Inspector & Date</th>
                        <th class="py-3.5 px-4">Logbook Status</th>
                        <th class="py-3.5 px-4">MRB Disposition</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                        <th class="py-3.5 px-4">No GRN & Lot</th>
                        <th class="py-3.5 px-4">Komponen & Komoditas</th>
                        <th class="py-3.5 px-4">Supplier / Vendor</th>
                        <th class="py-3.5 px-4 text-center">Sampel</th>
                        <th class="py-3.5 px-4">Inspektur & Tgl</th>
                        <th class="py-3.5 px-4">Status Logbook</th>
                        <th class="py-3.5 px-4">Disposisi MRB</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logbooks as $log)
                    @php $rcv = $log->receiving; @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-indigo-600">{{ $rcv->no_grn_iqa ?? '-' }}</span>
                            <div class="text-[10px] text-slate-400 font-mono">Lot: {{ $rcv->no_lot ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $rcv->part->kode_part ?? '-' }}</div>
                            <span class="inline-block px-2 py-0.2 rounded text-[10px] font-bold 
                                @if($rcv->part->kategori_komoditas === 'ee_part') bg-emerald-50 text-emerald-700
                                @elseif($rcv->part->kategori_komoditas === 'automotive') bg-teal-50 text-teal-700
                                @else bg-purple-50 text-purple-700 @endif">
                                {{ $rcv->part->komoditas_label ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700">
                            {{ $rcv->supplier->nama_supplier ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
                            {{ $log->qty_sampel }} pcs
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            <div class="font-bold text-slate-800">{{ $log->inspector->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $log->tanggal_inspeksi ? $log->tanggal_inspeksi->format('d/m/Y H:i') : '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $log->status_badge_class }}">
                                {{ $log->status_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->latestMrbDisposition)
                                <div class="font-bold text-slate-800">{{ $log->latestMrbDisposition->decision_label }}</div>
                                <div class="text-[10px] text-slate-400 truncate max-w-[120px]" title="{{ $log->latestMrbDisposition->catatan }}">
                                    {{ $log->latestMrbDisposition->catatan }}
                                </div>
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('logbooks.show', $log->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                View
                            </a>

                            @if($log->status === 'perlu_revisi' && auth()->user()->isInspector())
                            <a href="{{ route('logbooks.edit', $log->id) }}" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-xs transition">
                                Revise
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">
                            No inspection logbook records found matching the filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logbooks->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $logbooks->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

