@extends('layouts.app')
@section('title', 'MRB Verification & Disposition #' . $logbook->id . ' - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">gavel</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">MRB Verification & Disposition Decision</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $logbook->status_badge_class }}">
                        {{ $logbook->status_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Logbook #{{ $logbook->id }} | GRN No: <b class="font-mono text-slate-800">{{ $logbook->receiving->no_grn_iqa }}</b> | Lot: <b class="font-mono text-slate-800">{{ $logbook->receiving->no_lot }}</b></p>
                <p class="text-xs text-slate-500 mt-0.5">Logbook #{{ $logbook->id }} | GR No: <b class="font-mono text-slate-800">{{ $logbook->receiving->no_grn_iqa }}</b> | Lot: <b class="font-mono text-slate-800">{{ $logbook->receiving->no_lot }}</b></p>
            </div>
        </div>

        <a href="{{ route('supervisor.mrb.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
            &larr; Back to Queue
        </a>
    </div>

    <!-- Material Summary Card -->
    <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400">Component / Part:</span>
                <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $logbook->receiving->part->nama_part }}</div>
                <div class="font-mono text-slate-500 mt-0.5">{{ $logbook->receiving->part->kode_part }}</div>
            </div>
            <div>
                <span class="text-slate-400">Commodity & Standard:</span>
                <div class="font-bold text-indigo-700 mt-0.5">{{ $logbook->receiving->part->komoditas_label }}</div>
                <div class="text-slate-500 mt-0.5">{{ $logbook->receiving->part->standar_aql }}</div>
            </div>
            <div>
                <span class="text-slate-400">Supplier & Origin:</span>
                <div class="font-bold text-slate-800 mt-0.5">{{ $logbook->receiving->supplier->nama_supplier }}</div>
                <div class="text-slate-500 mt-0.5">{{ $logbook->receiving->supplier->negara_asal }}</div>
            </div>
            <div>
                <span class="text-slate-400">Inspector & Date:</span>
                <div class="font-bold text-slate-800 mt-0.5">{{ $logbook->inspector->name }}</div>
                <div class="text-slate-500 mt-0.5">{{ $logbook->tanggal_inspeksi ? $logbook->tanggal_inspeksi->format('d/m/Y H:i') : '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Parameter Measurements Table -->
    <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="material-symbols-outlined text-blue-600">straighten</span>
            Sample Measurements Verification vs Tolerance Limits
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <th class="py-3 px-3">Test Parameter</th>
                        <th class="py-3 px-3 text-center">Nominal</th>
                        <th class="py-3 px-3 text-center">Min - Max</th>
                        <th class="py-3 px-3 text-center">Unit</th>
                        @for($s = 1; $s <= $logbook->qty_sampel; $s++)
                            <th class="py-3 px-3 text-center">S-{{ $s }}</th>
                        @endfor
                        <th class="py-3 px-3 text-center">Evaluation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($logbook->receiving->part->parameters as $param)
                    @php
                        $paramMeasurements = $logbook->measurements->where('part_parameter_id', $param->id);
                        $hasFail = $paramMeasurements->where('status_toleransi', 'fail')->count() > 0;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-3 font-bold text-slate-800">{{ $param->nama_parameter }}</td>
                        <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">{{ $param->nominal !== null ? number_format($param->nominal, 2) : '-' }}</td>
                        <td class="py-3 px-3 text-center font-mono text-slate-500">
                            {{ $param->batas_min !== null ? number_format($param->batas_min, 2) : '-' }} to {{ $param->batas_max !== null ? number_format($param->batas_max, 2) : '-' }}
                        </td>
                        <td class="py-3 px-3 text-center font-mono text-blue-600 font-bold">{{ $param->satuan }}</td>
                        @for($s = 1; $s <= $logbook->qty_sampel; $s++)
                            @php $m = $paramMeasurements->where('sample_number', $s)->first(); @endphp
                            <td class="py-3 px-3 text-center font-mono">
                                @if($m)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold 
                                        {{ $m->status_toleransi === 'pass' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                        {{ number_format($m->nilai_ukur, 2) }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        @endfor
                        <td class="py-3 px-3 text-center">
                            @if($hasFail)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">OUT OF SPEC</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">PASS</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Defect & NEST Evidence Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Defect Cards -->
        <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="material-symbols-outlined text-rose-600">bug_report</span>
                Defect Findings ({{ $logbook->defects->count() }} items)
            </h3>
            <div class="space-y-3">
                @forelse($logbook->defects as $defect)
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-2">
                    <div class="flex justify-between items-start">
                        <span class="font-bold text-xs text-slate-900">{{ $defect->defectCategory->nama_defect ?? 'Defect' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $defect->severity_badge_class }}">
                            {{ strtoupper($defect->tingkat_keparahan) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 font-sans">{{ $defect->deskripsi }}</p>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/80 text-[11px]">
                        <span class="text-slate-500 font-mono">Defect Qty: <b>{{ $defect->qty_defect }} pcs</b></span>
                        @if($defect->foto_bukti)
                            <a href="{{ asset('storage/' . $defect->foto_bukti) }}" target="_blank" class="text-blue-600 font-bold hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">image</span>
                                <span>View Photo</span>
                            </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-slate-400 text-xs">No physical defect records.</div>
                @endforelse
            </div>
        </div>

        <!-- NEST Chronology Card -->
        <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="material-symbols-outlined text-amber-600">track_changes</span>
                NEST Track Issue Chronology
            </h3>
            @if($logbook->nestProcess)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 space-y-3">
                <div class="flex justify-between items-center text-xs text-amber-900">
                    <span class="font-bold">Submitted by: {{ $logbook->nestProcess->inspector->name ?? 'Inspector' }}</span>
                    <span class="font-mono text-[11px] text-slate-500">{{ $logbook->nestProcess->diajukan_pada ? $logbook->nestProcess->diajukan_pada->format('d/m/Y H:i') : '-' }}</span>
                </div>
                <div class="text-xs text-slate-800 leading-relaxed bg-white p-3.5 rounded-xl border border-amber-200/80 font-mono">
                    {{ $logbook->nestProcess->kronologi_masalah }}
                </div>
                @if($logbook->nestProcess->bukti_pendukung)
                <div class="pt-1">
                    <a href="{{ asset('storage/' . $logbook->nestProcess->bukti_pendukung) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:underline font-bold">
                        <span class="material-symbols-outlined text-[16px]">attachment</span>
                        <span>View NEST Supporting Evidence Document</span>
                    </a>
                </div>
                @endif
            </div>
            @else
            <div class="py-6 text-center text-slate-400 text-xs">This lot did not go through the NEST process.</div>
            @endif
        </div>
    </div>

    <!-- Official MRB Disposition Decision Form -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-xl p-6 sm:p-8 text-white shadow-xl space-y-6">
        <div>
            <div class="flex items-center gap-2 text-amber-400 text-xs font-bold uppercase tracking-wider">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span>Material Review Board (MRB) Disposition Determination</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black mt-1 tracking-tight">Official IQC Quality Supervisor Decision</h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">
                Select an official disposition decision based on measurement results, supporting documents, and ISO 9001:2015 tolerance standards.
            </p>
        </div>

        <form action="{{ route('supervisor.mrb.decide', $logbook->id) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Disposition Radio Options -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <label class="p-4 rounded-2xl bg-white/10 border border-white/10 hover:bg-white/20 transition cursor-pointer flex flex-col justify-between has-[:checked]:bg-emerald-600/30 has-[:checked]:border-emerald-400 has-[:checked]:ring-2 has-[:checked]:ring-emerald-400">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-white">Accept (Passed)</span>
                        <input type="radio" name="status_keputusan" value="accept" required class="accent-emerald-500">
                    </div>
                    <span class="text-[10px] text-slate-300 mt-2">Use component (Use As Is). Pass to SMT production line.</span>
                </label>

                <label class="p-4 rounded-2xl bg-white/10 border border-white/10 hover:bg-white/20 transition cursor-pointer flex flex-col justify-between has-[:checked]:bg-rose-600/30 has-[:checked]:border-rose-400 has-[:checked]:ring-2 has-[:checked]:ring-rose-400">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-white">Return to Vendor</span>
                        <input type="radio" name="status_keputusan" value="return_to_vendor" class="accent-rose-500">
                    </div>
                    <span class="text-[10px] text-slate-300 mt-2">Return to supplier (RTV) due to major/critical defects.</span>
                </label>

                <label class="p-4 rounded-2xl bg-white/10 border border-white/10 hover:bg-white/20 transition cursor-pointer flex flex-col justify-between has-[:checked]:bg-red-700/40 has-[:checked]:border-red-400 has-[:checked]:ring-2 has-[:checked]:ring-red-400">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-white">Scrap</span>
                        <input type="radio" name="status_keputusan" value="scrap" class="accent-red-500">
                    </div>
                    <span class="text-[10px] text-slate-300 mt-2">Dispose of components in quarantine scrap, claim replacement.</span>
                </label>

                <label class="p-4 rounded-2xl bg-white/10 border border-white/10 hover:bg-white/20 transition cursor-pointer flex flex-col justify-between has-[:checked]:bg-purple-600/30 has-[:checked]:border-purple-400 has-[:checked]:ring-2 has-[:checked]:ring-purple-400">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-white">Rework</span>
                        <input type="radio" name="status_keputusan" value="rework" class="accent-purple-500">
                    </div>
                    <span class="text-[10px] text-slate-300 mt-2">Perform 100% sorting or component pin rework.</span>
                </label>

                <label class="p-4 rounded-2xl bg-white/10 border border-white/10 hover:bg-white/20 transition cursor-pointer flex flex-col justify-between has-[:checked]:bg-orange-600/30 has-[:checked]:border-orange-400 has-[:checked]:ring-2 has-[:checked]:ring-orange-400">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-white">Request Revision</span>
                        <input type="radio" name="status_keputusan" value="perlu_revisi" class="accent-orange-500">
                    </div>
                    <span class="text-[10px] text-slate-300 mt-2">Return to Inspector for sample re-measurement or tool calibration.</span>
                </label>
            </div>

            <!-- Mandatory Justification Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-200 mb-1">MRB Technical Justification & Considerations *</label>
                <textarea name="catatan" required rows="3" 
                    placeholder="Enter technical justification for the disposition decision (e.g., Pin pitch parameter meets upper tolerance limit. Disposition approved for production batch 1)..." 
                    class="w-full text-xs rounded-2xl bg-white/10 border-white/20 text-white placeholder:text-slate-400 focus:ring-2 focus:ring-amber-400 p-3.5 font-mono"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('supervisor.mrb.index') }}" class="px-5 py-2.5 rounded-2xl text-slate-300 text-xs font-bold hover:bg-white/10 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-indigo-600 hover:from-amber-600 hover:to-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-500/30 transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    <span>Confirm & Submit MRB Disposition</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

