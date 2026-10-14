@extends('layouts.app')

@section('title', 'Inspection & Dimension Measurement Form - Logbook Digital IQC')

@php
    $isGrnLocked = $isGrnLocked ?? (!empty(request('incoming_id')) || !empty(request('grn')) || request()->boolean('locked') || old('locked') === '1');
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

            <!-- Part Specification Spec Sheet Preview -->
            <div id="specCard" class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Supplier:</span>
                    <span id="specSupplier" class="text-slate-900 font-semibold">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Nominal Dimension:</span>
                    <span id="specNominal" class="text-indigo-600 font-bold font-mono">- mm</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Tolerance Limits (Min - Max):</span>
                    <span id="specTolerance" class="text-emerald-600 font-bold font-mono">- to - mm</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Sampling Level & AQL:</span>
                    <span id="specAQL" class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 border border-indigo-200 font-semibold">-</span>
                </div>
                @if($isGrnLocked)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-200/60 text-slate-600 font-semibold text-[11px]">
                        <span class="material-symbols-outlined text-[14px] text-amber-600">lock</span>
                        <span>Drawing spec locked</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Section 2: Input Pengukuran Sampel Aktual -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-600 icon-md">tune</span>
                        Physical Sample Measurement Table
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Enter actual values from micrometer / caliper measurements</p>
                </div>
                <button type="button" onclick="addSampleRow()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 border border-slate-300 text-xs font-semibold flex items-center gap-1 transition">
                    <span class="material-symbols-outlined icon-sm">add</span>
                    Add Sample
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600" id="measurementTable">
                    <thead class="bg-white/90 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-16 text-center">Sample #</th>
                            <th class="py-3 px-4 w-40">Nominal Spec</th>
                            <th class="py-3 px-4 w-48">Actual Measurement (mm) *</th>
                            <th class="py-3 px-4 w-32 text-center">Deviation</th>
                            <th class="py-3 px-4 w-32 text-center">Evaluation Result</th>
                            <th class="py-3 px-4 text-right w-20">Action</th>
                        </tr>
                    </thead>
                    <tbody id="measurementBody" class="divide-y divide-slate-200">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>

            <!-- Real-time Verification Bar -->
            <div class="p-4 bg-white/95 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-400">Samples Passed (Pass): <strong id="countPass" class="text-emerald-600">0</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span class="text-slate-400">Samples Failed (Out of Spec): <strong id="countFail" class="text-rose-600">0</strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-slate-900 font-bold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                        <span class="material-symbols-outlined icon-sm">send</span>
                        <span>Submit Logbook to Supervisor</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

