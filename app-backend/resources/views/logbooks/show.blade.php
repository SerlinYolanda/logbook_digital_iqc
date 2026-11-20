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

    <!-- Visual & Shelf-Life Verification Results -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Visual & Packaging Checklist Results -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                    Visual & Packaging Verification
                </span>
                @if($logbook->hasVisualFail())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        NON-CONFORMANCE
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        ALL VISUAL PASSED
                    </span>
                @endif
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-700 font-medium">Packaging & Label Integrity</span>
                    @if($logbook->visual_packaging === 'pass')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">PASS</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">FAIL</span>
                    @endif
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-700 font-medium">ESD Protection & Moisture Barrier (HIC)</span>
                    @if($logbook->visual_esd_moisture === 'pass')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">PASS</span>
                    @elseif($logbook->visual_esd_moisture === 'na')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">N/A</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">FAIL</span>
                    @endif
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-700 font-medium">Physical Body Condition (No Cracks)</span>
                    @if($logbook->visual_body === 'pass')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">PASS</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">FAIL</span>
                    @endif
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-700 font-medium">Lead Pin / Terminal (No Bent Leads/Oxidation)</span>
                    @if($logbook->visual_terminal === 'pass')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">PASS</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">FAIL</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Shelf-Life & Expiry Verification Results -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">event_available</span>
                    Shelf-Life & Expiration Status
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $logbook->shelf_life_badge_class }}">
                    @if($logbook->is_expired)
                        EXPIRED LOT
                    @elseif($logbook->shelf_life_status === 'near_expiry')
                        NEAR EXPIRY (&lt;30d)
                    @else
                        CERTIFIED VALID
                    @endif
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Date Code / Lot DOM</span>
                    <span class="font-mono font-bold text-slate-800 text-sm mt-0.5 block">
                        {{ $logbook->date_code ?? 'Not Recorded' }}
                    </span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Manufacturing Date</span>
                    <span class="font-mono font-bold text-slate-800 text-sm mt-0.5 block">
                        {{ $logbook->manufacturing_date ? $logbook->manufacturing_date->format('d/m/Y') : '-' }}
                    </span>
                </div>
                <div class="col-span-2 p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Expiration / Solderability Limit</span>
                        <span class="font-mono font-bold text-slate-800 text-sm mt-0.5 block">
                            {{ $logbook->expiration_date ? $logbook->expiration_date->format('d F Y') : 'Not Specified' }}
                        </span>
                    </div>
                    @if($logbook->expiration_date)
                        @php
                            $diff = (int) now()->diffInDays($logbook->expiration_date, false);
                        @endphp
                        <div class="text-right">
                            @if($diff < 0)
                                <span class="text-rose-600 font-bold font-mono text-xs">Expired {{ abs($diff) }} days ago</span>
                            @else
                                <span class="text-emerald-700 font-bold font-mono text-xs">{{ $diff }} days remaining</span>
                            @endif
                        </div>
                    @endif
                </div>
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

    <!-- Section: Quality Non-Conformance, Defects & NEST Escalation -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
        <!-- Section Header with Inspector Quick Actions -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-600">report_problem</span>
                    <h3 class="text-base font-bold text-slate-900">
                        Defects & NEST Escalation
                    </h3>
                </div>
            </div>

            <!-- Single Action Button: NEST Process -->
            @if(auth()->user()->isInspector() && in_array($logbook->status, ['draft', 'menunggu_penetapan', 'perlu_revisi']) && !$logbook->nestProcess)
            <button type="button" onclick="document.getElementById('nestModal').classList.remove('hidden')"
                class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[16px]">warning</span>
                <span>+ Submit NEST Process</span>
            </button>
            @endif
        </div>

        <!-- Integrated NEST Escalation Banner (Active Incident) -->
        @if($logbook->nestProcess)
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-300 space-y-2.5">
            <div class="flex justify-between items-center text-xs flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-600 text-white shadow-xs flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">warning</span>
                        Active NEST Special Escalation
                    </span>
                    <span class="font-bold text-amber-950">Submitted by: {{ $logbook->nestProcess->inspector->name ?? 'Inspector' }}</span>
                </div>
                <span class="text-slate-500 font-mono text-[11px]">{{ $logbook->nestProcess->diajukan_pada ? $logbook->nestProcess->diajukan_pada->format('d/m/Y H:i') : '-' }}</span>
            </div>
            <div class="text-xs text-slate-800 leading-relaxed font-sans bg-white p-3.5 rounded-xl border border-amber-200">
                <div class="text-[11px] font-extrabold uppercase text-amber-800 tracking-wider mb-1">Problem Chronology & Risk Analysis:</div>
                {{ $logbook->nestProcess->kronologi_masalah }}
            </div>
            @if($logbook->nestProcess->bukti_pendukung)
            <div class="text-xs pt-0.5">
                <a href="{{ asset('storage/' . $logbook->nestProcess->bukti_pendukung) }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline font-bold">
                    <span class="material-symbols-outlined text-[16px]">attachment</span>
                    <span>Open Supporting Evidence Document</span>
                </a>
            </div>
            @endif
        </div>
        @endif

        <!-- Interactive Filters Bar for Defects -->
        @php
            $totalDefectsCount = $logbook->defects->count();
            $criticalCount = $logbook->defects->where('tingkat_keparahan', 'critical')->count();
            $majorCount = $logbook->defects->where('tingkat_keparahan', 'major')->count();
            $minorCount = $logbook->defects->where('tingkat_keparahan', 'minor')->count();
            $withPhotoCount = $logbook->defects->filter(fn($d) => !empty($d->foto_bukti))->count();
        @endphp

        @if($totalDefectsCount > 0)
        <div class="flex items-center justify-between flex-wrap gap-2 pt-1 pb-1">
            <!-- Filter Pills -->
            <div class="flex items-center gap-1.5 flex-wrap" id="defectFilterPills">
                <button type="button" onclick="filterDefects('all')" class="defect-filter-btn px-3 py-1 rounded-lg text-xs font-bold transition bg-slate-900 text-white shadow-xs" data-filter="all">
                    All Issues ({{ $totalDefectsCount }})
                </button>
                @if($criticalCount > 0)
                <button type="button" onclick="filterDefects('critical')" class="defect-filter-btn px-3 py-1 rounded-lg text-xs font-semibold transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200" data-filter="critical">
                    Critical ({{ $criticalCount }})
                </button>
                @endif
                @if($majorCount > 0)
                <button type="button" onclick="filterDefects('major')" class="defect-filter-btn px-3 py-1 rounded-lg text-xs font-semibold transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200" data-filter="major">
                    Major ({{ $majorCount }})
                </button>
                @endif
                @if($minorCount > 0)
                <button type="button" onclick="filterDefects('minor')" class="defect-filter-btn px-3 py-1 rounded-lg text-xs font-semibold transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200" data-filter="minor">
                    Minor ({{ $minorCount }})
                </button>
                @endif
                @if($withPhotoCount > 0)
                <button type="button" onclick="filterDefects('photo')" class="defect-filter-btn px-3 py-1 rounded-lg text-xs font-semibold transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200" data-filter="photo">
                    <span class="inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">photo_camera</span>
                        With Photos ({{ $withPhotoCount }})
                    </span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input inside Defect section -->
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                <input type="text" id="defectSearchInput" onkeyup="searchDefects(this.value)" placeholder="Search defect notes or category..."
                    class="w-full text-xs rounded-xl border border-slate-200 pl-8 pr-3 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
        </div>
        @endif

        <!-- Defect Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="defectsGrid">
            @forelse($logbook->defects as $defect)
            <div class="defect-card p-4 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between transition hover:border-slate-300 hover:shadow-xs"
                data-severity="{{ strtolower($defect->tingkat_keparahan) }}"
                data-has-photo="{{ !empty($defect->foto_bukti) ? 'true' : 'false' }}"
                data-text="{{ strtolower(($defect->defectCategory->nama_defect ?? '') . ' ' . $defect->deskripsi) }}">
                <div>
                    <div class="flex justify-between items-start gap-2">
                        <span class="font-bold text-slate-900 text-xs">{{ $defect->defectCategory->nama_defect ?? 'IQC Defect' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $defect->severity_badge_class }}">
                            {{ strtoupper($defect->tingkat_keparahan) }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-600 mt-2">{{ $defect->deskripsi }}</div>
                    <div class="text-[11px] text-slate-500 mt-1 font-mono">Defect Qty: <b class="text-slate-800">{{ $defect->qty_defect }} pcs</b></div>
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
            <div class="col-span-full py-8 text-center text-slate-400">
                <span class="material-symbols-outlined text-[32px] text-emerald-500 block mb-1">verified</span>
                <span class="text-xs font-medium text-slate-600 block">No physical defects or visual anomalies recorded for this lot.</span>
                <span class="text-[11px] text-slate-400">Standard sampling inspection passed with zero non-conformances.</span>
            </div>
            @endforelse
        </div>

        <div id="defectNoFilterResult" class="hidden py-6 text-center text-slate-400 text-xs">
            <span class="material-symbols-outlined text-[24px] text-slate-300 block mb-1">filter_alt_off</span>
            No defects match the selected filter criteria.
        </div>
    </div>

    <!-- Section: Supervisor MRB Dispositions History -->
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

<!-- Modal Record Defect -->
<div id="addDefectModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-600">bug_report</span>
                Record Defect & Upload Photo
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

<!-- Modal Submit NEST Escalation -->
<div id="nestModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600">track_changes</span>
                Submit NEST Special Track Escalation
            </h3>
            <button type="button" onclick="document.getElementById('nestModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('logbooks.nest.store', $logbook->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Defect Category (Optional if physical defect exists)</label>
                <select name="defect_category_id" class="w-full text-xs rounded-xl border-slate-200">
                    <option value="">No specific physical defect (Process / Anomaly only)</option>
                    @foreach($defectCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->kode_defect }} - {{ $cat->nama_defect }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Defect Qty (pcs)</label>
                    <input type="number" name="qty_defect" min="1" value="1" class="w-full text-xs rounded-xl border-slate-200 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Severity Level</label>
                    <select name="tingkat_keparahan" class="w-full text-xs rounded-xl border-slate-200">
                        <option value="minor">Minor</option>
                        <option value="major" selected>Major</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Problem Chronology & Risk Analysis *</label>
                <textarea name="kronologi_masalah" required rows="3" 
                    placeholder="Provide details on the discovered anomaly, defect location, and potential line risk..." 
                    class="w-full text-xs rounded-xl border-slate-200"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Supporting Photo / Evidence Document (Max 10MB)</label>
                <input type="file" name="bukti_pendukung" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs rounded-xl border-slate-200">
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

<script>
let currentDefectFilter = 'all';
let defectSearchTerm = '';

function filterDefects(type) {
    currentDefectFilter = type;
    document.querySelectorAll('.defect-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-filter') === type) {
            btn.className = 'defect-filter-btn px-3 py-1 rounded-lg text-xs font-bold transition bg-slate-900 text-white shadow-xs';
        } else {
            btn.className = 'defect-filter-btn px-3 py-1 rounded-lg text-xs font-semibold transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200';
        }
    });
    applyDefectFilters();
}

function searchDefects(query) {
    defectSearchTerm = query.toLowerCase().trim();
    applyDefectFilters();
}

function applyDefectFilters() {
    const cards = document.querySelectorAll('.defect-card');
    let visibleCount = 0;
    cards.forEach(card => {
        const severity = card.getAttribute('data-severity');
        const hasPhoto = card.getAttribute('data-has-photo') === 'true';
        const text = card.getAttribute('data-text') || '';

        let matchesFilter = true;
        if (currentDefectFilter === 'critical' && severity !== 'critical') matchesFilter = false;
        if (currentDefectFilter === 'major' && severity !== 'major') matchesFilter = false;
        if (currentDefectFilter === 'minor' && severity !== 'minor') matchesFilter = false;
        if (currentDefectFilter === 'photo' && !hasPhoto) matchesFilter = false;

        let matchesSearch = true;
        if (defectSearchTerm && !text.includes(defectSearchTerm)) matchesSearch = false;

        if (matchesFilter && matchesSearch) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const noResult = document.getElementById('defectNoFilterResult');
    if (noResult) {
        noResult.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
    }
}
</script>
@endsection

