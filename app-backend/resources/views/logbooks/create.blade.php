@extends('layouts.app')
@section('title', 'Quality Inspection Logbook - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">assignment</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">IQC Quality Inspection Logbook Form</h1>
                <p class="text-xs text-slate-500 mt-0.5">UC-04: Testing dimensional, electrical & visual parameters against nominal tolerance specifications.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold border
                @if(auth()->user()->role === 'inspector_ee') bg-emerald-50 text-emerald-700 border-emerald-200
                @elseif(auth()->user()->role === 'inspector_automotive') bg-teal-50 text-teal-700 border-teal-200
                @elseif(auth()->user()->role === 'inspector_wire_harness') bg-purple-50 text-purple-700 border-purple-200
                @else bg-indigo-50 text-indigo-700 border-indigo-200 @endif">
                Sub-Role: {{ auth()->user()->role_label }}
            </span>
            <a href="{{ route('logbooks.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                Back to History
            </a>
        </div>
    </div>

    @if(!$receiving)
    <!-- Step 1: Select Material from Available Inbound Queue -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
            <span class="material-symbols-outlined text-[16px]">conveyor_belt</span>
            <span>Step 1: Select Material Lot from Inbound Queue</span>
        </div>
        <h2 class="text-base font-bold text-slate-900">Select Material Ready for Inspection</h2>
        <p class="text-xs text-slate-500">Displaying materials in the queue matching your commodity authorization ({{ auth()->user()->role_label }}).</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            @forelse($pendingReceivings as $item)
            <div class="p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 hover:shadow-md transition bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start gap-2">
                        <span class="font-mono text-xs font-bold text-indigo-600">{{ $item->no_grn_iqa }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $item->aging_badge_class }}">
                            {{ $item->aging_status }}
                        </span>
                    </div>
                    <h3 class="font-bold text-sm text-slate-800 mt-2">{{ $item->part->nama_part ?? '-' }}</h3>
                    <div class="text-xs text-slate-500 font-mono mt-0.5">Part: {{ $item->part->kode_part ?? '-' }}</div>
                    <div class="text-xs text-slate-500 mt-1">Supplier: {{ $item->supplier->nama_supplier ?? '-' }}</div>
                    <div class="text-xs text-slate-500 font-mono mt-0.5">Lot: {{ $item->no_lot }} | Qty: {{ number_format($item->qty_datang) }} pcs</div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-200 flex justify-between items-center">
                    <span class="text-[11px] font-semibold text-slate-500">{{ $item->part->komoditas_label }}</span>
                    <a href="{{ route('logbooks.create', ['receiving_id' => $item->id]) }}" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition">
                        Open Form &rarr;
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-slate-400">
                <span class="material-symbols-outlined text-[36px] text-slate-300 block mb-1">hourglass_empty</span>
                No inbound materials currently awaiting inspection for your commodity.
            </div>
            @endforelse
        </div>
    </div>
    @else
    <!-- Step 2: Material Selected - Inspection Form with Parameter Table -->
    <form action="{{ route('logbooks.store') }}" method="POST" id="inspectionForm" class="space-y-6">
        @csrf
        <input type="hidden" name="receiving_id" value="{{ $receiving->id }}">
        <input type="hidden" name="action_type" id="actionTypeInput" value="submit">

        <!-- Material Details Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold font-mono">
                        GRN
                    </div>
                    <div>
                        <div class="font-mono font-bold text-slate-900 text-sm">{{ $receiving->no_grn_iqa }}</div>
                        <div class="text-xs text-slate-500">PO No: <span class="font-mono text-slate-700 font-semibold">{{ $receiving->no_po }}</span> | Lot: <span class="font-mono text-slate-700 font-bold">{{ $receiving->no_lot }}</span></div>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs flex-wrap">
                    <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400">Inbound Qty:</span>
                        <b class="text-slate-800 font-mono">{{ number_format($receiving->qty_datang) }} pcs</b>
                    </div>
                    <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400">Commodity:</span>
                        <b class="text-indigo-700">{{ $receiving->part->komoditas_label }}</b>
                    </div>
                    <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400">Standard:</span>
                        <b class="text-slate-700">{{ $receiving->part->standar_aql }}</b>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-2 text-xs">
                <div>
                    <span class="text-slate-400 font-medium">Component Name:</span>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $receiving->part->nama_part }}</div>
                    <div class="font-mono text-slate-500 mt-0.5">{{ $receiving->part->kode_part }}</div>
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Supplier & Country:</span>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $receiving->supplier->nama_supplier }}</div>
                    <div class="text-slate-500 mt-0.5">{{ $receiving->supplier->negara_asal }}</div>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Test Sample Size (Sampling Size):</label>
                    <select name="qty_sampel" id="sampleSizeSelect" class="w-full text-xs rounded-xl border-slate-200 py-2">
                        <option value="5" selected>5 Samples (Level II Standard)</option>
                        <option value="8">8 Samples</option>
                        <option value="13">13 Samples</option>
                        <option value="20">20 Samples</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Sample Measurements Table -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[16px]">straighten</span>
                        <span>Dimensional & Physical Parameter Measurement Sheet</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Record Sample Measurement Values</h3>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> PASS (In Tolerance)
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> FAIL (Out-of-Spec)
                    </span>
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
                            @for($s = 1; $s <= 5; $s++)
                                <th class="py-3 px-3 text-center sample-col sample-col-{{ $s }}">S-{{ $s }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($receiving->part->parameters as $param)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-3 font-bold text-slate-800">
                                {{ $param->nama_parameter }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">
                                {{ $param->nominal !== null ? number_format($param->nominal, 2) : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-500">
                                {{ $param->batas_min !== null ? number_format($param->batas_min, 2) : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-500">
                                {{ $param->batas_max !== null ? number_format($param->batas_max, 2) : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-indigo-600 font-bold">
                                {{ $param->satuan }}
                            </td>
                            @for($s = 1; $s <= 5; $s++)
                            <td class="py-2 px-2 text-center sample-col sample-col-{{ $s }}">
                                <input type="number" step="any"
                                    name="measurements[{{ $param->id }}][{{ $s }}]"
                                    data-min="{{ $param->batas_min }}"
                                    data-max="{{ $param->batas_max }}"
                                    class="w-20 text-center text-xs font-mono py-1.5 px-2 rounded-xl border border-slate-200 measurement-input focus:ring-2 focus:ring-indigo-500"
                                    placeholder="{{ $param->nominal ?? '0.00' }}"
                                    oninput="validateInput(this)">
                            </td>
                            @endfor
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-slate-400">
                                No specification parameters registered for this component. Please contact Master Data.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="outOfSpecAlert" class="hidden mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-rose-600 shrink-0">report_problem</span>
                <div>
                    <b>Out-of-Spec Warning:</b> Sample measurement(s) detected outside nominal tolerance limits. Upon submission, this logbook will automatically route to <b>Pending MRB Review</b> for supervisor disposition.
                </div>
            </div>
        </div>

        <!-- Submission Action Buttons -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                Ensure all measurements are taken using ISO 9001 calibrated instruments.
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="button" onclick="submitAs('draft')" 
                    class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex-1 sm:flex-none">
                    Save as Draft
                </button>
                <button type="button" onclick="submitAs('submit')" 
                    class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition flex-1 sm:flex-none">
                    Submit Inspection Logbook &rarr;
                </button>
            </div>
        </div>
    </form>
    @endif

</div>

<script>
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

    checkAllOutOfSpec();
}

function checkAllOutOfSpec() {
    const inputs = document.querySelectorAll('.measurement-input');
    let hasFail = false;
    inputs.forEach(inp => {
        if (inp.classList.contains('border-rose-500')) {
            hasFail = true;
        }
    });
    const alertBox = document.getElementById('outOfSpecAlert');
    if (alertBox) {
        if (hasFail) alertBox.classList.remove('hidden');
        else alertBox.classList.add('hidden');
    }
}

function submitAs(type) {
    document.getElementById('actionTypeInput').value = type;
    document.getElementById('inspectionForm').submit();
}
</script>
@endsection

