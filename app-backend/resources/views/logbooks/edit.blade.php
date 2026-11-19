@extends('layouts.app')
@section('title', 'Revise Inspection Logbook #' . $logbook->id . ' - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">edit_note</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Revise Measurement Data - Logbook #{{ $logbook->id }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Correct measurement values based on Quality Supervisor directives and evaluation notes.</p>
            </div>
        </div>

        <a href="{{ route('logbooks.show', $logbook->id) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
            Cancel
        </a>
    </div>

    <!-- Supervisor Revision Directive Card -->
    @if($logbook->latestMrbDisposition)
    <div class="p-5 rounded-3xl bg-amber-50 border border-amber-300 shadow-sm space-y-2">
        <div class="flex items-center justify-between text-xs font-bold text-amber-900">
            <span class="flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600">assignment_return</span>
                Revision Directive from Quality Supervisor ({{ $logbook->latestMrbDisposition->supervisor->name ?? 'Supervisor' }})
            </span>
            <span class="font-mono text-amber-700">{{ $logbook->latestMrbDisposition->diputuskan_pada ? $logbook->latestMrbDisposition->diputuskan_pada->format('d/m/Y H:i') : '' }}</span>
        </div>
        <div class="p-3.5 bg-white rounded-2xl border border-amber-200 text-xs text-slate-800 font-mono leading-relaxed">
            {{ $logbook->latestMrbDisposition->catatan }}
        </div>
    </div>
    @endif

    <!-- Form Edit Measurements -->
    <form action="{{ route('logbooks.update', $logbook->id) }}" method="POST" id="editInspectionForm" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="action_type" id="editActionTypeInput" value="submit">

        <!-- Material Details Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 font-medium">GRN / Lot No:</span>
                    <span class="text-slate-400 font-medium">GR / Lot No:</span>
                    <div class="font-mono font-bold text-slate-800 text-sm mt-0.5">{{ $logbook->receiving->no_grn_iqa }}</div>
                    <div class="font-mono text-slate-500 mt-0.5">Lot: {{ $logbook->receiving->no_lot }}</div>
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Component & Commodity:</span>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $logbook->receiving->part->nama_part }}</div>
                    <div class="text-indigo-700 font-semibold mt-0.5">{{ $logbook->receiving->part->komoditas_label }}</div>
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Supplier / Vendor:</span>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $logbook->receiving->supplier->nama_supplier }}</div>
                    <div class="text-slate-500 mt-0.5">{{ $logbook->receiving->supplier->negara_asal }}</div>
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Sample Size:</span>
                    <div class="font-mono font-bold text-indigo-700 text-sm mt-0.5">{{ $logbook->qty_sampel }} pcs</div>
                    <div class="text-slate-500 mt-0.5">Level II Standard</div>
                </div>
            </div>
        </div>

        <!-- 1. Visual & Packaging Verification (Correction) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                        <span>Visual & Packaging Inspection Gate</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Physical Packaging & Visual Verification</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-2">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-900">Packaging & Labeling</div>
                        <p class="text-[11px] text-slate-500 mt-1">Reel/box sealed, label matching PO & Lot.</p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="radio" name="visual_packaging" value="pass" {{ $logbook->visual_packaging === 'pass' ? 'checked' : '' }} class="text-emerald-600">
                            <span>PASS</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 cursor-pointer">
                            <input type="radio" name="visual_packaging" value="fail" {{ $logbook->visual_packaging === 'fail' ? 'checked' : '' }} class="text-rose-600">
                            <span>FAIL</span>
                        </label>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-900">ESD & Moisture (HIC)</div>
                        <p class="text-[11px] text-slate-500 mt-1">Bag intact, HIC card blue (not pink).</p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="radio" name="visual_esd_moisture" value="pass" {{ $logbook->visual_esd_moisture === 'pass' ? 'checked' : '' }} class="text-emerald-600">
                            <span>PASS</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 cursor-pointer">
                            <input type="radio" name="visual_esd_moisture" value="na" {{ $logbook->visual_esd_moisture === 'na' ? 'checked' : '' }} class="text-slate-600">
                            <span>N/A</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 cursor-pointer">
                            <input type="radio" name="visual_esd_moisture" value="fail" {{ $logbook->visual_esd_moisture === 'fail' ? 'checked' : '' }} class="text-rose-600">
                            <span>FAIL</span>
                        </label>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-900">Body Integrity</div>
                        <p class="text-[11px] text-slate-500 mt-1">No chips, cracks, burns, or flashing.</p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="radio" name="visual_body" value="pass" {{ $logbook->visual_body === 'pass' ? 'checked' : '' }} class="text-emerald-600">
                            <span>PASS</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 cursor-pointer">
                            <input type="radio" name="visual_body" value="fail" {{ $logbook->visual_body === 'fail' ? 'checked' : '' }} class="text-rose-600">
                            <span>FAIL</span>
                        </label>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-900">Lead Pin & Solderability</div>
                        <p class="text-[11px] text-slate-500 mt-1">Pins straight, no oxidation or rust.</p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="radio" name="visual_terminal" value="pass" {{ $logbook->visual_terminal === 'pass' ? 'checked' : '' }} class="text-emerald-600">
                            <span>PASS</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 cursor-pointer">
                            <input type="radio" name="visual_terminal" value="fail" {{ $logbook->visual_terminal === 'fail' ? 'checked' : '' }} class="text-rose-600">
                            <span>FAIL</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Shelf-Life & Expiration (Correction) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[16px]">event_available</span>
                        <span>Shelf-Life & Expiration Verification</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Date Code & Expiry Limit</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">Date Code / Lot Code:</label>
                    <input type="text" name="date_code" value="{{ $logbook->date_code }}"
                        class="w-full text-xs font-mono rounded-xl border border-slate-200 py-2.5 px-3">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">Manufacturing Date:</label>
                    <input type="date" name="manufacturing_date" value="{{ $logbook->manufacturing_date ? $logbook->manufacturing_date->format('Y-m-d') : '' }}"
                        class="w-full text-xs rounded-xl border border-slate-200 py-2.5 px-3">
                </div>
                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">Expiration Date:</label>
                    <input type="date" name="expiration_date" value="{{ $logbook->expiration_date ? $logbook->expiration_date->format('Y-m-d') : '' }}"
                        class="w-full text-xs rounded-xl border border-slate-200 py-2.5 px-3">
                </div>
            </div>
        </div>

        <!-- Editable Sample Measurements Table -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[16px]">edit_attributes</span>
                        <span>Measurement Sheet Correction</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Update Sample Measurement Values</h3>
                </div>
            </div>

            <div class="overflow-x-auto mt-4">
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($logbook->receiving->part->parameters as $param)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-3 font-bold text-slate-800">{{ $param->nama_parameter }}</td>
                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">{{ $param->nominal !== null ? number_format($param->nominal, 2) : '-' }}</td>
                            <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $param->batas_min !== null ? number_format($param->batas_min, 2) : '-' }}</td>
                            <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $param->batas_max !== null ? number_format($param->batas_max, 2) : '-' }}</td>
                            <td class="py-3 px-3 text-center font-mono text-indigo-600 font-bold">{{ $param->satuan }}</td>
                            @for($s = 1; $s <= $logbook->qty_sampel; $s++)
                                @php 
                                    $existingVal = $logbook->measurements
                                        ->where('part_parameter_id', $param->id)
                                        ->where('sample_number', $s)
                                        ->first();
                                    $val = $existingVal ? $existingVal->nilai_ukur : null;
                                @endphp
                                <td class="py-2 px-2 text-center">
                                    <input type="number" step="any"
                                        name="measurements[{{ $param->id }}][{{ $s }}]"
                                        value="{{ $val }}"
                                        data-min="{{ $param->batas_min }}"
                                        data-max="{{ $param->batas_max }}"
                                        class="w-20 text-center text-xs font-mono py-1.5 px-2 rounded-xl border border-slate-200 measurement-input focus:ring-2 focus:ring-indigo-500"
                                        placeholder="{{ $param->nominal ?? '0.00' }}"
                                        oninput="validateInput(this)">
                                </td>
                            @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                Upon re-submission, this logbook will be returned to the IQC Supervisor queue for final verification.
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="button" onclick="submitEditAs('draft')" 
                    class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex-1 sm:flex-none">
                    Save Draft
                </button>
                <button type="button" onclick="submitEditAs('submit')" 
                    class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-amber-600 to-indigo-600 hover:from-amber-700 hover:to-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition flex-1 sm:flex-none">
                    Submit Revision to Supervisor &rarr;
                </button>
            </div>
        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.measurement-input').forEach(inp => validateInput(inp));
});

function validateInput(input) {
    const min = parseFloat(input.dataset.min);
    const max = parseFloat(input.dataset.max);
    const val = parseFloat(input.value);

    if (isNaN(val)) {
        input.classList.remove('bg-emerald-50', 'border-emerald-500', 'text-emerald-700', 'bg-rose-50', 'border-rose-500', 'text-rose-700');
        input.classList.add('border-slate-200');
    } else if ((!isNaN(min) && val < min) || (!isNaN(max) && val > max)) {
        input.classList.remove('bg-emerald-50', 'border-emerald-500', 'text-emerald-700', 'border-slate-200');
        input.classList.add('bg-rose-50', 'border-rose-500', 'text-rose-700', 'font-bold');
    } else {
        input.classList.remove('bg-rose-50', 'border-rose-500', 'text-rose-700', 'border-slate-200');
        input.classList.add('bg-emerald-50', 'border-emerald-500', 'text-emerald-700', 'font-bold');
    }
}

function submitEditAs(type) {
    document.getElementById('editActionTypeInput').value = type;
    document.getElementById('editInspectionForm').submit();
}
</script>
@endsection

