@extends('layouts.app')

@section('title', 'Quality Supervisor Validation Panel - Logbook Digital IQC')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-50 text-amber-700">
                    <span class="material-symbols-outlined icon-xl">verified</span>
                </span>
                <h1 class="text-xl font-bold text-slate-900">Quality Supervisor Authorization & Validation Panel</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Evaluate sampling measurement results from inspectors and determine lot disposition decisions.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold">
                {{ $pendingInspections->total() }} Lots Awaiting Decision
            </span>
        </div>
    </div>

    <!-- Pending Queue Table -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-700 icon-md">pending_actions</span>
                Lot Queue Awaiting Disposition Authorization
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-white/90 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Test Date</th>
                        <th class="py-3 px-4">PO & Lot Number</th>
                        <th class="py-3 px-4">Part & Supplier</th>
                        <th class="py-3 px-4 text-center">Sample / Qty</th>
                        <th class="py-3 px-4 text-center">Dimension Status</th>
                        <th class="py-3 px-4 text-center">Defect Findings</th>
                        <th class="py-3 px-4 text-center">Inspector</th>
                        <th class="py-3 px-4 text-right">Validation Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($pendingInspections as $ins)
                        @php
                            $hasFail = $ins->measurements->contains('is_pass', false);
                            $defectCount = $ins->defects->count();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono text-slate-400">
                                {{ $ins->inspection_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <div class="font-bold text-slate-900">{{ $ins->po_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $ins->lot_number }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-900">{{ $ins->part->part_name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $ins->part->supplier_name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <span class="text-slate-900 font-semibold">{{ $ins->sample_size }}</span> / {{ number_format($ins->lot_quantity) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($hasFail)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                        OUT OF SPEC
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                        IN-SPEC / PASS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($defectCount > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 font-mono">
                                        {{ $defectCount }} Defect(s)
                                    </span>
                                @else
                                    <span class="text-slate-500">None (0)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center text-slate-600">
                                {{ $ins->inspector->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('supervisor.review', $ins->id) }}" class="px-3.5 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-slate-900 font-semibold text-xs shadow-md transition inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined icon-sm">fact_check</span>
                                    <span>Set Disposition</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-500 text-xs">
                                <span class="material-symbols-outlined icon-2xl text-slate-600 block mb-2">task_alt</span>
                                All inspection lot queues have been validated. None pending.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendingInspections->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/90">
                {{ $pendingInspections->links() }}
            </div>
        @endif
    </div>

    <!-- Recent Validations History -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400 icon-md">history_edu</span>
                Recent Disposition Decisions
            </h2>
        </div>
        <div class="p-4 divide-y divide-slate-200">
            @foreach($recentValidations as $rv)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold text-slate-900">{{ $rv->lot_number }}</span> • {{ $rv->part->part_name ?? '-' }}
                        <p class="text-[11px] text-slate-400 mt-0.5">SPV Notes: {{ $rv->disposition_notes ?: 'No special notes' }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            {{ $rv->disposition === 'accept' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 
                               ($rv->disposition === 'return_to_vendor' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 
                               ($rv->disposition === 'scrap' ? 'bg-purple-50 text-purple-700 border border-purple-200' :
                               ($rv->disposition === 'rework' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200'))) }}">
                            {{ str_replace('_', ' ', $rv->disposition) }}
                        </span>
                        <a href="{{ route('inspections.show', $rv->id) }}" class="text-slate-400 hover:text-slate-900">
                            <span class="material-symbols-outlined icon-sm">visibility</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