<script>
    let currentNominal = 0;
    let currentMin = 0;
    let currentMax = 0;
    let currentUnit = 'mm';

    document.addEventListener('DOMContentLoaded', () => {
        const partSelect = document.getElementById('part_id');
        if (partSelect.value) {
            onPartSelected(partSelect);
        } else if (partSelect.options.length > 1) {
            partSelect.selectedIndex = 1;
            onPartSelected(partSelect);
        }
    });

    function onPartSelected(select) {
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) return;

        currentNominal = parseFloat(opt.dataset.nominal) || 0;
        currentMin = parseFloat(opt.dataset.min) || 0;
        currentMax = parseFloat(opt.dataset.max) || 0;
        currentUnit = opt.dataset.unit || 'mm';

        document.getElementById('specSupplier').innerText = opt.dataset.supplier || '-';
        document.getElementById('specNominal').innerText = `${currentNominal.toFixed(3)} ${currentUnit}`;
        document.getElementById('specTolerance').innerText = `${currentMin.toFixed(3)} to ${currentMax.toFixed(3)} ${currentUnit}`;
        document.getElementById('specAQL').innerText = `AQL ${opt.dataset.aql || '1.0'}`;

        // Rebuild initial 5 rows
        buildInitialRows(parseInt(document.getElementById('sample_size').value) || 5);
    }

    function buildInitialRows(count) {
        const body = document.getElementById('measurementBody');
        body.innerHTML = '';
        for (let i = 1; i <= count; i++) {
            createRow(i, currentNominal);
        }
        recalcSummary();
    }

    function createRow(index, initialVal = currentNominal) {
        const body = document.getElementById('measurementBody');
        const tr = document.createElement('tr');
        tr.className = "hover:bg-slate-50/50 transition measurement-row";
        tr.id = `row-${index}`;

        tr.innerHTML = `
            <td class="py-3 px-4 text-center font-bold text-slate-400">${index}</td>
            <td class="py-3 px-4 font-mono text-slate-400">${currentNominal.toFixed(3)} ${currentUnit}</td>
            <td class="py-3 px-4">
                <input type="number" step="0.001" name="measurements[]" value="${initialVal.toFixed(3)}" required
                    oninput="evalRow(this, ${index})"
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-900 font-mono focus:border-indigo-500 focus:outline-none transition" />
            </td>
            <td class="py-3 px-4 text-center font-mono text-xs" id="dev-${index}">+0.000</td>
            <td class="py-3 px-4 text-center" id="badge-${index}">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">PASS</span>
            </td>
            <td class="py-3 px-4 text-right">
                <button type="button" onclick="removeRow(${index})" class="text-slate-500 hover:text-rose-600 transition p-1">
                    <span class="material-symbols-outlined icon-sm">delete</span>
                </button>
            </td>
        `;
        body.appendChild(tr);
        evalRow(tr.querySelector('input'), index);
    }

    function addSampleRow() {
        const rows = document.querySelectorAll('.measurement-row');
        const nextIndex = rows.length + 1;
        createRow(nextIndex, currentNominal);
        document.getElementById('sample_size').value = nextIndex;
        recalcSummary();
    }

    function removeRow(index) {
        const row = document.getElementById(`row-${index}`);
        if (row) {
            row.remove();
            renumberRows();
            recalcSummary();
        }
    }

    function renumberRows() {
        const rows = document.querySelectorAll('.measurement-row');
        rows.forEach((r, idx) => {
            const num = idx + 1;
            r.id = `row-${num}`;
            r.querySelector('td:first-child').innerText = num;
        });
        document.getElementById('sample_size').value = rows.length;
    }

    function evalRow(input, index) {
        const val = parseFloat(input.value);
        const devElem = document.getElementById(`dev-${index}`);
        const badgeElem = document.getElementById(`badge-${index}`);

        if (isNaN(val)) {
            devElem.innerText = '-';
            badgeElem.innerHTML = `<span class="text-slate-500">-</span>`;
            recalcSummary();
            return;
        }

        const dev = val - currentNominal;
        const sign = dev >= 0 ? `+${dev.toFixed(3)}` : dev.toFixed(3);
        devElem.innerText = sign;

        const isPass = (val >= currentMin && val <= currentMax);
        if (isPass) {
            badgeElem.innerHTML = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">PASS</span>`;
            devElem.className = "py-3 px-4 text-center font-mono text-xs text-slate-600";
        } else {
            badgeElem.innerHTML = `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-500/40 animate-pulse">OUT OF SPEC</span>`;
            devElem.className = "py-3 px-4 text-center font-mono text-xs text-rose-600 font-bold";
        }

        recalcSummary();
    }

    function recalcSummary() {
        let pass = 0;
        let fail = 0;
        const inputs = document.querySelectorAll('input[name="measurements[]"]');
        inputs.forEach(inp => {
            const val = parseFloat(inp.value);
            if (!isNaN(val)) {
                if (val >= currentMin && val <= currentMax) {
                    pass++;
                } else {
                    fail++;
                }
            }
        });
        document.getElementById('countPass').innerText = pass;
        document.getElementById('countFail').innerText = fail;
    }
</script>
@endsection
