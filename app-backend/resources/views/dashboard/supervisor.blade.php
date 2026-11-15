@extends('layouts.app')

@section('title', 'IQC Supervisor Dashboard - Logbook Digital IQC')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Welcome Header for Supervisor -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <span class="material-symbols-outlined icon-xl">admin_panel_settings</span>
                </span>
                <h1 class="text-xl font-bold text-slate-900">IQC Supervisor Control Center</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Welcome back, <span class="font-semibold text-slate-700">{{ Auth::user()->name ?? 'Supervisor' }}</span>. Monitor MRB dispositions, supplier compliance, and team inspection throughput.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('supervisor.validation') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-slate-900 text-xs font-bold shadow-lg shadow-amber-600/20 transition">
                <span class="material-symbols-outlined icon-sm">fact_check</span>
                Validation Panel
                @if($pendingCount > 0)
                    <span class="bg-rose-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                @endif
            </a>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                Supervisor Oversight Active
            </span>
        </div>
    </div>

    {{-- ══ Full-Width Chart Section ══════════════════════════════════════════ --}}
    <div class="bg-white/90 border border-slate-200 rounded-2xl shadow-xl overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 pt-5 pb-4
                    border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center
                            justify-center border border-amber-100 shrink-0">
                    <span class="material-symbols-outlined icon-md">show_chart</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">7-Day Inspection Trend</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daily outcome breakdown — approved, pending, and rejected.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="inline-flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50
                             border border-emerald-200 px-2.5 py-1 rounded-full font-medium shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Data
                </span>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('incoming-materials.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600
                          hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5
                          rounded-xl transition">
                    <span class="material-symbols-outlined icon-sm">local_shipping</span>
                    GRN Tracking
                </a>
                @endif
            </div>
        </div>
        <div class="px-6 py-5">
            <canvas id="spvTrendChart" height="65"></canvas>
        </div>
    </div>
    {{-- ══ End Chart Section ════════════════════════════════════════════════ --}}

    <!-- Section 1: Executive KPI Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <!-- Card 1: Pending MRB Dispositions -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col justify-between {{ $pendingCount > 0 ? 'border-amber-300' : '' }}">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pending MRB Dispositions</span>
                    <h3 class="text-3xl font-extrabold {{ $pendingCount > 0 ? 'text-amber-600' : 'text-slate-400' }} mt-1">
                        {{ $pendingCount }}
                        <span class="text-xs font-normal text-slate-500">Lots</span>
                    </h3>
                </div>
                <div class="p-3 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100">
                    <span class="material-symbols-outlined text-2xl">pending_actions</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Requires disposition decision</span>
                @if($pendingCount > 0)
                    <a href="{{ route('supervisor.validation') }}" class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 hover:bg-amber-100 transition">
                        Action Needed →
                    </a>
                @else
                    <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">All Clear ✓</span>
                @endif
            </div>
        </div>

        <!-- Card 2: Pass Rate MTD -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Lot Pass Rate (MTD)</span>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $passRate }}%</h3>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                    <span class="material-symbols-outlined text-2xl">verified</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">{{ $approvedCount }} approved / {{ $totalInspections }} total</span>
                <span class="font-bold {{ $passRate >= 90 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-rose-700 bg-rose-50 border-rose-200' }} px-2 py-0.5 rounded border">
                    {{ $passRate >= 90 ? 'Good' : 'Below Target' }}
                </span>
            </div>
        </div>

        <!-- Card 3: Today's Inspection Throughput -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-400 to-indigo-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Today's Inspection Count</span>
                    <h3 class="text-3xl font-extrabold text-indigo-600 mt-1">
                        {{ $inspectedTodayCount }}
                        <span class="text-xs font-normal text-slate-500">Lots Tested</span>
                    </h3>
                </div>
                <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <span class="material-symbols-outlined text-2xl">bar_chart</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Shift inspection throughput</span>
                <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                    {{ $inspectedTodayCount > 0 ? 'Active' : 'No Activity' }}
                </span>
            </div>
        </div>

        <!-- Card 4: Rejected / Out of Spec -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-400 to-rose-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Rejected / RTV</span>
                    <h3 class="text-3xl font-extrabold {{ $rejectedCount > 0 ? 'text-rose-600' : 'text-slate-400' }} mt-1">
                        {{ $rejectedCount }}
                        <span class="text-xs font-normal text-slate-500">Lots</span>
                    </h3>
                </div>
                <div class="p-3 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100">
                    <span class="material-symbols-outlined text-2xl">gpp_bad</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Rejected / returned to vendor</span>
                <a href="{{ route('inspections.index') }}?status=rejected" class="font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 hover:bg-rose-100 transition">
                    View →
                </a>
            </div>
        </div>
    </div>

    <!-- Section 2: Pending MRB / Disposition Queue Table -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600 icon-md">gavel</span>
                    Material Review Board (MRB) — Pending Disposition Queue
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Inspect out-of-spec lots submitted by inspectors and authorize formal lot disposition.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold w-fit">
                    {{ $pendingCount }} Pending Escalation{{ $pendingCount !== 1 ? 's' : '' }}
                </span>
                <a href="{{ route('supervisor.validation') }}" class="px-3 py-1 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-500 transition">
                    Open Full Panel →
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 font-semibold">
                    <tr>
                        <th class="py-3 px-4">Inspection Date</th>
                        <th class="py-3 px-4">PO / Lot Number</th>
                        <th class="py-3 px-4">Part & Supplier</th>
                        <th class="py-3 px-4 text-center">Sample / Qty</th>
                        <th class="py-3 px-4 text-center">Dimension Status</th>
                        <th class="py-3 px-4 text-center">Defects</th>
                        <th class="py-3 px-4 text-center">Inspector</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($pendingInspections as $ins)
                        @php
                            $hasFail = $ins->measurements->contains('is_pass', false);
                            $defectCount = $ins->defects->count();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono text-slate-500">
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
                                <span class="text-slate-900 font-semibold">{{ $ins->sample_size }}</span>
                                / {{ number_format($ins->lot_quantity) }} pcs
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($hasFail)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">OUT OF SPEC</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">IN-SPEC / PASS</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($defectCount > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 font-mono">{{ $defectCount }} Defect(s)</span>
                                @else
                                    <span class="text-slate-400">None (0)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center text-slate-700 font-medium">
                                {{ $ins->inspector->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('supervisor.review', $ins->id) }}" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-slate-900 font-bold text-[11px] shadow-md transition inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined icon-sm">fact_check</span>
                                    Set Disposition
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-500 text-xs">
                                <span class="material-symbols-outlined icon-2xl text-emerald-400 block mb-2">task_alt</span>
                                <span class="font-semibold text-slate-700 block">All inspection lots have been validated!</span>
                                No pending disposition items. Queue is clear.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Recent Disposition Decisions -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400 icon-md">history_edu</span>
                Recent Disposition Decisions
            </h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentValidations as $rv)
                <div class="px-5 py-3.5 flex items-center justify-between text-xs hover:bg-slate-50/60 transition">
                    <div class="flex items-center gap-3">
                        <div>
                            <span class="font-bold text-slate-900">{{ $rv->lot_number }}</span>
                            <span class="text-slate-400 mx-1.5">·</span>
                            <span class="text-slate-600">{{ $rv->part->part_name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-slate-400 font-mono">{{ $rv->updated_at->format('d/m/Y') }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            {{ $rv->disposition === 'accept' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                               ($rv->disposition === 'return_to_vendor' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                               ($rv->disposition === 'scrap' ? 'bg-purple-50 text-purple-700 border border-purple-200' :
                               ($rv->disposition === 'rework' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200'))) }}">
                            {{ str_replace('_', ' ', $rv->disposition ?? 'Pending') }}
                        </span>
                        <a href="{{ route('inspections.show', $rv->id) }}" class="text-slate-400 hover:text-indigo-600 transition" title="View Detail">
                            <span class="material-symbols-outlined icon-sm">visibility</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-xs text-slate-400">
                    No disposition decisions recorded yet.
                </div>
            @endforelse
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('spvTrendChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Approved',
                    data: @json($chartApproved),
                    borderColor: 'rgba(16,185,129,1)',
                    backgroundColor: 'rgba(16,185,129,0.12)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: 'rgba(16,185,129,1)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Pending',
                    data: @json($chartPending),
                    borderColor: 'rgba(245,158,11,1)',
                    backgroundColor: 'rgba(245,158,11,0.10)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: 'rgba(245,158,11,1)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Rejected',
                    data: @json($chartRejected),
                    borderColor: 'rgba(239,68,68,1)',
                    backgroundColor: 'rgba(239,68,68,0.10)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: 'rgba(239,68,68,1)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 11, family: 'Inter' }, boxWidth: 12, padding: 14, usePointStyle: true }
                },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11, family: 'Inter' } } },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { size: 11, family: 'Inter' } },
                    grid: { color: 'rgba(148,163,184,0.12)' }
                }
            }
        }
    });
})();
</script>
@endsection