@extends('layouts.app')
@section('title', 'Inspection Logbook Details #' . $logbook->id . ' - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">fact_check</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">IQC Quality Logbook Sheet</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $logbook->status_badge_class }}">
                        {{ $logbook->status_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">GRN No: <b class="font-mono text-slate-800">{{ $logbook->receiving->no_grn_iqa }}</b> | Lot: <b class="font-mono text-slate-800">{{ $logbook->receiving->no_lot }}</b></p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>Print Sheet</span>
            </button>
            <a href="{{ route('logbooks.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                Back
            </a>
        </div>
    </div>

    <!-- Alert Banner if Logbook is Under Revision -->
    @if($logbook->status === 'perlu_revisi')
    <div class="p-5 rounded-3xl bg-amber-50 border border-amber-300 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-[22px]">edit_note</span>
            </div>
            <div>
                <h4 class="text-sm font-bold text-amber-900">Attention: This logbook requires measurement data revision!</h4>
                <div class="text-xs text-amber-800 mt-1 bg-white/80 p-3 rounded-xl border border-amber-200 font-mono">
                    <b>Supervisor Notes:</b> {{ $logbook->latestMrbDisposition->catatan ?? 'Please re-examine sample measurement values.' }}
                </div>
            </div>
        </div>
        @if(auth()->user()->isInspector())
        <a href="{{ route('logbooks.edit', $logbook->id) }}" class="px-5 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition shrink-0">
            Correct & Revise Now &rarr;
        </a>
        @endif
    </div>
    @endif

    <!-- Material & Inspection Overview Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">info</span>
                Material Data & Standard Specifications
            </span>
            <span class="text-xs text-slate-400 font-mono">Date: {{ $logbook->tanggal_inspeksi ? $logbook->tanggal_inspeksi->format('d F Y H:i') : '-' }}</span>
        </div>

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
                <span class="text-slate-400">Responsible Inspector:</span>
                <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $logbook->inspector->name }}</div>
                <div class="text-slate-500 mt-0.5">{{ $logbook->inspector->role_label }}</div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs pt-3 border-t border-slate-100">
            <div>
                <span class="text-slate-400">PO No:</span>
                <div class="font-mono font-bold text-slate-800">{{ $logbook->receiving->no_po }}</div>
            </div>
            <div>
                <span class="text-slate-400">Lot No:</span>
                <div class="font-mono font-bold text-slate-800">{{ $logbook->receiving->no_lot }}</div>
            </div>
            <div>
                <span class="text-slate-400">Inbound Qty:</span>
                <div class="font-mono font-bold text-slate-800">{{ number_format($logbook->receiving->qty_datang) }} pcs</div>
            </div>
            <div>
                <span class="text-slate-400">Inspection Sample Size:</span>
                <div class="font-mono font-bold text-indigo-700">{{ $logbook->qty_sampel }} samples</div>
            </div>
        </div>
    </div>

    <!-- Parameter Measurements Results Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">straighten</span>
                Sample Measurements vs Nominal Tolerances
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <th class="py-3 px-3">Inspection Parameter</th>
                        <th class="py-3 px-3 text-center">Nominal</th>
                        <th class="py-3 px-3 text-center">Min Limit</th>
                        <th class="py-3 px-3 text-center">Max Limit</th>
                        <th class="py-3 px-3 text-center">Unit</th>
                        @for($s = 1; $s <= $logbook->qty_sampel; $s++)
                            <th class="py-3 px-3 text-center">S-{{ $s }}</th>
                        @endfor
                        <th class="py-3 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logbook->receiving->part->parameters as $param)
                    @php
                        $paramMeasurements = $logbook->measurements->where('part_parameter_id', $param->id);
                        $hasFail = $paramMeasurements->where('status_toleransi', 'fail')->count() > 0;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-3 font-bold text-slate-800">{{ $param->nama_parameter }}</td>
                        <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">{{ $param->nominal !== null ? number_format($param->nominal, 2) : '-' }}</td>
                        <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $param->batas_min !== null ? number_format($param->batas_min, 2) : '-' }}</td>
                        <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $param->batas_max !== null ? number_format($param->batas_max, 2) : '-' }}</td>
                        <td class="py-3 px-3 text-center font-mono text-indigo-600 font-bold">{{ $param->satuan }}</td>
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
                    @empty
                    <tr>
                        <td colspan="12" class="py-6 text-center text-slate-400">No specification parameters recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section: Defect Recording & Evidence Photos (UC-05) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-600">bug_report</span>
                    Defect Records & Photo Evidence (UC-05)
                </h3>
                <p class="text-xs text-slate-500">Physical, visual, or functional non-conformance defects found during inspection.</p>
            </div>

            @if(auth()->user()->isInspector() && in_array($logbook->status, ['draft', 'menunggu_penetapan', 'perlu_revisi']))
            <button type="button" onclick="document.getElementById('addDefectModal').classList.remove('hidden')"
                class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-[16px]">add_a_photo</span>
                <span>+ Record Defect & Photo</span>
            </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($logbook->defects as $defect)
            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start gap-2">
                        <span class="font-bold text-slate-900 text-xs">{{ $defect->defectCategory->nama_defect ?? 'IQC Defect' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $defect->severity_badge_class }}">
                            {{ strtoupper($defect->tingkat_keparahan) }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-600 mt-2">{{ $defect->deskripsi }}</div>
                    <div class="text-[11px] text-slate-400 mt-1 font-mono">Defect Qty: <b>{{ $defect->qty_defect }} pcs</b></div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
                    @if($defect->foto_bukti)
                        <a href="{{ asset('storage/' . $defect->foto_bukti) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline font-bold">
                            <span class="material-symbols-outlined text-[14px]">image</span>
                            <span>View Photo Evidence</span>
                        </a>
                    @else
                        <span class="text-[11px] text-slate-400 italic">No photo attached</span>
                    @endif

                    @if(auth()->user()->isInspector() && in_array($logbook->status, ['draft', 'perlu_revisi']))
                    <form action="{{ route('defects.destroy', $defect->id) }}" method="POST" onsubmit="return confirm('Delete this defect record?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs" title="Delete">
                            <span class="material-symbols-outlined text-[16px]">delete</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full py-6 text-center text-slate-400">
                <span class="material-symbols-outlined text-[28px] text-emerald-500 block mb-1">check_circle</span>
                No defects recorded on this lot.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Section: NEST Process (Not Evaluated Special Track) (UC-06) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600">track_changes</span>
                    NEST Special Track Process (UC-06)
                </h3>
                <p class="text-xs text-slate-500">Not Evaluated Special Track: Handling high-risk lot anomalies requiring MRB disposition.</p>
            </div>

            @if(auth()->user()->isInspector() && in_array($logbook->status, ['draft', 'menunggu_penetapan', 'perlu_revisi']) && !$logbook->nestProcess)
            <button type="button" onclick="document.getElementById('nestModal').classList.remove('hidden')"
                class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                <span class="material-symbols-outlined text-[16px]">assignment_late</span>
                <span>+ Submit NEST Process</span>
            </button>
            @endif
        </div>

        @if($logbook->nestProcess)
        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200 space-y-2">
            <div class="flex justify-between items-center text-xs">
                <span class="font-bold text-amber-900 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-amber-600">pending</span>
                    Submitted by: {{ $logbook->nestProcess->inspector->name ?? 'Inspector' }}
                </span>
                <span class="text-slate-500 font-mono">{{ $logbook->nestProcess->diajukan_pada ? $logbook->nestProcess->diajukan_pada->format('d/m/Y H:i') : '-' }}</span>
            </div>
            <div class="text-xs text-slate-800 leading-relaxed font-sans bg-white p-3.5 rounded-xl border border-amber-200/80">
                <b>Problem Chronology & Risk Analysis:</b><br>
                {{ $logbook->nestProcess->kronologi_masalah }}
            </div>
            @if($logbook->nestProcess->bukti_pendukung)
            <div class="text-xs pt-1">
                <a href="{{ asset('storage/' . $logbook->nestProcess->bukti_pendukung) }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline font-bold">
                    <span class="material-symbols-outlined text-[16px]">attachment</span>
                    <span>Open NEST Supporting Evidence Document</span>
                </a>
            </div>
            @endif
        </div>
        @else
        <div class="py-4 text-center text-slate-400 text-xs">
            This lot did not go through the NEST special track (standard inspection procedure).
        </div>
        @endif
    </div>

    <!-- Section: Supervisor MRB Dispositions History (UC-10) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
            <span class="material-symbols-outlined text-purple-600">gavel</span>
            Material Review Board (MRB) Dispositions
        </h3>

        <div class="space-y-3">
            @forelse($logbook->mrbDispositions as $disp)
            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-800">
                        Disposition: <span class="text-indigo-600 font-black">{{ $disp->decision_label }}</span>
                    </span>
                    <span class="text-slate-400 font-mono">{{ $disp->diputuskan_pada ? $disp->diputuskan_pada->format('d/m/Y H:i') : '-' }}</span>
                </div>
                <div class="text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-200 font-mono">
                    {{ $disp->catatan }}
                </div>
                <div class="text-[11px] text-slate-400 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">verified</span>
                    Verified by Supervisor: <b>{{ $disp->supervisor->name ?? 'IQC Supervisor' }}</b>
                </div>
            </div>
            @empty
            <div class="py-4 text-center text-slate-400 text-xs">
                No MRB disposition has been recorded for this lot yet.
            </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Record Defect (UC-05) -->
<div id="addDefectModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-600">bug_report</span>
                Record Defect & Upload Photo (UC-05)
            </h3>
            <button type="button" onclick="document.getElementById('addDefectModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('logbooks.defects.store', $logbook->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Defect Category *</label>
                <select name="defect_category_id" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">Select Defect Category...</option>
                    @foreach($defectCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->kode_defect }} - {{ $cat->nama_defect }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Defect Qty (pcs) *</label>
                    <input type="number" name="qty_defect" required min="1" value="1" class="w-full text-xs rounded-xl border-slate-200 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Severity Level *</label>
                    <select name="tingkat_keparahan" required class="w-full text-xs rounded-xl border-slate-200">
                        <option value="minor">Minor</option>
                        <option value="major" selected>Major</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Problem Description / Defect Location *</label>
                <textarea name="deskripsi" required rows="3" placeholder="Describe the defect and affected pins / areas..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Upload Defect Photo Evidence (Max 5MB)</label>
                <input type="file" name="foto_bukti" accept="image/*" class="w-full text-xs rounded-xl border-slate-200">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('addDefectModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/20">
                    Save Defect Record
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Submit NEST (UC-06) -->
<div id="nestModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600">track_changes</span>
                Submit NEST Special Track (UC-06)
            </h3>
            <button type="button" onclick="document.getElementById('nestModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('logbooks.nest.store', $logbook->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Problem Chronology & Risk Analysis *</label>
                <textarea name="kronologi_masalah" required rows="4" 
                    placeholder="Provide details on the discovered anomaly and potential production line risk..." 
                    class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Supporting Document / Evidence (PDF/Image Max 10MB)</label>
                <input type="file" name="bukti_pendukung" class="w-full text-xs rounded-xl border-slate-200">
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800">
                <b>SOP Note:</b> Submitting NEST will transition this logbook to <b>Pending MRB Review</b> and notify the Quality Supervisor for validation and disposition.
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('nestModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-500/20">
                    Submit NEST Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

