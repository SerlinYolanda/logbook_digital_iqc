@extends('layouts.app')
@section('title', 'MRB Verification & Disposition - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">gavel</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Verifikasi & Disposisi MRB</h1>
                <p class="text-xs text-slate-500 mt-0.5">Verifikasi logbook anomali dan otorisasi disposisi MRB.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Pending MRB Queue</span>
                <span class="text-xl font-black text-amber-600 font-mono">{{ $pendingCount }} Lots</span>
            </div>
        </div>
    </div>

    <!-- Active Pending Review Queue -->
    <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600">pending_actions</span>
                Antrean Menunggu Disposisi MRB
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">GRN & Lot No</th>
                        <th class="py-3 px-4">GR & Lot No</th>
                        <th class="py-3 px-4">Component & Commodity</th>
                        <th class="py-3 px-4">Supplier / Vendor</th>
                        <th class="py-3 px-4">Inspector & Date</th>
                        <th class="py-3 px-4">Reason / Trigger</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingReviews as $log)
                    @php $rcv = $log->receiving; @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-blue-600">{{ $rcv->no_grn_iqa ?? '-' }}</span>
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
                        <td class="py-3.5 px-4 text-slate-600">
                            <div class="font-bold text-slate-800">{{ $log->inspector->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $log->tanggal_inspeksi ? $log->tanggal_inspeksi->format('d/m/Y H:i') : '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->nestProcess)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    NEST Process ({{ Str::limit($log->nestProcess->kronologi_masalah, 30) }})
                                </span>
                            @elseif($log->defects->count() > 0)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    {{ $log->defects->count() }} Defect Findings
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    Out-of-Spec Measurement
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('supervisor.mrb.show', $log->id) }}" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-indigo-600 hover:from-amber-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition">
                                <span class="material-symbols-outlined text-[16px]">gavel</span>
                                <span>Verify & Authorize</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            <span class="material-symbols-outlined text-[32px] text-emerald-500 block mb-1">task_alt</span>
                            No logbooks currently awaiting MRB disposition.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- History of Recent MRB Decisions -->
    <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="material-symbols-outlined text-purple-600">history</span>
            Historical MRB Disposition Log
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Decision Date</th>
                        <th class="py-3 px-4">GRN & Lot No</th>
                        <th class="py-3 px-4">GR & Lot No</th>
                        <th class="py-3 px-4">Component & Supplier</th>
                        <th class="py-3 px-4">Authorized Disposition</th>
                        <th class="py-3 px-4">Supervisor</th>
                        <th class="py-3 px-4">Disposition Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentDecisions as $item)
                    @php $rcv = $item->logbook->receiving ?? null; @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">
                            {{ $item->diputuskan_pada ? $item->diputuskan_pada->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-mono font-bold text-blue-600">{{ $rcv->no_grn_iqa ?? '-' }}</span>
                            <div class="text-[10px] text-slate-400 font-mono">Lot: {{ $rcv->no_lot ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">{{ $rcv->part->kode_part ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $rcv->supplier->nama_supplier ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold 
                                @if($item->status_keputusan === 'accept') bg-emerald-100 text-emerald-800
                                @elseif($item->status_keputusan === 'perlu_revisi') bg-orange-100 text-orange-800
                                @elseif($item->status_keputusan === 'return_to_vendor') bg-rose-100 text-rose-800
                                @elseif($item->status_keputusan === 'scrap') bg-red-200 text-red-900
                                @else bg-purple-100 text-purple-800 @endif">
                                {{ $item->decision_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-semibold">
                            {{ $item->supervisor->name ?? 'IQC Supervisor' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-mono text-[11px] max-w-[280px] truncate" title="{{ $item->catatan }}">
                            {{ $item->catatan }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400">No previous MRB dispositions recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentDecisions->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $recentDecisions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

