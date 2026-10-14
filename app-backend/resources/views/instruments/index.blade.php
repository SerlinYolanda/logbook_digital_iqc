@extends('layouts.app')
@section('title', 'Measurement Instrument & Calibration Status - Logbook Digital IQC')
@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">speed</span>
                Measurement Instrument & Digital Calibration Status
            </h1>
            <p class="text-xs text-slate-500 mt-1">Monitoring metrology instrument accuracy and calibration validity per ISO 9001:2015</p>
        </div>
        
        <div class="flex items-center gap-2">
            @if($stats['expired'] > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 text-xs font-bold rounded-xl border border-rose-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    {{ $stats['expired'] }} Expired Calibration
                </span>
            @elseif($stats['due_soon'] > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-xl border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ $stats['due_soon'] }} Approaching Due Date
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    All Instruments Valid
                </span>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'supervisor']))
            <button type="button" onclick="toggleAddInstrumentModal(true)" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                <span class="material-symbols-outlined icon-sm">add_circle</span>
                Add Instrument
            </button>
            @endif
        </div>
    </div>

    <!-- Stats Card Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined icon-lg">straighten</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Registered Instruments</p>
                <h3 class="text-xl font-bold text-slate-900">{{ $stats['total'] }} Unit</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined icon-lg">verified</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Calibration Valid (OK)</p>
                <h3 class="text-xl font-bold text-emerald-600">{{ $stats['valid'] }} Unit</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined icon-lg">alarm</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Approaching Due Date</p>
                <h3 class="text-xl font-bold text-amber-600">{{ $stats['due_soon'] }} Unit</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined icon-lg">sync_saved_locally</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Daily Verification</p>
                <h3 class="text-xl font-bold text-slate-800">{{ $stats['verified_pct'] }}% Complete</h3>
            </div>
        </div>
    </div>

    <!-- Equipment Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500 text-[18px]">build_circle</span>
                IQC Metrology Instrument Registry
            </h2>
            <span class="text-xs text-slate-400 font-mono">Tolerance Standard: ±0.001 mm</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Instrument ID</th>
                        <th class="py-3.5 px-4">Instrument Name</th>
                        <th class="py-3.5 px-4">Brand / Model Series</th>
                        <th class="py-3.5 px-4">Measuring Range & Resolution</th>
                        <th class="py-3.5 px-4 text-center">Calibration Due Date</th>
                        <th class="py-3.5 px-4 text-center">Calibration Status</th>
                        <th class="py-3.5 px-4 text-center">Daily Verification</th>
                        @if(in_array(auth()->user()->role, ['admin', 'supervisor']))
                        <th class="py-3.5 px-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($instruments as $ins)
                    @php
                        $calStatus = $ins->calibrationStatus();
                        $daysLeft = $ins->daysUntilDue();
                    @endphp
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3.5 px-4 font-mono font-bold text-indigo-700">{{ $ins->instrument_code }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $ins->name }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $ins->brand_model ?? '-' }}</td>
                        <td class="py-3.5 px-4 font-mono text-slate-600">{{ $ins->measuring_range ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-mono">
                            {{ $ins->calibration_due_date->format('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($calStatus === 'valid')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Valid (OK)
                                </span>
                            @elseif($calStatus === 'due_soon')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800" title="Expires in {{ $daysLeft }} days">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Due ({{ $daysLeft }}d)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Expired
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($ins->isVerifiedToday())
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                    <span class="material-symbols-outlined text-[14px]">check</span> Verified Today
                                </span>
                            @else
                                @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'inspector']))
                                <form action="{{ route('instruments.verify', $ins->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 text-[10px] font-bold rounded-lg border border-slate-200 hover:border-emerald-300 transition cursor-pointer">
                                        Verify Zero-Set
                                    </button>
                                </form>
                                @else
                                <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            @endif
                        </td>
                        @if(in_array(auth()->user()->role, ['admin', 'supervisor']))
                        <td class="py-3.5 px-4 text-right space-x-1">
                            <button type="button" onclick="openEditInstrumentModal({{ json_encode($ins) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition inline-flex items-center cursor-pointer" title="Edit">
                                <span class="material-symbols-outlined icon-sm">edit</span>
                            </button>
                            <form action="{{ route('instruments.destroy', $ins->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete instrument {{ $ins->instrument_code }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 border border-slate-200 transition inline-flex items-center cursor-pointer" title="Delete">
                                    <span class="material-symbols-outlined icon-sm">delete</span>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-400 text-xs">
                            <span class="material-symbols-outlined text-slate-300 block mb-1" style="font-size:32px">speed</span>
                            No instruments registered yet.
                            @if(in_array(auth()->user()->role, ['admin', 'supervisor']))
                                Click <strong>Add Instrument</strong> to register metrology equipment.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(in_array(auth()->user()->role, ['admin', 'supervisor']))
<!-- Add Instrument Modal -->
<div id="addInstrumentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">add_circle</span>
                Register New Measurement Instrument
            </h3>
            <button type="button" onclick="toggleAddInstrumentModal(false)" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined icon-sm">close</span>
            </button>
        </div>

        <form action="{{ route('instruments.store') }}" method="POST" class="mt-4 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Instrument ID / Code *</label>
                <input type="text" name="instrument_code" required placeholder="e.g.: TL-CAL-001" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono uppercase focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Instrument Name *</label>
                <input type="text" name="name" required placeholder="e.g.: Digital Vernier Caliper" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Brand / Model</label>
                    <input type="text" name="brand_model" placeholder="e.g.: Mitutoyo 500-196-30" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Measuring Range</label>
                    <input type="text" name="measuring_range" placeholder="e.g.: 0 - 150 mm (0.01 mm)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Calibration Due Date *</label>
                <input type="date" name="calibration_due_date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="toggleAddInstrumentModal(false)" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition flex items-center gap-1.5 shadow-md shadow-indigo-600/20 cursor-pointer">
                    <span class="material-symbols-outlined icon-sm">save</span>
                    Save Instrument
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Instrument Modal -->
<div id="editInstrumentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">edit</span>
                Edit Measurement Instrument
            </h3>
            <button type="button" onclick="toggleEditInstrumentModal(false)" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined icon-sm">close</span>
            </button>
        </div>

        <form id="editInstrumentForm" method="POST" class="mt-4 space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Instrument ID / Code *</label>
                <input type="text" id="edit_code" name="instrument_code" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono uppercase focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Instrument Name *</label>
                <input type="text" id="edit_name" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Brand / Model</label>
                    <input type="text" id="edit_brand" name="brand_model" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Measuring Range</label>
                    <input type="text" id="edit_range" name="measuring_range" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Calibration Due Date *</label>
                <input type="date" id="edit_due" name="calibration_due_date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="toggleEditInstrumentModal(false)" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition flex items-center gap-1.5 shadow-md shadow-indigo-600/20 cursor-pointer">
                    <span class="material-symbols-outlined icon-sm">save</span>
                    Update Instrument
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAddInstrumentModal(show) {
        document.getElementById('addInstrumentModal').classList.toggle('hidden', !show);
    }
    function toggleEditInstrumentModal(show) {
        document.getElementById('editInstrumentModal').classList.toggle('hidden', !show);
    }
    function openEditInstrumentModal(ins) {
        document.getElementById('editInstrumentForm').action = '/instruments/' + ins.id;
        document.getElementById('edit_code').value = ins.instrument_code || '';
        document.getElementById('edit_name').value = ins.name || '';
        document.getElementById('edit_brand').value = ins.brand_model || '';
        document.getElementById('edit_range').value = ins.measuring_range || '';
        document.getElementById('edit_due').value = (ins.calibration_due_date || '').substring(0, 10);
        toggleEditInstrumentModal(true);
    }
</script>
@endif
@endsection
