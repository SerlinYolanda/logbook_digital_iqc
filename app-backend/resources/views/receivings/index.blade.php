@extends('layouts.app')
@section('title', 'Inbound Material & GRN Queue - IQC Digital Logbook')
@section('title', 'Inbound Material & GR Queue - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">conveyor_belt</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Inbound Material (GRN) & Queue Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">UC-02: Inbound material registration, IQA GRN assignment, and queue aging benchmark monitoring.</p>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Inbound Material (GR) & Queue Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">UC-02: Inbound material registration, IQA GR assignment, and queue aging benchmark monitoring.</p>
            </div>
        </div>

        @if(auth()->user()->isMasterData() || auth()->user()->isAdmin())
        <button type="button" onclick="document.getElementById('registerGrnModal').classList.remove('hidden')" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>Register Inbound Material</span>
        </button>
        @endif
    </div>

    <!-- Queue Aging KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Inbound</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalArrivals }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Waiting in Queue</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $waitingCount }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">In Inspection</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $inProgressCount }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">QC Completed</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $completedCount }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-xs bg-rose-50/30">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Overdue (&gt;24 Hours)</span>
            <div class="text-2xl font-black text-rose-600 mt-1 {{ $overdueCount > 0 ? 'animate-pulse' : '' }}">{{ $overdueCount }}</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('receivings.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search GRN, PO, Lot, Part..." 
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search GR, PO, Lot, Part..." 
                    class="w-full text-xs rounded-xl border-slate-200 px-3.5 py-2.5">
            </div>
            <div>
                <select name="status_antrean" class="w-full text-xs rounded-xl border-slate-200 px-3.5 py-2.5">
                    <option value="">All Queue Statuses</option>
                    <option value="menunggu_inspeksi" {{ request('status_antrean') === 'menunggu_inspeksi' ? 'selected' : '' }}>Waiting for Inspection</option>
                    <option value="dalam_inspeksi" {{ request('status_antrean') === 'dalam_inspeksi' ? 'selected' : '' }}>In Inspection</option>
                    <option value="selesai" {{ request('status_antrean') === 'selesai' ? 'selected' : '' }}>Completed</option>
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
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex-1">
                    Filter
                </button>
                <a href="{{ route('receivings.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table of Inbound Receivings -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">IQA GRN No</th>
                        <th class="py-3.5 px-4">IQA GR No</th>
                        <th class="py-3.5 px-4">Received Date & Aging</th>
                        <th class="py-3.5 px-4">Supplier & Country</th>
                        <th class="py-3.5 px-4">Component & Commodity</th>
                        <th class="py-3.5 px-4">PO / Lot Number</th>
                        <th class="py-3.5 px-4 text-right">Inbound Qty</th>
                        <th class="py-3.5 px-4">Queue Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receivings as $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                            {{ $item->no_grn_iqa }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-slate-700">{{ $item->tanggal_terima ? $item->tanggal_terima->format('d/m/Y H:i') : '-' }}</div>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold border {{ $item->aging_badge_class }}">
                                {{ $item->aging_status }} ({{ $item->aging_hours }} hrs)
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $item->supplier->nama_supplier ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->supplier->negara_asal ?? 'Indonesia' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">{{ $item->part->kode_part ?? '-' }}</div>
                            <span class="inline-block px-2 py-0.2 rounded-full text-[10px] font-bold 
                                @if($item->part->kategori_komoditas === 'ee_part') bg-emerald-50 text-emerald-700
                                @elseif($item->part->kategori_komoditas === 'automotive') bg-teal-50 text-teal-700
                                @else bg-purple-50 text-purple-700 @endif">
                                {{ $item->part->komoditas_label ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-600">
                            <div>{{ $item->no_po }}</div>
                            <div class="text-[10px] text-slate-400 font-bold">Lot: {{ $item->no_lot }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800">
                            {{ number_format($item->qty_datang) }} pcs
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold 
                                @if($item->status_antrean === 'selesai') bg-emerald-100 text-emerald-800
                                @elseif($item->status_antrean === 'dalam_inspeksi') bg-amber-100 text-amber-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ $item->queue_status_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1">
                            @if(auth()->user()->isInspector() && $item->status_antrean !== 'selesai')
                            <a href="{{ route('logbooks.create', ['receiving_id' => $item->id]) }}" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] shadow-xs transition">
                                <span class="material-symbols-outlined text-[14px]">fact_check</span>
                                <span>Inspect</span>
                            </a>
                            @endif

                            @if((auth()->user()->isMasterData() || auth()->user()->isAdmin()) && $item->status_antrean === 'menunggu_inspeksi')
                            <form action="{{ route('receivings.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this inbound material receiving entry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">
                            No inbound material queue data found matching the selected filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receivings->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $receivings->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Modal Inbound Material Registration (GRN) -->
<!-- Modal Inbound Material Registration (GR) -->
<div id="registerGrnModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">post_add</span>
                Inbound Material Registration (GRN)
                Inbound Material Registration (GR)
            </h3>
            <button type="button" onclick="document.getElementById('registerGrnModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('receivings.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">IQA GRN Number *</label>
                <input type="text" name="no_grn_iqa" required value="GRN-{{ date('Ym') }}-{{ str_pad(rand(10, 999), 3, '0', STR_PAD_LEFT) }}" 
                <label class="block text-xs font-bold text-slate-700 mb-1">IQA GR Number *</label>
                <input type="text" name="no_grn_iqa" required value="GR-{{ date('Ym') }}-{{ str_pad(rand(10, 999), 3, '0', STR_PAD_LEFT) }}" 
                    class="w-full text-xs font-mono rounded-xl border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Received Date & Time *</label>
                    <input type="datetime-local" name="tanggal_terima" required value="{{ date('Y-m-d\TH:i') }}"
                        class="w-full text-xs rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Inbound Quantity (pcs) *</label>
                    <input type="number" name="qty_datang" required min="1" placeholder="e.g. 5000"
                        class="w-full text-xs rounded-xl border-slate-200 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Supplier / Vendor *</label>
                <select name="supplier_id" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">Select Supplier...</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->nama_supplier }} ({{ $sup->negara_asal }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Part / Component *</label>
                <select name="part_id" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">Select Component...</option>
                    @foreach($parts as $pt)
                        <option value="{{ $pt->id }}">{{ $pt->kode_part }} - {{ $pt->nama_part }} ({{ $pt->komoditas_label }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">PO Number *</label>
                    <input type="text" name="no_po" required placeholder="PO-2026-..." class="w-full text-xs rounded-xl border-slate-200 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lot Number *</label>
                    <input type="text" name="no_lot" required placeholder="LOT-..." class="w-full text-xs rounded-xl border-slate-200 font-mono">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('registerGrnModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20">
                    Register into Queue
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

