@extends('layouts.app')

@section('title', 'Administrator Dashboard - Logbook Digital IQC')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Welcome Header for Admin -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-rose-50 text-rose-700">
                    <span class="material-symbols-outlined icon-xl">manage_accounts</span>
                </span>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">IQC System Administrator</h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Welcome, <span class="font-semibold text-slate-700">{{ Auth::user()->name ?? 'Administrator' }}</span> — Full system access and user management.
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition">
                <span class="material-symbols-outlined icon-sm">manage_accounts</span>
                User Management
            </a>
            <a href="{{ route('incoming-materials.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                <span class="material-symbols-outlined icon-sm">local_shipping</span>
                GRN Tracking
            </a>
            <a href="{{ route('master-data.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                <span class="material-symbols-outlined icon-sm">dns</span>
                Master Parts
            </a>
            <a href="{{ route('instruments.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                <span class="material-symbols-outlined icon-sm">speed</span>
                Instruments
            </a>
        </div>
    </div>

    {{-- ══ Full-Width Chart Section ══════════════════════════════════════════ --}}
    <div class="bg-white/90 border border-slate-200 rounded-2xl shadow-xl overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 pt-5 pb-4
                    border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600">
                    <span class="material-symbols-outlined icon-md">show_chart</span>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">7-Day Inspection Trend</h2>
                    <p class="text-xs text-slate-400 mt-0.5">System-wide daily inspection outcomes.</p>
                </div>
            </div>
            <a href="{{ route('incoming-materials.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600
                      hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5
                      rounded-xl transition shrink-0">
                <span class="material-symbols-outlined icon-sm">local_shipping</span>
                GRN Tracking
            </a>
        </div>
        <div class="px-6 py-5">
            <canvas id="adminTrendChart" height="65"></canvas>
        </div>
    </div>
    {{-- ══ End Chart Section ════════════════════════════════════════════════ --}}

    <!-- Section 1: KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card: Total Users -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-400 to-indigo-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Registered Users</span>
                    <h3 class="text-3xl font-extrabold text-indigo-600 mt-1">
                        {{ $totalUsers }}
                    </h3>
                </div>
                <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <span class="material-symbols-outlined text-2xl">group</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ $inspectorCount }} Inspector · {{ $supervisorCount }} Supervisor</span>
                <a href="{{ route('admin.users.index') }}" class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200 hover:bg-indigo-100 transition">
                    Manage →
                </a>
            </div>
        </div>

        <!-- Card: Total Inspections -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Inspections</span>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $totalInspections }}</h3>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                    <span class="material-symbols-outlined text-2xl">fact_check</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ $approvedCount }} Approved · {{ $rejectedCount }} Rejected</span>
                <a href="{{ route('inspections.index') }}" class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 hover:bg-emerald-100 transition">
                    Logbook →
                </a>
            </div>
        </div>

        <!-- Card: Pending Review -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pending SPV Review</span>
                    <h3 class="text-3xl font-extrabold {{ $pendingCount > 0 ? 'text-amber-600' : 'text-slate-400' }} mt-1">{{ $pendingCount }}</h3>
                </div>
                <div class="p-3 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100">
                    <span class="material-symbols-outlined text-2xl">pending_actions</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Awaiting supervisor disposition</span>
                @if($pendingCount > 0)
                    <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Action Needed</span>
                @else
                    <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Clear ✓</span>
                @endif
            </div>
        </div>

        <!-- Card: Lots Received -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-400 to-slate-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total GRN Received</span>
                    <h3 class="text-3xl font-extrabold text-slate-700 mt-1">{{ $totalLotsArrived }}</h3>
                </div>
                <div class="p-3 rounded-2xl bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="material-symbols-outlined text-2xl">inventory_2</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Incoming arrivals backlog</span>
                <a href="{{ route('incoming-materials.index') }}" class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200 hover:bg-indigo-100 transition">
                    Track GRN →
                </a>
            </div>
        </div>
    </div>

    <!-- Section 2: Quick Navigation Tiles -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- User Management -->
        <a href="{{ route('admin.users.index') }}" class="group bg-white/90 border border-indigo-100 hover:border-indigo-400 rounded-2xl p-6 shadow-xl flex items-center gap-5 transition-all duration-200 hover:shadow-indigo-100">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 group-hover:bg-indigo-700 text-white flex items-center justify-center shadow-lg transition">
                <span class="material-symbols-outlined text-[28px]">manage_accounts</span>
            </div>
            <div>
                <div class="font-bold text-slate-900 text-sm">User Account Management</div>
                <div class="text-xs text-slate-400 mt-0.5">Create, edit, disable inspector & supervisor credentials</div>
                <div class="text-xs font-semibold text-indigo-600 mt-1.5">{{ $totalUsers }} registered users →</div>
            </div>
        </a>

        <!-- Master Data -->
        <a href="{{ route('master-data.index') }}" class="group bg-white/90 border border-emerald-100 hover:border-emerald-400 rounded-2xl p-6 shadow-xl flex items-center gap-5 transition-all duration-200 hover:shadow-emerald-100">
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 group-hover:bg-emerald-700 text-white flex items-center justify-center shadow-lg transition">
                <span class="material-symbols-outlined text-[28px]">dns</span>
            </div>
            <div>
                <div class="font-bold text-slate-900 text-sm">Master Part Library (EE)</div>
                <div class="text-xs text-slate-400 mt-0.5">Register and manage electronic component part drawings</div>
                <div class="text-xs font-semibold text-emerald-600 mt-1.5">Manage specifications →</div>
            </div>
        </a>

        <!-- Instruments -->
        <a href="{{ route('instruments.index') }}" class="group bg-white/90 border border-amber-100 hover:border-amber-400 rounded-2xl p-6 shadow-xl flex items-center gap-5 transition-all duration-200 hover:shadow-amber-100">
            <div class="w-14 h-14 rounded-2xl bg-amber-600 group-hover:bg-amber-700 text-white flex items-center justify-center shadow-lg transition">
                <span class="material-symbols-outlined text-[28px]">speed</span>
            </div>
            <div>
                <div class="font-bold text-slate-900 text-sm">Measurement Instruments</div>
                <div class="text-xs text-slate-400 mt-0.5">Calibration registry, expiry tracking, and verification</div>
                <div class="text-xs font-semibold text-amber-600 mt-1.5">Manage instruments →</div>
            </div>
        </a>
    </div>

    <!-- Section 3: Recent Inspection Activity -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 icon-md">history_edu</span>
                Recent Inspection Activity
            </h2>
            <a href="{{ route('inspections.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Lot / PO</th>
                        <th class="py-3 px-4">Part</th>
                        <th class="py-3 px-4">Inspector</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentInspections as $ins)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $ins->inspection_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 font-mono">
                                <div class="font-semibold text-slate-800">{{ $ins->lot_number }}</div>
                                <div class="text-[10px] text-slate-400">{{ $ins->po_number }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-700">{{ $ins->part->part_name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $ins->part->part_number ?? '' }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $ins->inspector->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $ins->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                       ($ins->status === 'rejected' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                                       ($ins->status === 'pending_approval' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                                       'bg-slate-100 text-slate-500 border border-slate-200')) }}">
                                    {{ str_replace('_', ' ', $ins->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('inspections.show', $ins->id) }}" class="text-slate-400 hover:text-indigo-600 transition" title="View">
                                    <span class="material-symbols-outlined icon-sm">visibility</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400 text-xs">
                                <span class="material-symbols-outlined icon-2xl text-slate-300 block mb-2">inbox</span>
                                No inspection records found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('adminTrendChart');
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