@extends('layouts.app')
@section('title', 'Inbound Material & GRN Queue - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-5 pb-12">

    <!-- Executive Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Inbound Material (GRN) & Queue</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penerimaan dock, antrean inspeksi IQA, dan monitoring aging FIFO.</p>
        </div>

        @if(auth()->user()->isMasterData() || auth()->user()->isAdmin())
        <button type="button" onclick="document.getElementById('registerGrnModal').classList.remove('hidden')" 
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
            <span class="material-symbols-outlined text-[16px]">add_circle</span>
            <span>Register Inbound GRN</span>
        </button>
        @endif
    </div>

    <!-- Queue Aging KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Inbound</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">{{ $totalArrivals }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Waiting in Queue</span>
            <div class="text-2xl font-bold text-blue-600 mt-1 font-mono">{{ $waitingCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">In Inspection</span>
            <div class="text-2xl font-bold text-amber-600 mt-1 font-mono">{{ $inProgressCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">QC Completed</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1 font-mono">{{ $completedCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Overdue (&gt;24 Hours)</span>
            <div class="text-2xl font-bold text-rose-600 mt-1 font-mono">{{ $overdueCount }}</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl p-3.5 border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('receivings.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari GRN, PO, Lot, Part..." 
                    class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 bg-slate-50 focus:bg-white">
            </div>
            <div>
                <select name="status_antrean" class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 bg-slate-50 focus:bg-white">
                    <option value="">Semua Status Antrean</option>
                    <option value="menunggu_inspeksi" {{ request('status_antrean') === 'menunggu_inspeksi' ? 'selected' : '' }}>Menunggu Inspeksi</option>
                    <option value="dalam_inspeksi" {{ request('status_antrean') === 'dalam_inspeksi' ? 'selected' : '' }}>Dalam Inspeksi</option>
                    <option value="selesai" {{ request('status_antrean') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div>
                <select name="komoditas" class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 bg-slate-50 focus:bg-white">
                    <option value="">Semua Komoditas</option>
                    <option value="ee_part" {{ request('komoditas') === 'ee_part' ? 'selected' : '' }}>EE Part</option>
                    <option value="automotive" {{ request('komoditas') === 'automotive' ? 'selected' : '' }}>Automotive</option>
                    <option value="wire_harness" {{ request('komoditas') === 'wire_harness' ? 'selected' : '' }}>Wire Harness</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex-1 transition">
                    Filter
                </button>
                <a href="{{ route('receivings.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table of Inbound Receivings -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">IQA GRN No</th>
                        <th class="py-3 px-4">Tgl Masuk & Aging</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Komponen & Komoditas</th>
                        <th class="py-3 px-4">PO / Lot Number</th>
                        <th class="py-3 px-4 text-right">Qty Masuk</th>
                        <th class="py-3 px-4">Status Antrean</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receivings as $item)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-semibold text-blue-600">
                            {{ $item->no_grn_iqa }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-slate-800 text-[11px]">{{ $item->tanggal_terima ? $item->tanggal_terima->format('d/m/Y H:i') : '-' }}</div>
                            <span class="inline-block mt-0.5 text-[10px] font-medium {{ $item->aging_hours > 24 ? 'text-rose-600 font-bold' : ($item->aging_hours > 12 ? 'text-amber-600' : 'text-slate-400') }}">
                                {{ $item->aging_status }} ({{ $item->aging_hours }}h)
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-slate-800 text-[11px]">{{ $item->supplier->nama_supplier ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->supplier->negara_asal ?? 'Indonesia' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800">{{ $item->part->kode_part ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-[130px]">{{ $item->part->nama_part ?? '-' }}</div>
                            <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-medium mt-0.5
                                @if($item->part->kategori_komoditas === 'ee_part') bg-emerald-50 text-emerald-700
                                @elseif($item->part->kategori_komoditas === 'automotive') bg-blue-50 text-blue-700
                                @else bg-purple-50 text-purple-700 @endif">
                                {{ $item->part->komoditas_label ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600">
                            <div>{{ $item->no_po }}</div>
                            <div class="text-[10px] text-slate-400">Lot: {{ $item->no_lot }}</div>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800">
                            {{ number_format($item->qty_datang) }} pcs
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium 
                                @if($item->status_antrean === 'selesai') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($item->status_antrean === 'dalam_inspeksi') bg-blue-50 text-blue-700 border border-blue-200
                                @else bg-amber-50 text-amber-700 border border-amber-200 @endif">
                                <span class="w-1.5 h-1.5 rounded-full 
                                    @if($item->status_antrean === 'selesai') bg-emerald-500
                                    @elseif($item->status_antrean === 'dalam_inspeksi') bg-blue-500
                                    @else bg-amber-500 @endif"></span>
                                {{ $item->queue_status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1">
                            @if(auth()->user()->isInspector() && $item->status_antrean !== 'selesai')
                            <a href="{{ route('logbooks.create', ['receiving_id' => $item->id]) }}" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium text-[11px] shadow-xs transition">
                                <span class="material-symbols-outlined text-[14px]">fact_check</span>
                                <span>Inspeksi</span>
                            </a>
                            @endif

                            @if((auth()->user()->isMasterData() || auth()->user()->isAdmin()) && $item->status_antrean === 'menunggu_inspeksi')
                            <form action="{{ route('receivings.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data antrean inbound ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                            Tidak ada data antrean inbound material yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receivings->hasPages())
        <div class="p-3 border-t border-slate-100">
            {{ $receivings->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Modal Inbound Material Registration (GRN) -->
<div id="registerGrnModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-lg w-full p-5 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600 text-[18px]">post_add</span>
                Registrasi Inbound Material (GRN)
            </h3>
            <button type="button" onclick="document.getElementById('registerGrnModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form action="{{ route('receivings.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor IQA GRN *</label>
                <input type="text" name="no_grn_iqa" required value="GRN-{{ date('Ym') }}-{{ str_pad(rand(10, 999), 3, '0', STR_PAD_LEFT) }}" 
                    class="w-full text-xs font-mono rounded-lg border-slate-200 px-3 py-2">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal & Waktu Masuk *</label>
                    <input type="datetime-local" name="tanggal_terima" required value="{{ date('Y-m-d\TH:i') }}"
                        class="w-full text-xs rounded-lg border-slate-200 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Qty Inbound (pcs) *</label>
                    <input type="number" name="qty_datang" required min="1" placeholder="Contoh: 5000"
                        class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier / Vendor *</label>
                <select name="supplier_id" required class="w-full text-xs rounded-lg border-slate-200 px-3 py-2">
                    <option value="">Pilih Supplier...</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->nama_supplier }} ({{ $sup->negara_asal }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Part / Komponen *</label>
                <select name="part_id" required class="w-full text-xs rounded-lg border-slate-200 px-3 py-2">
                    <option value="">Pilih Komponen...</option>
                    @foreach($parts as $pt)
                        <option value="{{ $pt->id }}">{{ $pt->kode_part }} - {{ $pt->nama_part }} ({{ $pt->komoditas_label }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor PO *</label>
                    <input type="text" name="no_po" required placeholder="PO-2026-..." class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Lot *</label>
                    <input type="text" name="no_lot" required placeholder="LOT-..." class="w-full text-xs rounded-lg border-slate-200 px-3 py-2 font-mono">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('registerGrnModal').classList.add('hidden')" class="px-3 py-2 rounded-lg text-slate-600 text-xs font-medium hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition">
                    Daftarkan ke Antrean
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
