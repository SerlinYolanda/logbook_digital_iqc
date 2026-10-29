@extends('layouts.app')

@section('title', 'Inspection Logbook Detail #' . $inspection->lot_number)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inspections.index') }}" class="p-1.5 rounded-lg bg-slate-100 text-slate-400 hover:text-slate-900 transition">
                    <span class="material-symbols-outlined icon-sm">arrow_back</span>
                </a>
                <h1 class="text-xl font-bold text-slate-900">Inspection Detail: {{ $inspection->lot_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold 
                    {{ $inspection->status === 'approved' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 
                       ($inspection->status === 'rejected' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                    {{ strtoupper($inspection->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                No. PO: <span class="text-slate-900 font-mono font-semibold">{{ $inspection->po_number }}</span> • Test Date: <span class="text-slate-900 font-mono">{{ $inspection->inspection_date->format('d M Y') }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="openDefectModal({{ $inspection->id }}, '{{ $inspection->lot_number }}', '{{ $inspection->part->part_name ?? '' }}')" 
                class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-semibold flex items-center gap-1.5 transition">
                <span class="material-symbols-outlined icon-sm">bug_report</span>
                <span>Log Defect</span>
            </button>
            @if(auth()->user()->isSupervisor() && $inspection->status === 'pending_approval')
                <a href="{{ route('supervisor.review', $inspection->id) }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-slate-900 font-semibold text-xs shadow-lg shadow-amber-600/30 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined icon-sm">verified</span>
                    <span>Validate Now</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Part & Inspection Specs Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white/90 border border-slate-200 p-5 rounded-2xl">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Component Part</span>
            <div class="font-bold text-slate-900 text-sm">{{ $inspection->part->part_name ?? '-' }}</div>
            <div class="text-xs text-indigo-600 font-mono mt-0.5">{{ $inspection->part->part_number ?? '-' }}</div>
            <div class="text-xs text-slate-400 mt-2">Vendor: <span class="text-slate-700">{{ $inspection->part->supplier_name ?? '-' }}</span></div>
        </div>

        <div class="bg-white/90 border border-slate-200 p-5 rounded-2xl">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Specification Tolerance</span>
            <div class="text-xs text-slate-600">Nominal: <strong class="text-indigo-600 font-mono">{{ $inspection->part->nominal_dimension }} mm</strong></div>
            <div class="text-xs text-slate-600 mt-1">Limits: <strong class="text-emerald-600 font-mono">{{ $inspection->part->tolerance_min }} - {{ $inspection->part->tolerance_max }} mm</strong></div>
            <div class="text-xs text-slate-400 mt-2">AQL: <span class="text-slate-700">{{ $inspection->part->aql_level }} ({{ $inspection->part->sampling_level }})</span></div>
        </div>

        <div class="bg-white/90 border border-slate-200 p-5 rounded-2xl">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Authorization & Disposition</span>
            <div class="text-xs text-slate-600">Inspector: <strong class="text-slate-900">{{ $inspection->inspector->name ?? '-' }}</strong></div>
            <div class="text-xs text-slate-600 mt-1">Supervisor: <strong class="text-amber-700">{{ $inspection->supervisor->name ?? 'Not yet verified' }}</strong></div>
            <div class="text-xs text-slate-600 mt-2">
                Disposition: 
                @if($inspection->disposition)
                    <span class="font-bold uppercase text-slate-900">{{ $inspection->disposition }}</span>
                @else
                    <span class="text-slate-500">Awaiting Review</span>
                @endif
            </div>
        </div>
    </div>

    @if($inspection->disposition_notes)
        <div class="p-4 rounded-xl bg-white/95 border border-slate-200 text-xs">
            <div class="font-semibold text-amber-700 flex items-center gap-1.5 mb-1">
                <span class="material-symbols-outlined icon-sm">assignment</span>
                Quality Supervisor Notes:
            </div>
            <p class="text-slate-600 leading-relaxed">{{ $inspection->disposition_notes }}</p>
        </div>
    @endif

    @php
        $isVisualInspection = $inspection->measurements->contains(function ($m) {
            $tool = strtolower($m->measurement_tool ?? '');
            $param = strtolower($m->parameter_name ?? '');
            return str_contains($tool, 'visual') || str_contains($tool, 'document') || str_contains($param, 'verification') || str_contains($param, 'integrity') || str_contains($param, 'protection') || str_contains($param, 'compliance');
        });
    @endphp

    @if($isVisualInspection)
        <!-- Visual & Packaging Verification Checklist Report (For IC, Capacitors, Resistors, etc.) -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl space-y-0">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-slate-50 via-white to-slate-50">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-indigo-600 text-white shadow-sm">
                        <span class="material-symbols-outlined icon-md">visibility</span>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                            Visual &amp; Packaging Verification Checklist (Non-Dimensional Protocol)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Physical appearance verification, carrier tape &amp; reel/tray packaging integrity, part number marking, and lead coplanarity per JEDEC J-STD-020 / EIA standards.
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs self-start sm:self-auto">
                    <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                    Non-Dimensional IQC Protocol
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-16 text-center">Item #</th>
                            <th class="py-3 px-4 w-72">Testing Checkpoint Criteria</th>
                            <th class="py-3 px-4 w-60">Verification Method / Tool</th>
                            <th class="py-3 px-4 text-center w-36">Verification Result</th>
                            <th class="py-3 px-4">Inspector Verification Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($inspection->measurements as $m)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 text-center font-bold font-mono text-slate-400">
                                    #{{ str_pad($m->sample_number, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 text-xs">{{ $m->parameter_name }}</div>
                                    <span class="text-[10px] text-indigo-600 font-semibold">Standard Quality Checkpoint</span>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs">
                                        <span class="material-symbols-outlined text-[15px] text-slate-500">search</span>
                                        {{ $m->measurement_tool ?? 'Visual Inspection' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($m->is_pass)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span>PASS (COMPLIANT)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-300 shadow-2xs animate-pulse">
                                            <span class="material-symbols-outlined text-[14px]">cancel</span>DEFECT (NON-CONFORMING)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 font-medium">
                                    {{ $m->notes ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Dimensional Measurements Table -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 icon-md">straighten</span>
                    Actual Sample Measurement Data ({{ $inspection->measurements->count() }} Points)
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-white/90 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-16 text-center">Sample #</th>
                            <th class="py-3 px-4 w-44">Test Parameter</th>
                            <th class="py-3 px-4 w-44">Measurement Tool</th>
                            <th class="py-3 px-4">Nominal Drawing</th>
                            <th class="py-3 px-4">Actual Measurement</th>
                            <th class="py-3 px-4 text-center">Deviation</th>
                            <th class="py-3 px-4 text-center">Result</th>
                            <th class="py-3 px-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($inspection->measurements as $m)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center font-bold text-slate-400">#{{ str_pad($m->sample_number, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $m->parameter_name ?? 'Dimensi Kritis Blueprint' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-medium">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[15px] text-slate-500">construction</span>
                                        {{ $m->measurement_tool ?? 'Digital Caliper (CAL-001)' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-500">
                                    {{ number_format($m->nominal_value ?? $inspection->part->nominal_dimension, 3) }} mm
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ number_format($m->actual_value, 3) }} mm</td>
                                <td class="py-3 px-4 text-center font-mono {{ $m->is_pass ? 'text-slate-600' : 'text-rose-600 font-bold' }}">
                                    {{ $m->deviation >= 0 ? '+' : '' }}{{ number_format($m->deviation, 3) }} mm
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($m->is_pass)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">PASS</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">OUT OF SPEC</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-400">{{ $m->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Defect Findings List -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-rose-600 icon-md">report</span>
                Defect Findings Related to This Lot ({{ $inspection->defects->count() }})
            </h2>
        </div>

        <div class="p-5">
            @if($inspection->defects->count() > 0)
                <div class="space-y-3">
                    @foreach($inspection->defects as $def)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm">{{ $def->defect_type }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $def->severity === 'critical' ? 'bg-rose-100 text-rose-700 border border-rose-200' : 
                                           ($def->severity === 'major' ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30' : 'bg-amber-100 text-amber-700 border border-amber-200') }}">
                                        {{ $def->severity }}
                                    </span>
                                </div>
                                <p class="text-slate-400 mt-1">{{ $def->description ?? 'No additional notes.' }}</p>
                            </div>
                            <div class="flex items-center gap-4 shrink-0">
                                <span class="text-slate-600">Defect Count: <strong class="text-rose-600 font-mono">{{ $def->quantity }} pcs</strong></span>
                                <form action="{{ route('defects.destroy', $def->id) }}" method="POST" onsubmit="return confirm('Delete this defect record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-500 hover:text-rose-600 transition p-1">
                                        <span class="material-symbols-outlined icon-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-500 text-xs">
                    No defect findings for this lot. Sample condition is good.
                </div>
            @endif
        </div>
    </div>

    {{-- =========================================================
         PHOTO EVIDENCE SECTION
         - Inspector: upload pass + defect photos (only when status=draft)
         - SPV / anyone: view gallery
    ========================================================= --}}
    @php
        $allPhotos    = $inspection->photos ?? collect();
        $passPhotos   = $allPhotos->where('photo_type', 'pass');
        $defectPhotos = $allPhotos->where('photo_type', 'defect');
        $isDraft      = $inspection->status === 'draft';
        $isInspector  = auth()->user()->role === 'inspector';
        $canUpload    = $isDraft && $isInspector && $inspection->inspector_id === auth()->id();
    @endphp

    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl" id="photoSection">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 icon-md">photo_library</span>
                Photo Evidence ({{ $allPhotos->count() }} file{{ $allPhotos->count() === 1 ? '' : 's' }})
                @if($isDraft)
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold border border-amber-200">REQUIRED BEFORE SUBMIT</span>
                @endif
            </h2>
            @if(!$isDraft)
                <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                    <span class="material-symbols-outlined text-[15px]">lock</span>
                    Locked — Inspection Finalized
                </span>
            @endif
        </div>

        {{-- Validation errors from submit-to-spv --}}
        @if($errors->has('photos'))
            <div class="mx-5 mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">warning</span>
                {{ $errors->first('photos') }}
            </div>
        @endif

        <div class="p-5 space-y-6">

            {{-- ── PASS / OK PHOTOS ── --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide">Pass / OK Evidence ({{ $passPhotos->count() }})</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" id="passGrid">
                    @foreach($passPhotos as $photo)
                        <div class="relative group rounded-xl overflow-hidden border-2 border-emerald-200 bg-slate-50" data-photo-id="{{ $photo->id }}">
                            <a href="{{ Storage::disk('public')->url($photo->path) }}" target="_blank">
                                <img src="{{ Storage::disk('public')->url($photo->path) }}" alt="{{ $photo->label }}"
                                     class="w-full h-28 object-cover group-hover:brightness-90 transition">
                            </a>
                            <div class="p-1.5 text-[10px] text-slate-600 truncate">{{ $photo->label ?: 'Pass Photo' }}</div>
                            @if($canUpload)
                                <button onclick="deletePhoto({{ $photo->id }}, this)"
                                        class="absolute top-1 right-1 bg-rose-600 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition"
                                        title="Remove photo">
                                    <span class="material-symbols-outlined text-[14px]">close</span>
                                </button>
                            @endif
                        </div>
                    @endforeach

                    @if($canUpload)
                        {{-- Upload button for Pass photos --}}
                        <label for="uploadPass" class="flex flex-col items-center justify-center gap-2 h-28 rounded-xl border-2 border-dashed border-emerald-300 hover:border-emerald-500 hover:bg-emerald-50 cursor-pointer transition text-emerald-600">
                            <span class="material-symbols-outlined text-[28px]">add_photo_alternate</span>
                            <span class="text-[10px] font-semibold">Add Pass Photo</span>
                            <input id="uploadPass" type="file" class="hidden" accept="image/*" onchange="uploadPhoto(this, 'pass')">
                        </label>
                    @endif
                </div>
                @if($passPhotos->count() === 0 && !$canUpload)
                    <p class="text-xs text-slate-400 italic">No pass evidence photos uploaded.</p>
                @endif
            </div>

            {{-- ── DEFECT PHOTOS ── --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                    <span class="text-xs font-bold text-rose-700 uppercase tracking-wide">Defect Evidence ({{ $defectPhotos->count() }})</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" id="defectGrid">
                    @foreach($defectPhotos as $photo)
                        <div class="relative group rounded-xl overflow-hidden border-2 border-rose-200 bg-slate-50" data-photo-id="{{ $photo->id }}">
                            <a href="{{ Storage::disk('public')->url($photo->path) }}" target="_blank">
                                <img src="{{ Storage::disk('public')->url($photo->path) }}" alt="{{ $photo->label }}"
                                     class="w-full h-28 object-cover group-hover:brightness-90 transition">
                            </a>
                            <div class="p-1.5 text-[10px] text-slate-600 truncate">{{ $photo->label ?: 'Defect Photo' }}</div>
                            @if($canUpload)
                                <button onclick="deletePhoto({{ $photo->id }}, this)"
                                        class="absolute top-1 right-1 bg-rose-600 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition"
                                        title="Remove photo">
                                    <span class="material-symbols-outlined text-[14px]">close</span>
                                </button>
                            @endif
                        </div>
                    @endforeach

                    @if($canUpload)
                        <label for="uploadDefect" class="flex flex-col items-center justify-center gap-2 h-28 rounded-xl border-2 border-dashed border-rose-300 hover:border-rose-500 hover:bg-rose-50 cursor-pointer transition text-rose-600">
                            <span class="material-symbols-outlined text-[28px]">add_photo_alternate</span>
                            <span class="text-[10px] font-semibold">Add Defect Photo</span>
                            <input id="uploadDefect" type="file" class="hidden" accept="image/*" onchange="uploadPhoto(this, 'defect')">
                        </label>
                    @endif
                </div>
                @if($defectPhotos->count() === 0 && !$canUpload)
                    <p class="text-xs text-slate-400 italic">No defect evidence photos uploaded.</p>
                @endif
            </div>

            {{-- Upload progress indicator --}}
            <div id="uploadProgress" class="hidden">
                <div class="flex items-center gap-2 text-xs text-indigo-600">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Uploading photo...
                </div>
            </div>

        </div>
    </div>

    {{-- =========================================================
         SUBMIT TO SPV BUTTON (only visible if status=draft & canUpload)
    ========================================================= --}}
    @if($canUpload)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 shadow-xl" id="submitSpvPanel">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-amber-800 flex items-center gap-2">
                        <span class="material-symbols-outlined icon-md">send</span>
                        Submit to Supervisor for Disposition
                    </h3>
                    <p class="text-xs text-amber-700 mt-1">
                        Upload <strong>at least 1 pass photo</strong> and <strong>at least 1 defect photo</strong> before submitting. Once submitted, photos are locked.
                    </p>
                    <div class="flex items-center gap-4 mt-2 text-xs">
                        <span id="passCount" class="font-semibold {{ $passPhotos->count() > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            <span class="material-symbols-outlined text-[14px]">{{ $passPhotos->count() > 0 ? 'check_circle' : 'radio_button_unchecked' }}</span>
                            Pass photos: {{ $passPhotos->count() }}
                        </span>
                        <span id="defectCount" class="font-semibold {{ $defectPhotos->count() > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            <span class="material-symbols-outlined text-[14px]">{{ $defectPhotos->count() > 0 ? 'check_circle' : 'radio_button_unchecked' }}</span>
                            Defect photos: {{ $defectPhotos->count() }}
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('inspections.submit-to-spv', $inspection->id) }}" id="submitSpvForm">
                    @csrf
                    <button type="submit" id="submitSpvBtn"
                            class="px-5 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 shadow-lg transition
                                   {{ ($passPhotos->count() > 0 && $defectPhotos->count() > 0) 
                                       ? 'bg-amber-600 hover:bg-amber-500 text-white cursor-pointer' 
                                       : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}"
                            {{ ($passPhotos->count() === 0 || $defectPhotos->count() === 0) ? 'disabled' : '' }}
                            onclick="return confirm('Submit this inspection to Supervisor? Photos will be locked.')">
                        <span class="material-symbols-outlined icon-md">verified</span>
                        Submit to Supervisor
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>

@include('components.defect-modal')

@push('scripts')
<script>
const inspectionId = {{ $inspection->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function uploadPhoto(input, type) {
    if (!input.files || !input.files[0]) return;

    const label = prompt(`Enter a label for this ${type === 'pass' ? 'PASS ✅' : 'DEFECT 🔴'} photo (optional):`);

    const formData = new FormData();
    formData.append('photo', input.files[0]);
    formData.append('photo_type', type);
    formData.append('label', label || '');
    formData.append('_token', csrfToken);

    document.getElementById('uploadProgress').classList.remove('hidden');

    try {
        const res = await fetch(`/inspections/${inspectionId}/photos`, {
            method: 'POST',
            body: formData,
        });
        const data = await res.json();

        if (data.success) {
            addPhotoToGrid(data.photo);
            updateSubmitButton();
        } else {
            alert('Upload failed. Please try again.');
        }
    } catch (e) {
        alert('Upload error: ' + e.message);
    } finally {
        document.getElementById('uploadProgress').classList.add('hidden');
        input.value = '';
    }
}

function addPhotoToGrid(photo) {
    const grid = document.getElementById(photo.photo_type === 'pass' ? 'passGrid' : 'defectGrid');
    const borderColor = photo.photo_type === 'pass' ? 'border-emerald-200' : 'border-rose-200';
    const label = photo.label || (photo.photo_type === 'pass' ? 'Pass Photo' : 'Defect Photo');

    const el = document.createElement('div');
    el.className = `relative group rounded-xl overflow-hidden border-2 ${borderColor} bg-slate-50`;
    el.dataset.photoId = photo.id;
    el.innerHTML = `
        <a href="${photo.url}" target="_blank">
            <img src="${photo.url}" alt="${label}" class="w-full h-28 object-cover group-hover:brightness-90 transition">
        </a>
        <div class="p-1.5 text-[10px] text-slate-600 truncate">${label}</div>
        <button onclick="deletePhoto(${photo.id}, this)"
                class="absolute top-1 right-1 bg-rose-600 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition"
                title="Remove photo">
            <span class="material-symbols-outlined text-[14px]">close</span>
        </button>
    `;

    // Insert before the upload label button
    const uploadLabel = grid.querySelector('label');
    if (uploadLabel) {
        grid.insertBefore(el, uploadLabel);
    } else {
        grid.appendChild(el);
    }
}

async function deletePhoto(photoId, btn) {
    if (!confirm('Remove this photo?')) return;

    const res = await fetch(`/inspection-photos/${photoId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    });
    const data = await res.json();
    if (data.success) {
        const card = btn.closest('[data-photo-id]');
        card.remove();
        updateSubmitButton();
    } else {
        alert('Delete failed.');
    }
}

function updateSubmitButton() {
    const passCount   = document.getElementById('passGrid')?.querySelectorAll('[data-photo-id]').length ?? 0;
    const defectCount = document.getElementById('defectGrid')?.querySelectorAll('[data-photo-id]').length ?? 0;
    const submitBtn   = document.getElementById('submitSpvBtn');
    const passLabel   = document.getElementById('passCount');
    const defectLabel = document.getElementById('defectCount');

    if (submitBtn) {
        const ready = passCount >= 1 && defectCount >= 1;
        submitBtn.disabled = !ready;
        submitBtn.className = `px-5 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 shadow-lg transition ${
            ready ? 'bg-amber-600 hover:bg-amber-500 text-white cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'
        }`;
    }
    if (passLabel) {
        passLabel.className = `font-semibold ${passCount > 0 ? 'text-emerald-600' : 'text-rose-600'}`;
        passLabel.innerHTML = `<span class="material-symbols-outlined text-[14px]">${passCount > 0 ? 'check_circle' : 'radio_button_unchecked'}</span> Pass photos: ${passCount}`;
    }
    if (defectLabel) {
        defectLabel.className = `font-semibold ${defectCount > 0 ? 'text-emerald-600' : 'text-rose-600'}`;
        defectLabel.innerHTML = `<span class="material-symbols-outlined text-[14px]">${defectCount > 0 ? 'check_circle' : 'radio_button_unchecked'}</span> Defect photos: ${defectCount}`;
    }
}
</script>
@endpush
@endsection
