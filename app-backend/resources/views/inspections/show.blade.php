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

    <!-- Measurements Table -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 icon-md">straighten</span>
                Actual Sample Measurement Data ({{ $inspection->measurements->count() }} Samples)
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-white/90 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-20 text-center">Sample #</th>
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
                            <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $m->sample_number }}</td>
                            <td class="py-3 px-4 font-mono text-slate-400">{{ number_format($inspection->part->nominal_dimension, 3) }} mm</td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ number_format($m->actual_value, 3) }} mm</td>
                            <td class="py-3 px-4 text-center font-mono {{ $m->is_pass ? 'text-slate-600' : 'text-rose-600 font-bold' }}">
                                {{ $m->deviation >= 0 ? '+' : '' }}{{ number_format($m->deviation, 3) }}
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

    <!-- Defect Temuan List -->
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

</div>

@include('components.defect-modal')
@endsection
