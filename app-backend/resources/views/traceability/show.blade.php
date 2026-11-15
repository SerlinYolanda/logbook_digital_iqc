@extends('layouts.app')
@section('title', 'Lot History Traceability ' . $receiving->no_lot . ' - IQC Digital Logbook')
@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">timeline</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Material Quality Traceability Journey</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Lot: {{ $receiving->no_lot }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Comprehensive end-to-end quality tracking from supplier to IQC final disposition.</p>
            </div>
        </div>

        <a href="{{ route('traceability.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
            &larr; Back to Search
        </a>
    </div>

    <!-- Material Master Spec Header -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400">Component / Part:</span>
                <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $receiving->part->nama_part }}</div>
                <div class="font-mono text-slate-500 mt-0.5">{{ $receiving->part->kode_part }}</div>
            </div>
            <div>
                <span class="text-slate-400">Commodity & Standard:</span>
                <div class="font-bold text-indigo-700 mt-0.5">{{ $receiving->part->komoditas_label }}</div>
                <div class="text-slate-500 mt-0.5">{{ $receiving->part->standar_aql }}</div>
            </div>
            <div>
                <span class="text-slate-400">Supplier & Origin:</span>
                <div class="font-bold text-slate-800 mt-0.5">{{ $receiving->supplier->nama_supplier }}</div>
                <div class="text-slate-500 mt-0.5">{{ $receiving->supplier->negara_asal }}</div>
            </div>
            <div>
                <span class="text-slate-400">Inbound Qty & GRN:</span>
                <div class="font-mono font-bold text-slate-800 mt-0.5">{{ number_format($receiving->qty_datang) }} pcs</div>
                <div class="font-mono text-slate-500 mt-0.5">{{ $receiving->no_grn_iqa }}</div>
            </div>
        </div>
    </div>

    <!-- Visual Stepper Timeline -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-8">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 pb-4 border-b border-slate-100">
            <span class="material-symbols-outlined text-indigo-600">route</span>
            Material Quality Lifecycle Journey
        </h3>

        <div class="relative pl-6 sm:pl-8 border-l-2 border-indigo-200 space-y-8">
            <!-- Step 1: Inbound GRN Arrival -->
            <div class="relative">
                <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-md shadow-blue-500/30">
                    1
                </span>
                <div>
                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider block">
                        {{ $receiving->tanggal_terima ? $receiving->tanggal_terima->format('d M Y - H:i') : '-' }} WIB
                    </span>
                    <h4 class="text-sm font-bold text-slate-900 mt-0.5">Material Arrived at Receiving Dock & Registered to GRN</h4>
                    <p class="text-xs text-slate-600 mt-1">
                        Material received with GRN No <b class="font-mono">{{ $receiving->no_grn_iqa }}</b>, PO <b class="font-mono">{{ $receiving->no_po }}</b>, Qty <b class="font-mono">{{ number_format($receiving->qty_datang) }} pcs</b>.
                    </p>
                    <div class="mt-2 text-[11px]">
                        Queue Status: <span class="px-2 py-0.5 rounded font-bold border {{ $receiving->aging_badge_class }}">{{ $receiving->aging_status }}</span>
                    </div>
                </div>
            </div>

            <!-- Step 2: Sampling Inspection -->
            @php $latestLog = $receiving->latest_logbook; @endphp
            <div class="relative">
                <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full 
                    {{ $latestLog ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-xs font-bold shadow-md">
                    2
                </span>
                <div>
                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider block">
                        {{ $latestLog && $latestLog->tanggal_inspeksi ? $latestLog->tanggal_inspeksi->format('d M Y - H:i') : 'Pending Inspection' }}
                    </span>
                    <h4 class="text-sm font-bold text-slate-900 mt-0.5">MIL-STD-105E Sampling Quality Inspection</h4>
                    @if($latestLog)
                        <p class="text-xs text-slate-600 mt-1">
                            Inspected by <b class="text-slate-800">{{ $latestLog->inspector->name }}</b> ({{ $latestLog->inspector->role_label }}). Total test samples: <b class="font-mono">{{ $latestLog->qty_sampel }} pcs</b>.
                        </p>
                        <div class="mt-2 text-xs">
                            Logbook Status: <span class="px-2.5 py-0.5 rounded-full font-bold border {{ $latestLog->status_badge_class }}">{{ $latestLog->status_label }}</span>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 mt-1 italic">Material is still in the queue and has not been inspected yet.</p>
                    @endif
                </div>
            </div>

            <!-- Step 3: Defect & Non-conformance (if any) -->
            @if($latestLog && $latestLog->defects->count() > 0)
            <div class="relative">
                <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs font-bold shadow-md shadow-rose-500/30">
                    3
                </span>
                <div>
                    <h4 class="text-sm font-bold text-rose-700">Non-conformance Found ({{ $latestLog->defects->count() }} Defect Findings)</h4>
                    <div class="mt-2 space-y-2">
                        @foreach($latestLog->defects as $def)
                        <div class="p-3 bg-rose-50/70 rounded-xl border border-rose-200 text-xs">
                            <div class="flex justify-between items-center font-bold text-slate-800">
                                <span>{{ $def->defectCategory->nama_defect ?? 'Defect' }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] {{ $def->severity_badge_class }}">{{ strtoupper($def->tingkat_keparahan) }}</span>
                            </div>
                            <p class="text-slate-600 mt-1">{{ $def->deskripsi }} (Qty: {{ $def->qty_defect }} pcs)</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Step 4: NEST Track (if any) -->
            @if($latestLog && $latestLog->nestProcess)
            <div class="relative">
                <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold shadow-md shadow-amber-500/30">
                    4
                </span>
                <div>
                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider block">
                        {{ $latestLog->nestProcess->diajukan_pada ? $latestLog->nestProcess->diajukan_pada->format('d M Y - H:i') : '-' }} WIB
                    </span>
                    <h4 class="text-sm font-bold text-amber-800 mt-0.5">NEST Special Track (Not Evaluated Special Track) Activated</h4>
                    <p class="text-xs text-slate-600 mt-1 bg-amber-50 p-3 rounded-xl border border-amber-200 font-mono">
                        {{ $latestLog->nestProcess->kronologi_masalah }}
                    </p>
                </div>
            </div>
            @endif

            <!-- Step 5: MRB Disposition Decision -->
            @php $disp = $latestLog ? $latestLog->latestMrbDisposition : null; @endphp
            <div class="relative">
                <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full 
                    {{ $disp ? 'bg-purple-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-xs font-bold shadow-md">
                    5
                </span>
                <div>
                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider block">
                        {{ $disp && $disp->diputuskan_pada ? $disp->diputuskan_pada->format('d M Y - H:i') : 'Pending Decision' }}
                    </span>
                    <h4 class="text-sm font-bold text-slate-900 mt-0.5">Official Material Review Board (MRB) Disposition Determination</h4>
                    @if($disp)
                        <div class="mt-2 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1.5">
                            <div class="font-bold text-slate-900">
                                Disposition: <span class="text-indigo-600 font-black text-sm">{{ $disp->decision_label }}</span>
                            </div>
                            <div class="text-slate-600 font-mono text-[11px] bg-white p-2.5 rounded-lg border border-slate-200">
                                <b>Supervisor Justification:</b> {{ $disp->catatan }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                Determined by: <b>{{ $disp->supervisor->name ?? 'IQC Supervisor' }}</b>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 mt-1 italic">No official disposition determined yet by IQC Supervisor.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection