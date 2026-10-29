@extends('layouts.app')

@section('title', 'Inspection & Dimension Measurement Form - Logbook Digital IQC')

@php
    $isGrnLocked = $isGrnLocked ?? (!empty(request('incoming_id')) || !empty(request('grn')) || request()->boolean('locked') || old('locked') === '1');

    $partsPayload = $parts->map(function($p) {
        return [
            'id' => $p->id,
            'part_number' => $p->part_number,
            'part_name' => $p->part_name,
            'supplier_name' => $p->supplier_name,
            'aql_level' => $p->aql_level,
            'sampling_level' => $p->sampling_level,
            'nominal_dimension' => (float)$p->nominal_dimension,
            'tolerance_min' => (float)$p->tolerance_min,
            'tolerance_max' => (float)$p->tolerance_max,
            'unit' => $p->unit ?? 'mm',
            'parameters' => $p->parameters->map(function($param) {
                return [
                    'id' => $param->id,
                    'parameter_name' => $param->parameter_name,
                    'nominal_dimension' => (float)$param->nominal_dimension,
                    'tolerance_min' => (float)$param->tolerance_min,
                    'tolerance_max' => (float)$param->tolerance_max,
                    'unit' => $param->unit ?? 'mm',
                    'is_critical' => (bool)$param->is_critical,
                    'instrument' => $param->instrument ? [
                        'id' => $param->instrument->id,
                        'instrument_code' => $param->instrument->instrument_code,
                        'name' => $param->instrument->name,
                        'brand_model' => $param->instrument->brand_model,
                        'measuring_range' => $param->instrument->measuring_range,
                    ] : null,
                ];
            })->values(),
        ];
    })->keyBy('id');
@endphp

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <span class="material-symbols-outlined icon-xl">straighten</span>
                </span>
                <h1 class="text-xl font-bold text-slate-900">Dimension Measurement & IQC Sampling Form</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Input actual measurement results per sample. Deviation and Pass/Fail status are calculated automatically in real-time.
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($isGrnLocked)
                <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs border border-slate-300 transition flex items-center gap-1.5 font-medium">
                    <span class="material-symbols-outlined icon-sm">qr_code_scanner</span>
                    Scan Different GRN
                </a>
            @endif
            <a href="{{ route('inspections.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 text-xs border border-slate-300 transition">
                Back to History
            </a>
        </div>
    </div>

    <!-- Inspection Form -->
    <form action="{{ route('inspections.store') }}" method="POST" id="inspectionForm" class="space-y-6">
        @csrf
        <input type="hidden" name="incoming_id" value="{{ old('incoming_id', request('incoming_id')) }}">
        <input type="hidden" name="locked" value="{{ $isGrnLocked ? '1' : '0' }}">
        <input type="hidden" name="parameter_id" id="hidden_parameter_id" value="">
        <input type="hidden" name="parameter_name" id="hidden_parameter_name" value="">
        <input type="hidden" name="measurement_tool" id="hidden_measurement_tool" value="">

        <!-- Section 1: Data Lot & Spesifikasi Part -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-6 shadow-xl space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-3">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 icon-md">info</span>
                    Lot Information & Component Specifications
                </h2>
                @if($isGrnLocked)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-sm w-fit">
                        <span class="material-symbols-outlined text-[14px]">lock</span>
                        Locked &amp; Synced with GRN
                    </span>
                @endif
            </div>

            @if($isGrnLocked)
                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 text-amber-900 text-xs">
                    <span class="material-symbols-outlined text-amber-600 text-lg flex-shrink-0">lock</span>
                    <div class="flex-1">
                        <span class="font-bold">GRN Direct Mode:</span>
                        Part specification and lot details are locked to match incoming material 
                        <span class="font-mono font-bold text-indigo-700 bg-white/90 px-1.5 py-0.5 rounded border border-amber-200 ml-1">{{ request('grn', $incomingMaterial->grn_number ?? 'GRN') }}</span>.
                        Fields cannot be modified to ensure audit compliance.
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Part Selector -->
                <div>
                    <label for="part_id" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Select Part Number *</span>
                        @if($isGrnLocked) <span class="text-[10px] text-amber-600 font-bold lowercase flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">lock</span>locked</span> @endif
                    </label>
                    @if($isGrnLocked)
                        <input type="hidden" name="part_id" value="{{ old('part_id', request('part_id', $selectedPart->id ?? '')) }}">
                        <div class="relative">
                            <select id="part_id" disabled
                                class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-700 font-semibold cursor-not-allowed pr-9 appearance-none">
                                @foreach($parts as $p)
                                    <option value="{{ $p->id }}" 
                                        data-name="{{ $p->part_name }}"
                                        data-supplier="{{ $p->supplier_name }}"
                                        data-nominal="{{ $p->nominal_dimension }}"
                                        data-min="{{ $p->tolerance_min }}"
                                        data-max="{{ $p->tolerance_max }}"
                                        data-unit="{{ $p->unit }}"
                                        data-aql="{{ $p->aql_level }}"
                                        {{ (old('part_id', request('part_id')) == $p->id || (isset($selectedPart) && $selectedPart->id == $p->id)) ? 'selected' : '' }}>
                                        {{ $p->part_number }} - {{ $p->part_name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[16px] pointer-events-none">lock</span>
                        </div>
                    @else
                        <select id="part_id" name="part_id" required onchange="onPartSelected(this)"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                            <option value="">-- Select Component Part --</option>
                            @foreach($parts as $p)
                                <option value="{{ $p->id }}" 
                                    data-name="{{ $p->part_name }}"
                                    data-supplier="{{ $p->supplier_name }}"
                                    data-nominal="{{ $p->nominal_dimension }}"
                                    data-min="{{ $p->tolerance_min }}"
                                    data-max="{{ $p->tolerance_max }}"
                                    data-unit="{{ $p->unit }}"
                                    data-aql="{{ $p->aql_level }}"
                                    {{ (old('part_id', request('part_id')) == $p->id || (isset($selectedPart) && $selectedPart->id == $p->id)) ? 'selected' : '' }}>
                                    {{ $p->part_number }} - {{ $p->part_name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- PO Number -->
                <div>
                    <label for="po_number" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Purchase Order / GRN Number *</span>
                        @if($isGrnLocked) <span class="text-[10px] text-amber-600 font-bold lowercase flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">lock</span>locked</span> @endif
                    </label>
                    <div class="relative">
                        <input type="text" id="po_number" name="po_number" value="{{ old('po_number', request('grn', request('po', 'PO-' . date('Ymd') . '-01'))) }}" required
                            {{ $isGrnLocked ? 'readonly' : '' }}
                            class="w-full {{ $isGrnLocked ? 'bg-slate-100 text-slate-700 cursor-not-allowed select-none pr-9' : 'bg-slate-50 text-slate-900 focus:outline-none focus:border-indigo-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono transition"
                            placeholder="e.g.: GRN-2026-8901">
                        @if($isGrnLocked)
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[16px] pointer-events-none">lock</span>
                        @endif
                    </div>
                </div>

                <!-- Lot Number -->
                <div>
                    <label for="lot_number" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Vendor Production Lot Number *</span>
                        @if($isGrnLocked) <span class="text-[10px] text-amber-600 font-bold lowercase flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">lock</span>locked</span> @endif
                    </label>
                    <div class="relative">
                        <input type="text" id="lot_number" name="lot_number" value="{{ old('lot_number', request('lot', 'LOT-' . strtoupper(substr(md5(time()), 0, 6)))) }}" required
                            {{ $isGrnLocked ? 'readonly' : '' }}
                            class="w-full {{ $isGrnLocked ? 'bg-slate-100 text-slate-700 cursor-not-allowed select-none pr-9' : 'bg-slate-50 text-slate-900 focus:outline-none focus:border-indigo-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono transition"
                            placeholder="e.g.: LOT-BRK-2409-X">
                        @if($isGrnLocked)
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[16px] pointer-events-none">lock</span>
                        @endif
                    </div>
                </div>

                <!-- Lot Quantity -->
                <div>
                    <label for="lot_quantity" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Incoming Lot Quantity (Pcs) *</span>
                        @if($isGrnLocked) <span class="text-[10px] text-amber-600 font-bold lowercase flex items-center gap-0.5"><span class="material-symbols-outlined text-[12px]">lock</span>locked</span> @endif
                    </label>
                    <div class="relative">
                        <input type="number" id="lot_quantity" name="lot_quantity" value="{{ old('lot_quantity', request('qty', 1000)) }}" min="1" required
                            {{ $isGrnLocked ? 'readonly' : '' }}
                            class="w-full {{ $isGrnLocked ? 'bg-slate-100 text-slate-700 cursor-not-allowed select-none pr-9' : 'bg-slate-50 text-slate-900 focus:outline-none focus:border-indigo-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs transition">
                        @if($isGrnLocked)
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[16px] pointer-events-none">lock</span>
                        @endif
                    </div>
                </div>

                <!-- Sample Size -->
                <div>
                    <label for="sample_size" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                        Inspection Sample Size (AQL) *
                    </label>
                    <input type="number" id="sample_size" name="sample_size" value="5" min="1" max="500" required onchange="adjustSampleRows(this.value)"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                </div>

                <!-- Inspection Date -->
                <div>
                    <label for="inspection_date" class="block font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                        Inspection Date *
                    </label>
                    <input type="date" id="inspection_date" name="inspection_date" value="{{ date('Y-m-d') }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- Part Specification Spec Sheet Preview (Dynamically updated per part) -->
            <div id="specCard" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3 text-xs">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/60 pb-2.5">
                    <div class="flex items-center gap-3">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Supplier:</span>
                            <span id="specSupplier" class="text-slate-900 font-bold">-</span>
                        </div>
                        <div class="h-6 w-px bg-slate-200"></div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Sampling Standard &amp; AQL:</span>
                            <span id="specAQL" class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold text-[11px]">-</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Assigned Measurement Tools:</span>
                        <span id="specToolStatus" class="text-slate-800 font-semibold text-xs">-</span>
                    </div>
                    @if($isGrnLocked)
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-200/60 text-slate-600 font-semibold text-[11px]">
                            <span class="material-symbols-outlined text-[14px] text-amber-600">lock</span>
                            <span>Drawing spec locked</span>
                        </div>
                    @endif
                </div>

                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-1.5">Inspected Dimensions / Test Parameters for this Part:</span>
                    <div id="specParamsList" class="flex flex-wrap gap-2">
                        <!-- Populated dynamically by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Inspection Method Selection & Quality Verification -->
        <!-- Mode Switcher: Visual & Packaging Inspection (Non-Dimensional) vs Physical Precision Measurement -->
        <div class="bg-white/95 border border-slate-200/90 rounded-2xl p-5 shadow-xl shadow-slate-200/40 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/70 pb-4">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 rounded-xl bg-indigo-50 text-indigo-700 shrink-0">
                        <span class="material-symbols-outlined text-[24px]">fact_check</span>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-extrabold text-slate-900 tracking-tight">IQC Inspection Method &amp; Testing Criteria</h2>
                            <span id="autoDetectedBadge" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Auto-Detected
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5" id="methodDescription">
                            Electronic components (ICs, Capacitors, Resistors, Diodes) undergo visual inspection, package integrity, and marking verification per JEDEC/EIA standards without caliper dimensional measurement.
                        </p>
                    </div>
                </div>

                <!-- Mode Toggle Tabs -->
                <div class="inline-flex p-1 rounded-xl bg-slate-100 border border-slate-200 shrink-0 self-start md:self-auto">
                    <button type="button" id="tabModeVisual" onclick="setInspectionMode('visual')"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-white text-indigo-700 shadow-2xs cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                        <span>Visual &amp; Packaging Inspection (Non-Dimensional)</span>
                    </button>
                    <button type="button" id="tabModeDimensional" onclick="setInspectionMode('dimensional')"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 text-slate-600 hover:text-slate-900 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">straighten</span>
                        <span>Physical Dimensional Measurement (Caliper/Tool)</span>
                    </button>
                </div>
                <input type="hidden" name="inspection_type" id="inspection_type" value="visual">
            </div>

            <!-- PANEL A: VISUAL & PACKAGING INSPECTION (IC, CAPACITOR, RESISTOR COMPONENTS) -->
            <div id="visualInspectionSection" class="space-y-4">
                <div class="p-4 rounded-xl bg-indigo-50/60 border border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1.5 rounded-lg bg-indigo-600 text-white shrink-0">
                            <span class="material-symbols-outlined text-[16px]">verified</span>
                        </span>
                        <div>
                            <span class="font-extrabold text-indigo-950 block">Industrial Non-Dimensional Inspection Protocol (JEDEC / EIA Standard)</span>
                            <span class="text-indigo-700/80 text-[11px]">Physical lot verification, tape/reel/tray packaging integrity, part number marking, and lead/pin condition without dimensional measurement.</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-indigo-200 font-mono font-bold text-indigo-800 text-[11px] shrink-0">
                        <span class="material-symbols-outlined text-[14px] text-indigo-600">inventory_2</span>
                        4 Checkpoints Verified per Lot
                    </span>
                </div>

                <!-- Checkpoints Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Checkpoint 1: Part Number & Marking -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-2xs hover:border-slate-300 transition space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 rounded-lg bg-blue-50 text-blue-600 shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-xs">1. Part Number &amp; Marking Verification</h4>
                                    <span class="text-[10px] text-slate-400">Method: Visual / 10x Loupe Magnifier</span>
                                </div>
                            </div>
                            <!-- Status Toggle -->
                            <div class="inline-flex p-0.5 rounded-lg bg-slate-100 border border-slate-200 text-[11px]">
                                <button type="button" onclick="setVisualCheckpointStatus('marking', true)" id="btnMarkingPass"
                                    class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 bg-emerald-600 text-white shadow-2xs cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                    <span>PASS</span>
                                </button>
                                <button type="button" onclick="setVisualCheckpointStatus('marking', false)" id="btnMarkingFail"
                                    class="px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-rose-600 transition flex items-center gap-1 cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">cancel</span>
                                    <span>DEFECT</span>
                                </button>
                            </div>
                            <input type="hidden" name="visual_marking_pass" id="visual_marking_pass" value="1">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Part number on component body/reel label, vendor lot code, and manufacturer logo are legible and identical to Purchase Order &amp; Datasheet specifications.
                        </p>
                        <input type="text" name="visual_marking_notes" placeholder="Remark: Reel label &amp; body marking fully comply with PO/Data Sheet"
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>

                    <!-- Checkpoint 2: Packaging & Moisture/ESD Protection -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-2xs hover:border-slate-300 transition space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 rounded-lg bg-amber-50 text-amber-700 shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-xs">2. Packaging &amp; Moisture/ESD Protection (MSL)</h4>
                                    <span class="text-[10px] text-slate-400">Method: Visual &amp; Vacuum Seal Inspection</span>
                                </div>
                            </div>
                            <!-- Status Toggle -->
                            <div class="inline-flex p-0.5 rounded-lg bg-slate-100 border border-slate-200 text-[11px]">
                                <button type="button" onclick="setVisualCheckpointStatus('packaging', true)" id="btnPackagingPass"
                                    class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 bg-emerald-600 text-white shadow-2xs cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                    <span>PASS</span>
                                </button>
                                <button type="button" onclick="setVisualCheckpointStatus('packaging', false)" id="btnPackagingFail"
                                    class="px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-rose-600 transition flex items-center gap-1 cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">cancel</span>
                                    <span>DEFECT</span>
                                </button>
                            </div>
                            <input type="hidden" name="visual_packaging_pass" id="visual_packaging_pass" value="1">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Carrier tape/reel/tray intact, Moisture Barrier Bag (MBB) vacuum seal hermetic, active desiccant present, and Humidity Indicator Card (HIC) &lt; 10% compliant.
                        </p>
                        <input type="text" name="visual_packaging_notes" placeholder="Remark: Tape/Reel intact, MBB vacuum sealed, HIC < 10% compliant"
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>

                    <!-- Checkpoint 3: Physical Appearance & Lead/Pin Integrity -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-2xs hover:border-slate-300 transition space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600 shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">straighten</span>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-xs">3. Physical Appearance &amp; Lead/Pin Integrity</h4>
                                    <span class="text-[10px] text-slate-400">Method: Visual / Stereo Microscope (40x)</span>
                                </div>
                            </div>
                            <!-- Status Toggle -->
                            <div class="inline-flex p-0.5 rounded-lg bg-slate-100 border border-slate-200 text-[11px]">
                                <button type="button" onclick="setVisualCheckpointStatus('pins', true)" id="btnPinsPass"
                                    class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 bg-emerald-600 text-white shadow-2xs cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                    <span>PASS</span>
                                </button>
                                <button type="button" onclick="setVisualCheckpointStatus('pins', false)" id="btnPinsFail"
                                    class="px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-rose-600 transition flex items-center gap-1 cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">cancel</span>
                                    <span>DEFECT</span>
                                </button>
                            </div>
                            <input type="hidden" name="visual_pins_pass" id="visual_pins_pass" value="1">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Leads/pins aligned (coplanarity maintained), free of bend/skew/bridging, zero oxidation/corrosion. Molded package free of cracks or chipping.
                        </p>
                        <input type="text" name="visual_pins_notes" placeholder="Remark: Leads coplanar and oxidation-free, package intact without physical defects"
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>

                    <!-- Checkpoint 4: Manufacturer CoC & RoHS Compliance -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-2xs hover:border-slate-300 transition space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 rounded-lg bg-purple-50 text-purple-600 shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">verified_user</span>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-xs">4. Manufacturer CoC &amp; RoHS Compliance</h4>
                                    <span class="text-[10px] text-slate-400">Method: Document Verification</span>
                                </div>
                            </div>
                            <!-- Status Toggle -->
                            <div class="inline-flex p-0.5 rounded-lg bg-slate-100 border border-slate-200 text-[11px]">
                                <button type="button" onclick="setVisualCheckpointStatus('coc', true)" id="btnCocPass"
                                    class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 bg-emerald-600 text-white shadow-2xs cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                    <span>PASS</span>
                                </button>
                                <button type="button" onclick="setVisualCheckpointStatus('coc', false)" id="btnCocFail"
                                    class="px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-rose-600 transition flex items-center gap-1 cursor-pointer">
                                    <span class="material-symbols-outlined text-[13px]">cancel</span>
                                    <span>DEFECT</span>
                                </button>
                            </div>
                            <input type="hidden" name="visual_coc_pass" id="visual_coc_pass" value="1">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Manufacturer Certificate of Conformance (CoC), RoHS/Lead-Free compliance certificates, and valid manufacturer trace lot numbers verified.
                        </p>
                        <input type="text" name="visual_coc_notes" placeholder="Remark: Manufacturer CoC &amp; RoHS compliance certificates verified valid"
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- PANEL B: PHYSICAL SAMPLE MEASUREMENT TABLE (DIMENSIONAL PRECISION FOR MECHANICAL PARTS) -->
            <div id="dimensionalInspectionSection" class="hidden space-y-4">
                <!-- Dimensional Metrology Toolbar -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 rounded-xl bg-slate-50/80 border border-slate-200">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-indigo-600 text-[18px]">straighten</span>
                            Physical Precision Dimensional Measurement Table
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Multi-parameter precision dimensional inspection verifying blueprint tolerances per ISO 2859-1 / MIL-STD-105E &amp; Wireframe 3.11.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Multi-Parameter Badge -->
                        <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-3 py-1.5 shadow-2xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600 text-[18px]">rule</span>
                            <div>
                                <span class="text-[9px] text-indigo-400 uppercase font-extrabold block leading-tight">Inspection Matrix</span>
                                <span id="paramMatrixBadge" class="text-xs font-bold text-indigo-800">Multi-Parameter per Sample</span>
                            </div>
                        </div>

                        <!-- Clean Measurement Tool Selection Dropdown -->
                        <div class="bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-2xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-slate-600 text-[18px]">construction</span>
                            <div>
                                <label for="globalToolSelect" class="text-[9px] text-slate-400 uppercase font-extrabold block leading-tight">Default Measurement Tool</label>
                                <select id="globalToolSelect" onchange="applyGlobalTool(this.value)"
                                    class="text-xs font-bold text-slate-800 bg-transparent border-0 p-0 focus:ring-0 focus:outline-none cursor-pointer">
                                    @if(isset($instruments) && $instruments->count() > 0)
                                        @foreach($instruments as $insItem)
                                            <option value="{{ $insItem->name }} ({{ $insItem->instrument_code }})"
                                                data-code="{{ $insItem->instrument_code }}"
                                                data-range="{{ $insItem->measuring_range }}">
                                                {{ $insItem->name }} ({{ $insItem->instrument_code }})
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="Digital Caliper 150mm (CAL-001)">Digital Caliper 150mm (CAL-001)</option>
                                        <option value="Digital Outside Micrometer (MIC-001)">Digital Outside Micrometer (MIC-001)</option>
                                        <option value="Digital Height Gauge (HG-001)">Digital Height Gauge (HG-001)</option>
                                        <option value="Precision Pin Gauge Set (PIN-001)">Precision Pin Gauge Set (PIN-001)</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Add Sample Button -->
                        <button type="button" onclick="addSample()" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-indigo-600/20 transition cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Add Sample</span>
                        </button>

                        <button type="button" onclick="resetToAql()" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-600 border border-slate-300 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer shadow-2xs" title="Reset sample table to original AQL sample size">
                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                            <span>Reset AQL</span>
                        </button>
                    </div>
                </div>

                <!-- Dedicated Multi-Column Measurement Table -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs min-w-[980px]" id="measurementTable">
                        <thead class="bg-slate-50/90 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 w-56">Test Parameter / Dimension</th>
                                <th class="py-3.5 px-4 w-56">Measurement Tool</th>
                                <th class="py-3.5 px-4 w-44">Nominal Spec</th>
                                <th class="py-3.5 px-4 w-48">Actual Measurement (mm) *</th>
                                <th class="py-3.5 px-4 w-32 text-center">Deviation</th>
                                <th class="py-3.5 px-4 w-36 text-center">Pass/Fail Status</th>
                                <th class="py-3.5 px-4 w-16 text-center">Unit</th>
                            </tr>
                        </thead>
                        <tbody id="measurementBody" class="divide-y divide-slate-100 bg-white">
                            <!-- Populated dynamically by JS with multi-parameter per sample grouping -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SHARED REAL-TIME VERIFICATION BAR & DYNAMIC SUBMIT BUTTON -->
            <div class="p-5 bg-slate-50/90 border-t border-slate-200/90 rounded-xl space-y-4">
                <!-- Status Metrics & Overall Lot Verdict -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <!-- Total Physical Samples Pill -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs font-medium text-slate-600">
                            <span class="material-symbols-outlined text-slate-400 text-[16px]">tag</span>
                            <span>Inspected Units: <strong id="countSamples" class="text-slate-900 font-mono font-bold">5</strong> pcs</span>
                        </div>

                        <!-- Total Measurement / Criteria Points Pill -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs font-medium text-slate-600">
                            <span class="material-symbols-outlined text-slate-400 text-[16px]">format_list_numbered</span>
                            <span>Criteria Points: <strong id="countTotal" class="text-slate-900 font-mono font-bold">4</strong></span>
                        </div>

                        <!-- Passed Samples Pill -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-2xs font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>Passed: <strong id="countPass" class="text-emerald-700 font-mono font-bold text-sm">4</strong></span>
                        </div>

                        <!-- Out of Spec Samples Pill -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-2xs font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span>Defect / Out of Spec: <strong id="countFail" class="text-rose-700 font-mono font-bold text-sm">0</strong></span>
                        </div>
                    </div>

                    <!-- Lot Compliance Verdict Indicator Banner -->
                    <div id="lotVerdictBanner" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                        <span id="lotVerdictText">ALL CRITERIA COMPLIANT - PASS (Ready for Submission)</span>
                    </div>
                </div>

                <!-- Action Controls: Reset & Conditional Submit Button -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-200/80">
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="material-symbols-outlined text-[18px] text-indigo-600">policy</span>
                        <span>IQC Standard Operating Procedure (ISO 9001:2015 / IATF 16949 / MIL-STD-105E)</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Dynamic Conditional Submit Button: Green 'Save & Complete' vs Rose 'Submit Defect Report to Supervisor' -->
                        <button type="submit" id="submitBtn" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined icon-sm" id="submitBtnIcon">task_alt</span>
                            <span id="submitBtnText">Save &amp; Complete Inspection Log</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

<script>
    // Master parts data loaded from database
    const partsData = @json($partsPayload);

    // Master metrology instruments list for clean tool selection (Without calibration badges/labels)
    const instrumentsList = @json($instrumentsList);

    let currentPartId = null;
    let currentSampleCount = 5;
    let activeParameters = [];
    let defaultGlobalTool = instrumentsList.length > 0 ? instrumentsList[0].label : 'Digital Caliper 150mm (CAL-001)';
    let currentInspectionMode = 'visual';

    const visualCheckpointsState = {
        marking: true,
        packaging: true,
        pins: true,
        coc: true
    };

    function isElectronicComponent(part) {
        if (!part) return false;
        const num = (part.part_number || '').toUpperCase();
        const name = (part.part_name || '').toUpperCase();
        return num.startsWith('IC-') || num.startsWith('EE-MCU') || num.startsWith('CAP-') || num.startsWith('RES-') || num.startsWith('DIO-')
            || name.includes('IC') || name.includes('MICROCONTROLLER') || name.includes('CAPACITOR') || name.includes('RESISTOR') || name.includes('DIODE') || name.includes('CHIP');
    }

    function setInspectionMode(mode, isAuto = false) {
        currentInspectionMode = mode;
        const inputType = document.getElementById('inspection_type');
        if (inputType) inputType.value = mode;

        const visualSection = document.getElementById('visualInspectionSection');
        const dimSection = document.getElementById('dimensionalInspectionSection');
        const tabVisual = document.getElementById('tabModeVisual');
        const tabDim = document.getElementById('tabModeDimensional');
        const autoBadge = document.getElementById('autoDetectedBadge');
        const descElem = document.getElementById('methodDescription');

        if (mode === 'visual') {
            if (visualSection) visualSection.classList.remove('hidden');
            if (dimSection) dimSection.classList.add('hidden');

            if (tabVisual) {
                tabVisual.className = "px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-white text-indigo-700 shadow-2xs cursor-pointer";
            }
            if (tabDim) {
                tabDim.className = "px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 text-slate-600 hover:text-slate-900 cursor-pointer";
            }
            if (descElem) {
                descElem.innerText = "Electronic components (ICs, Capacitors, Resistors, Diodes) undergo visual inspection, package integrity, and marking verification per JEDEC/EIA standards without caliper dimensional measurement.";
            }
            if (autoBadge) {
                autoBadge.innerText = isAuto ? "Auto-Detected: Electronic Component (Non-Dimensional)" : "Active Mode: Visual & Packaging Inspection";
                autoBadge.className = "px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200";
            }

            // Remove required attribute from dimensional inputs so HTML validation won't block submit
            document.querySelectorAll('.actual-input').forEach(inp => {
                inp.removeAttribute('required');
            });

            recalcVisualSummary();
        } else {
            if (visualSection) visualSection.classList.add('hidden');
            if (dimSection) dimSection.classList.remove('hidden');

            if (tabDim) {
                tabDim.className = "px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-white text-indigo-700 shadow-2xs cursor-pointer";
            }
            if (tabVisual) {
                tabVisual.className = "px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 text-slate-600 hover:text-slate-900 cursor-pointer";
            }
            if (descElem) {
                descElem.innerText = "Precision multi-parameter dimensional measurement using metrology instruments (Digital Caliper / Micrometer / Gauge).";
            }
            if (autoBadge) {
                autoBadge.innerText = isAuto ? "Auto-Detected: Mechanical Component (Dimensional)" : "Active Mode: Dimensional Measurement";
                autoBadge.className = "px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200";
            }

            // Restore required attribute on actual measurement inputs
            document.querySelectorAll('.actual-input').forEach(inp => {
                inp.setAttribute('required', 'required');
            });

            recalcSummary();
        }
    }

    function setVisualCheckpointStatus(key, isPass) {
        visualCheckpointsState[key] = isPass;
        const hiddenInp = document.getElementById(`visual_${key}_pass`);
        if (hiddenInp) hiddenInp.value = isPass ? '1' : '0';

        const passBtn = document.getElementById(`btn${key.charAt(0).toUpperCase() + key.slice(1)}Pass`);
        const failBtn = document.getElementById(`btn${key.charAt(0).toUpperCase() + key.slice(1)}Fail`);

        if (passBtn && failBtn) {
            if (isPass) {
                passBtn.className = "px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 bg-emerald-600 text-white shadow-2xs cursor-pointer";
                failBtn.className = "px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-rose-600 transition flex items-center gap-1 cursor-pointer";
            } else {
                passBtn.className = "px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-emerald-600 transition flex items-center gap-1 cursor-pointer";
                failBtn.className = "px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 bg-rose-600 text-white shadow-2xs cursor-pointer animate-pulse";
            }
        }

        recalcVisualSummary();
    }

    function recalcVisualSummary() {
        if (currentInspectionMode !== 'visual') return;

        let pass = 0;
        let fail = 0;
        const total = 4;

        Object.keys(visualCheckpointsState).forEach(k => {
            if (visualCheckpointsState[k]) {
                pass++;
            } else {
                fail++;
            }
        });

        const countSamplesElem = document.getElementById('countSamples');
        if (countSamplesElem) countSamplesElem.innerText = currentSampleCount;

        const countTotalElem = document.getElementById('countTotal');
        if (countTotalElem) countTotalElem.innerText = total;

        const countPassElem = document.getElementById('countPass');
        if (countPassElem) countPassElem.innerText = pass;

        const countFailElem = document.getElementById('countFail');
        if (countFailElem) countFailElem.innerText = fail;

        // Live Verdict Banner Update
        const verdictBanner = document.getElementById('lotVerdictBanner');
        const verdictText = document.getElementById('lotVerdictText');
        if (verdictBanner && verdictText) {
            if (fail === 0) {
                verdictBanner.className = "inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs";
                verdictBanner.innerHTML = `
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    <span id="lotVerdictText">ALL VISUAL CRITERIA COMPLIANT - PASS (Ready for Submission)</span>
                `;
            } else {
                verdictBanner.className = "inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs animate-pulse";
                verdictBanner.innerHTML = `
                    <span class="material-symbols-outlined text-[18px]">warning</span>
                    <span id="lotVerdictText">${fail} VISUAL CRITERIA DEFECT(S) FOUND - SUPERVISOR DISPOSITION REQUIRED</span>
                `;
            }
        }

        // Conditional Submit Button Logic
        const submitBtn = document.getElementById('submitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitBtnIcon = document.getElementById('submitBtnIcon');

        if (submitBtn && submitBtnText && submitBtnIcon) {
            if (fail === 0) {
                submitBtn.className = "w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer";
                submitBtnIcon.innerText = "task_alt";
                submitBtnText.innerText = "Save & Complete Inspection Log";
            } else {
                submitBtn.className = "w-full sm:w-auto px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs shadow-lg shadow-rose-600/30 transition flex items-center justify-center gap-2 cursor-pointer animate-pulse";
                submitBtnIcon.innerText = "report_problem";
                submitBtnText.innerText = "Submit Defect Report to Supervisor";
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const partSelect = document.getElementById('part_id');
        if (partSelect && partSelect.value) {
            onPartSelected(partSelect);
        } else if (partSelect && partSelect.options.length > 1) {
            for (let i = 0; i < partSelect.options.length; i++) {
                if (partSelect.options[i].value) {
                    partSelect.selectedIndex = i;
                    onPartSelected(partSelect);
                    break;
                }
            }
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function onPartSelected(select) {
        const partId = select.value;
        if (!partId) return;

        currentPartId = partId;
        const part = partsData[partId];
        if (!part) return;

        // POKA-YOKE MISTAKE-PROOFING: Reject Wire Harness / Automotive selection
        const pNum = (part.part_number || '').toUpperCase();
        const pName = (part.part_name || '').toUpperCase();
        if (pNum.startsWith('WH-') || pNum.startsWith('AUTO-') || pName.includes('HARNESS') || pName.includes('AUTOMOTIVE')) {
            window.location.href = "{{ route('poka-yoke.denied') }}?part_number=" + encodeURIComponent(part.part_number) + "&part_name=" + encodeURIComponent(part.part_name);
            return;
        }

        // Auto-detect inspection mode: Electronic components (IC, Capacitor, Resistor) -> Visual; Mechanical -> Dimensional
        const isElectronic = isElectronicComponent(part);
        if (isElectronic) {
            setInspectionMode('visual', true);
        } else {
            setInspectionMode('dimensional', true);
        }

        // 1. Update Part Information in Spec Sheet Card
        const supplierElem = document.getElementById('specSupplier');
        if (supplierElem) supplierElem.innerText = part.supplier_name || '-';

        const aqlElem = document.getElementById('specAQL');
        if (aqlElem) aqlElem.innerText = `AQL ${part.aql_level || '1.0'} (${part.sampling_level || 'General II'})`;

        // 2. Load Part's multi-parameters dynamically from database
        activeParameters = (part.parameters && part.parameters.length > 0) ? part.parameters : [
            {
                id: '',
                parameter_name: 'Main Casing Width',
                nominal_dimension: part.nominal_dimension,
                tolerance_min: part.tolerance_min,
                tolerance_max: part.tolerance_max,
                unit: part.unit || 'mm',
                is_critical: true,
                instrument: {
                    instrument_code: 'CAL-001',
                    name: 'Digital Caliper 150mm',
                    measuring_range: '0-150mm'
                }
            }
        ];

        // 3. Render Parameter Pills in Spec Card
        const specParamsList = document.getElementById('specParamsList');
        if (specParamsList) {
            specParamsList.innerHTML = activeParameters.map(p => `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 shadow-2xs text-slate-800 text-[11px] font-medium">
                    <span class="w-1.5 h-1.5 rounded-full ${p.is_critical ? 'bg-indigo-600' : 'bg-slate-400'}"></span>
                    <strong class="text-indigo-950">${escapeHtml(p.parameter_name)}</strong>:
                    <span class="font-mono text-indigo-700 font-semibold">${p.nominal_dimension.toFixed(3)} ${p.unit || 'mm'}</span>
                    <span class="text-slate-400 text-[10px] font-mono">[${p.tolerance_min.toFixed(3)} ~ ${p.tolerance_max.toFixed(3)}]</span>
                </span>
            `).join('');
        }

        // 4. Render Assigned Tools in Spec Card (Completely clean - no calibration metadata)
        const specToolElem = document.getElementById('specToolStatus');
        if (specToolElem) {
            const distinctTools = [];
            activeParameters.forEach(p => {
                const t = p.instrument ? `${p.instrument.name} (${p.instrument.instrument_code})` : defaultGlobalTool;
                if (!distinctTools.includes(t)) distinctTools.push(t);
            });
            specToolElem.innerText = distinctTools.join(', ');
        }

        // 5. Update Matrix Badge in Table Header
        const matrixBadge = document.getElementById('paramMatrixBadge');
        if (matrixBadge) {
            matrixBadge.innerText = `${activeParameters.length} Dimensions per Sample`;
        }

        // 6. Update hidden inputs for backend defaults
        const hParamId = document.getElementById('hidden_parameter_id');
        if (hParamId) hParamId.value = activeParameters[0]?.id || '';

        const hParamName = document.getElementById('hidden_parameter_name');
        if (hParamName) hParamName.value = activeParameters[0]?.parameter_name || '';

        const hTool = document.getElementById('hidden_measurement_tool');
        if (hTool) {
            const firstTool = activeParameters[0]?.instrument;
            hTool.value = firstTool ? `${firstTool.name} (${firstTool.instrument_code})` : defaultGlobalTool;
        }

        // 7. Determine sample count from input
        const sampleSizeInput = document.getElementById('sample_size');
        currentSampleCount = parseInt(sampleSizeInput ? sampleSizeInput.value : 5) || 5;

        // 8. Render the multi-parameter sample grid
        renderSampleTable();
    }

    function applyGlobalTool(selectedTool) {
        defaultGlobalTool = selectedTool;
        const hTool = document.getElementById('hidden_measurement_tool');
        if (hTool) hTool.value = defaultGlobalTool;

        // Update all individual tool selects in table rows
        document.querySelectorAll('.row-tool-select').forEach(sel => {
            sel.value = defaultGlobalTool;
        });
    }

    function renderSampleTable() {
        const body = document.getElementById('measurementBody');
        if (!body) return;

        // Preserve already typed actual measurement values
        const preservedValues = {};
        document.querySelectorAll('.actual-input').forEach(inp => {
            const s = inp.dataset.sample;
            const pIdx = inp.dataset.paramIndex;
            if (s && pIdx !== undefined) {
                preservedValues[`${s}_${pIdx}`] = inp.value;
            }
        });

        // Preserve chosen tools per row
        const preservedTools = {};
        document.querySelectorAll('.row-tool-select').forEach(sel => {
            const s = sel.dataset.sample;
            const pIdx = sel.dataset.paramIndex;
            if (s && pIdx !== undefined) {
                preservedTools[`${s}_${pIdx}`] = sel.value;
            }
        });

        body.innerHTML = '';
        let globalPointIdx = 0;

        for (let s = 1; s <= currentSampleCount; s++) {
            const formattedS = String(s).padStart(2, '0');
            const paramCount = activeParameters.length;

            // 1. Dedicated Sample Unit Header Row (Visually grouping all dimensions for this sample)
            const headerTr = document.createElement('tr');
            headerTr.className = "bg-slate-100/80 border-t-2 border-slate-300/80";
            headerTr.innerHTML = `
                <td colspan="7" class="py-2.5 px-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600 text-white font-mono font-bold text-xs shadow-2xs">#${formattedS}</span>
                            <span class="font-extrabold text-slate-900 text-xs tracking-wider uppercase">Physical Sample Unit #${formattedS}</span>
                            <span class="text-[10px] text-indigo-700 font-bold bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md">
                                ${paramCount} Dimension(s) to Measure
                            </span>
                        </div>
                        <button type="button" onclick="removeSample(${s})" title="Remove Sample #${formattedS} and its measurement rows"
                            class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1 transition cursor-pointer">
                            <span class="material-symbols-outlined text-[15px]">delete</span>
                            <span>Remove Unit #${formattedS}</span>
                        </button>
                    </div>
                </td>
            `;
            body.appendChild(headerTr);

            // 2. Dimension measurement rows for this sample
            activeParameters.forEach((param, k) => {
                globalPointIdx++;
                const tr = document.createElement('tr');
                tr.className = "hover:bg-indigo-50/20 transition measurement-row border-b border-slate-100";
                tr.id = `row-point-${globalPointIdx}`;

                const defaultToolForParam = param.instrument
                    ? `${param.instrument.name} (${param.instrument.instrument_code})`
                    : defaultGlobalTool;

                const chosenTool = preservedTools[`${s}_${k}`] || defaultToolForParam;

                // Tool selection options HTML (Completely clean - no calibration badges or labels)
                let toolOptionsHtml = '';
                if (instrumentsList.length > 0) {
                    instrumentsList.forEach(ins => {
                        const isSelected = (ins.label === chosenTool) ? 'selected' : '';
                        toolOptionsHtml += `<option value="${escapeHtml(ins.label)}" ${isSelected}>${escapeHtml(ins.label)}</option>`;
                    });
                } else {
                    toolOptionsHtml = `<option value="${escapeHtml(chosenTool)}" selected>${escapeHtml(chosenTool)}</option>`;
                }

                // Look up preserved actual value or use nominal dimension as clean default
                const existingVal = preservedValues[`${s}_${k}`];
                const initialVal = (existingVal !== undefined && existingVal !== '')
                    ? existingVal
                    : param.nominal_dimension.toFixed(3);

                tr.innerHTML = `
                    <!-- Test Parameter Column -->
                    <td class="py-3 px-4">
                        <input type="hidden" name="sample_numbers[]" value="${s}">
                        <input type="hidden" name="parameter_names[]" value="${escapeHtml(param.parameter_name)}">
                        <input type="hidden" name="nominal_values[]" value="${param.nominal_dimension}">
                        <input type="hidden" name="tolerance_mins[]" value="${param.tolerance_min}">
                        <input type="hidden" name="tolerance_maxs[]" value="${param.tolerance_max}">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 shrink-0">
                                <span class="material-symbols-outlined text-[16px]">straighten</span>
                            </span>
                            <div>
                                <div class="font-bold text-slate-900 text-xs">${escapeHtml(param.parameter_name)}</div>
                                <span class="text-[10px] ${param.is_critical ? 'text-amber-700 font-bold' : 'text-slate-400'}">
                                    ${param.is_critical ? '★ Critical Dimension' : 'Standard Dimension'}
                                </span>
                            </div>
                        </div>
                    </td>

                    <!-- Measurement Tool Selection Column (Zero Calibration Metadata) -->
                    <td class="py-3 px-4">
                        <select name="measurement_tools[]" class="row-tool-select w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-500 transition cursor-pointer"
                            data-sample="${s}" data-param-index="${k}">
                            ${toolOptionsHtml}
                        </select>
                    </td>

                    <!-- Nominal Spec Column -->
                    <td class="py-3 px-4 font-mono">
                        <div class="font-bold text-xs text-slate-900">${param.nominal_dimension.toFixed(3)} ${param.unit || 'mm'}</div>
                        <div class="text-[11px] text-slate-500">Tol: [${param.tolerance_min.toFixed(3)} ~ ${param.tolerance_max.toFixed(3)}]</div>
                    </td>

                    <!-- Actual Measurement Input Column -->
                    <td class="py-3 px-4">
                        <div class="relative rounded-xl">
                            <input type="number" step="0.001" name="measurements[]" value="${initialVal}" required
                                class="actual-input w-full bg-white border border-slate-300 rounded-xl pl-3 pr-9 py-2 text-xs text-slate-900 font-mono font-bold focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition shadow-2xs hover:border-slate-400"
                                data-sample="${s}"
                                data-param-index="${k}"
                                data-point="${globalPointIdx}"
                                data-nominal="${param.nominal_dimension}"
                                data-min="${param.tolerance_min}"
                                data-max="${param.tolerance_max}"
                                data-unit="${param.unit || 'mm'}"
                                oninput="evalPoint(this)" placeholder="0.000" />
                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[11px] font-mono font-bold text-slate-400 pointer-events-none">${param.unit || 'mm'}</span>
                        </div>
                    </td>

                    <!-- Deviation (Δ) Column -->
                    <td class="py-3 px-4 text-center font-mono">
                        <span id="dev-point-${globalPointIdx}" class="inline-flex items-center gap-0.5 px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-slate-100 text-slate-700 border border-slate-200">
                            Δ +0.000 ${param.unit || 'mm'}
                        </span>
                    </td>

                    <!-- Pass / Fail Status Badge Column -->
                    <td class="py-3 px-4 text-center">
                        <div id="badge-point-${globalPointIdx}">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>PASS
                            </span>
                        </div>
                    </td>

                    <!-- Unit Column -->
                    <td class="py-3 px-4 text-center font-mono text-slate-400 font-bold">
                        ${param.unit || 'mm'}
                    </td>
                `;

                body.appendChild(tr);
            });
        }

        // Real-time evaluation of all measurement points
        evalAllPoints();
    }

    function evalAllPoints() {
        document.querySelectorAll('.actual-input').forEach(inp => {
            evalPoint(inp, false);
        });
        recalcSummary();
    }

    function evalPoint(input, triggerRecalc = true) {
        const val = parseFloat(input.value);
        const pointIdx = input.dataset.point;
        const nominal = parseFloat(input.dataset.nominal) || 0;
        const min = parseFloat(input.dataset.min) || 0;
        const max = parseFloat(input.dataset.max) || 0;
        const unit = input.dataset.unit || 'mm';

        const devElem = document.getElementById(`dev-point-${pointIdx}`);
        const badgeElem = document.getElementById(`badge-point-${pointIdx}`);
        if (!devElem || !badgeElem) return;

        if (isNaN(val)) {
            devElem.innerHTML = `<span class="text-slate-400">Δ -</span>`;
            devElem.className = "inline-flex items-center gap-0.5 px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-slate-100 text-slate-500 border border-slate-200";
            badgeElem.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">-</span>`;
            if (triggerRecalc) recalcSummary();
            return;
        }

        const dev = val - nominal;
        const sign = dev >= 0 ? `+${dev.toFixed(3)}` : dev.toFixed(3);
        const isPass = (val >= min && val <= max);

        if (isPass) {
            devElem.innerHTML = `Δ ${sign} ${unit}`;
            devElem.className = "inline-flex items-center gap-0.5 px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-emerald-50 text-emerald-700 border border-emerald-200";
            badgeElem.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span>PASS
                </span>
            `;
        } else {
            devElem.innerHTML = `Δ ${sign} ${unit}`;
            devElem.className = "inline-flex items-center gap-0.5 px-2.5 py-1 rounded-lg font-mono font-bold text-xs bg-rose-50 text-rose-700 border border-rose-300 font-extrabold";
            badgeElem.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-400 shadow-2xs animate-pulse">
                    <span class="material-symbols-outlined text-[14px]">warning</span>OUT OF SPEC
                </span>
            `;
        }

        if (triggerRecalc) recalcSummary();
    }

    function addSample() {
        currentSampleCount++;
        const sampleSizeInput = document.getElementById('sample_size');
        if (sampleSizeInput) sampleSizeInput.value = currentSampleCount;
        renderSampleTable();
    }

    function removeSample(sampleNumber) {
        if (currentSampleCount <= 1) {
            alert('At least 1 physical sample measurement is required.');
            return;
        }

        // Shift saved measurements when deleting sampleNumber
        const currentInputs = document.querySelectorAll('.actual-input');
        const remappedValues = {};
        currentInputs.forEach(inp => {
            const s = parseInt(inp.dataset.sample);
            const pIdx = inp.dataset.paramIndex;
            if (s < sampleNumber) {
                remappedValues[`${s}_${pIdx}`] = inp.value;
            } else if (s > sampleNumber) {
                remappedValues[`${s - 1}_${pIdx}`] = inp.value;
            }
        });

        currentSampleCount--;
        const sampleSizeInput = document.getElementById('sample_size');
        if (sampleSizeInput) sampleSizeInput.value = currentSampleCount;

        renderSampleTable();
    }

    function adjustSampleRows(val) {
        let count = parseInt(val) || 1;
        if (count < 1) count = 1;
        currentSampleCount = count;
        renderSampleTable();
    }

    function resetToAql() {
        currentSampleCount = 5;
        const sampleSizeInput = document.getElementById('sample_size');
        if (sampleSizeInput) sampleSizeInput.value = currentSampleCount;
        renderSampleTable();
    }

    function recalcSummary() {
        if (currentInspectionMode !== 'dimensional') return;
        let pass = 0;
        let fail = 0;
        const inputs = document.querySelectorAll('.actual-input');
        const totalPoints = inputs.length;

        inputs.forEach(inp => {
            const val = parseFloat(inp.value);
            const min = parseFloat(inp.dataset.min);
            const max = parseFloat(inp.dataset.max);
            if (!isNaN(val)) {
                if (val >= min && val <= max) {
                    pass++;
                } else {
                    fail++;
                }
            }
        });

        const countSamplesElem = document.getElementById('countSamples');
        if (countSamplesElem) countSamplesElem.innerText = currentSampleCount;

        const countTotalElem = document.getElementById('countTotal');
        if (countTotalElem) countTotalElem.innerText = totalPoints;

        const countPassElem = document.getElementById('countPass');
        if (countPassElem) countPassElem.innerText = pass;

        const countFailElem = document.getElementById('countFail');
        if (countFailElem) countFailElem.innerText = fail;

        // Real-time Verdict Banner Update
        const verdictBanner = document.getElementById('lotVerdictBanner');
        const verdictText = document.getElementById('lotVerdictText');
        if (verdictBanner && verdictText) {
            if (fail === 0) {
                verdictBanner.className = "inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs";
                verdictBanner.innerHTML = `
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    <span id="lotVerdictText">ALL SAMPLES COMPLIANT - PASS (Ready for Submission)</span>
                `;
            } else {
                verdictBanner.className = "inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs animate-pulse";
                verdictBanner.innerHTML = `
                    <span class="material-symbols-outlined text-[18px]">warning</span>
                    <span id="lotVerdictText">${fail} MEASUREMENT(S) OUT OF SPEC - DEFECT ESCALATION REQUIRED</span>
                `;
            }
        }

        // Conditional Submit Button Logic (Exact Requirements: fail == 0 -> Save & Complete, fail > 0 -> Submit Defect Report)
        const submitBtn = document.getElementById('submitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitBtnIcon = document.getElementById('submitBtnIcon');

        if (submitBtn && submitBtnText && submitBtnIcon) {
            if (fail === 0) {
                // If all samples pass (fail == 0): standard green style
                submitBtn.className = "w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer";
                submitBtnIcon.innerText = "task_alt";
                submitBtnText.innerText = "Save & Complete Inspection Log";
            } else {
                // If there are any out-of-spec samples (fail > 0): formal defect escalation red/rose style
                submitBtn.className = "w-full sm:w-auto px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs shadow-lg shadow-rose-600/30 transition flex items-center justify-center gap-2 cursor-pointer animate-pulse";
                submitBtnIcon.innerText = "report_problem";
                submitBtnText.innerText = "Submit Defect Report to Supervisor";
            }
        }
    }
</script>
@endsection