@extends('layouts.app')
@section('title', 'Dashboard - Logbook Digital IQC Portal')
@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-12">

    <!-- Top Greeting & Actor Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 sm:p-7 border border-slate-200/70 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[30px]">
                    @if($user->isSupervisor()) gavel
                    @elseif($user->isAdmin()) admin_panel_settings
                    @elseif($user->isMasterData()) warehouse
                    @else engineering @endif
                </span>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        IQC Digital Logbook Portal
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border 
                        @if($user->role === 'inspector_ee') bg-emerald-50 text-emerald-700 border-emerald-200
                        @elseif($user->role === 'inspector_automotive') bg-teal-50 text-teal-700 border-teal-200
                        @elseif($user->role === 'inspector_wire_harness') bg-purple-50 text-purple-700 border-purple-200
                        @elseif($user->role === 'supervisor') bg-amber-50 text-amber-700 border-amber-200
                        @elseif($user->role === 'master_data') bg-blue-50 text-blue-700 border-blue-200
                        @else bg-indigo-50 text-indigo-700 border-indigo-200 @endif">
                        {{ $user->role_label }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        Shift {{ $user->shift ?? '1' }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
                    Welcome back, <span class="font-bold text-slate-800">{{ $user->name }}</span>. 
                    @if($user->isSupervisor())
                        Monitor Pareto defect recapitulation, commodity pass rates, and evaluate MRB dispositions.
                    @elseif($user->isMasterData())
                        Manage inbound material (GRN) queues and component & supplier master data.
                    @elseif($user->isAdmin())
                        Manage user accounts and monitor system integrity via the Audit Trail.
                    @else
                        Perform lot barcode scanning with commodity Poka-Yoke validation and record sample measurements.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0 flex-wrap">
            @if($user->isMasterData() || $user->isAdmin())
            <a href="{{ route('receivings.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all duration-200">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>Register GRN</span>
            </a>
            @endif

            @if($user->isInspector())
            <a href="{{ route('logbooks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all duration-200">
                <span class="material-symbols-outlined text-[18px]">assignment_add</span>
                <span>Record Logbook</span>
            </a>
            @endif

            @if($user->isSupervisor())
            <a href="{{ route('supervisor.mrb.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition-all duration-200">
                <span class="material-symbols-outlined text-[18px]">gavel</span>
                <span>MRB Disposition ({{ $mrbPendingCount }})</span>
            </a>
            @endif

            <button type="button" onclick="window.location.reload()" title="Refresh" class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 transition">
                <span class="material-symbols-outlined text-[18px]">sync</span>
            </button>
        </div>
    </div>

    <!-- Alert Banner if Inspector has Revisions -->
    @if($user->isInspector() && count($myRevisionQueue) > 0)
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 shadow-sm flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">warning</span>
            </div>
            <div>
                <h4 class="text-sm font-bold text-amber-900">Attention: You have {{ count($myRevisionQueue) }} logbook(s) requiring revision!</h4>
                <p class="text-xs text-amber-700 mt-0.5">The supervisor has requested revisions on your lot measurement. Please review and update immediately.</p>
            </div>
        </div>
        <a href="{{ route('logbooks.edit', $myRevisionQueue[0]->id) }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition shrink-0">
            Revise Now &rarr;
        </a>
    </div>
    @endif

    <!-- Key Performance Indicators (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- KPI 1: Kedatangan Material & Antrean -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-bold uppercase tracking-wider">Inbound Queue</span>
                <span class="material-symbols-outlined text-indigo-600">conveyor_belt</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900">{{ $waitingQueue }}</span>
                <span class="text-xs text-slate-500 font-semibold">Lots Waiting</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Total Inbound: <b class="text-slate-800">{{ $totalInbound }}</b></span>
                <span class="text-indigo-600 font-bold">{{ $inProgress }} In Inspection</span>
            </div>
        </div>

        <!-- KPI 2: Logbook Selesai & Pass Rate -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-bold uppercase tracking-wider">Quality Pass Rate</span>
                <span class="material-symbols-outlined text-emerald-600">verified</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-600">{{ $passRate }}%</span>
                <span class="text-xs text-slate-500 font-semibold">Inspection Yield</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Accepted: <b class="text-emerald-700">{{ $acceptedCount }}</b></span>
                <span class="text-slate-500">Total Lots: <b class="text-slate-800">{{ $totalLogbooks }}</b></span>
            </div>
        </div>

        <!-- KPI 3: Menunggu Penetapan MRB -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-bold uppercase tracking-wider">MRB / NEST Queue</span>
                <span class="material-symbols-outlined text-amber-500">gavel</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-600">{{ $mrbPendingCount }}</span>
                <span class="text-xs text-slate-500 font-semibold">Awaiting Review</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-amber-700 font-bold">{{ $revisionsCount }} Needs Revision</span>
                <span class="text-rose-600 font-bold">{{ $rejectedCount }} RTV / Scrap</span>
            </div>
        </div>

        <!-- KPI 4: Queue Aging Status -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-bold uppercase tracking-wider">Queue Aging</span>
                <span class="material-symbols-outlined text-blue-600">timer</span>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                    {{ $normalAgingCount }} Normal
                </span>
                <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">
                    {{ $prioritasAgingCount }} Priority
                </span>
                <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800 {{ $overdueAgingCount > 0 ? 'animate-pulse' : '' }}">
                    {{ $overdueAgingCount }} Overdue
                </span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-[11px] text-slate-500">
                SOP Benchmark: Overdue if queue &gt; 24 hours
            </div>
        </div>
    </div>

    <!-- Inspector Poka-Yoke Quick Scanner Widget (UC-03) -->
    @if($user->isInspector())
    <div class="bg-gradient-to-r from-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-7 text-white shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 max-w-3xl">
            <div class="flex items-center gap-2 text-indigo-300 text-xs font-bold uppercase tracking-wider">
                <span class="material-symbols-outlined text-[16px]">qr_code_scanner</span>
                <span>UC-03: Scan Lot Barcode & Poka-Yoke Validation</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black mt-2 tracking-tight">
                Scan Inbound Lot / GRN Number
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">
                The Poka-Yoke mistake-proofing guard validates whether the material commodity matches your authorized inspector qualification ({{ $user->role_label }}). Mismatched commodities are automatically blocked and logged in the Audit Trail.
            </p>

            <form action="{{ route('logbooks.scan') }}" method="GET" class="mt-5 flex flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400">barcode_scanner</span>
                    <input type="text" name="code" required autofocus
                        placeholder="Scan lot barcode or type GRN Number (e.g. GRN-202611-001 or LOT-STM-2610A)..."
                        class="w-full pl-12 pr-4 py-3.5 rounded-2xl text-slate-900 placeholder:text-slate-400 text-sm font-mono border-0 focus:ring-4 focus:ring-indigo-400 shadow-inner">
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-sm shrink-0 transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/30">
                    <span>Validate Lot & Proceed</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Supervisor Section: Pareto Chart & 7-Day Trend (UC-11) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Defect Pareto Analysis Chart (Chart.js) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                            <span class="material-symbols-outlined text-[16px]">bar_chart</span>
                            <span>IQC Quality Analytics</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mt-1">Defect Pareto Chart (80/20 Rule)</h3>
                        <p class="text-xs text-slate-500">Defect frequency distribution and cumulative percentage.</p>
                    </div>
                </div>

                <div class="mt-6 relative h-64 w-full">
                    <canvas id="paretoChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Juran 80/20 Rule: Prioritize root causes driving the highest defect frequency.</span>
                <span class="font-bold text-indigo-600">Total Defects: {{ array_sum($paretoBarData) }} pcs</span>
            </div>
        </div>

        <!-- 7-Day Trend Line Chart -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                            <span class="material-symbols-outlined text-[16px]">trending_up</span>
                            <span>Last 7 Days Trend</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mt-1">Daily Inspection Output Volume</h3>
                        <p class="text-xs text-slate-500">Comparison of accepted lots versus non-conforming / MRB lots.</p>
                    </div>
                </div>

                <div class="mt-6 relative h-64 w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Accepted
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 ml-2"></span> Non-Conformance / MRB
                </span>
                <span>Updated in real-time</span>
            </div>
        </div>
    </div>

    <!-- Pass Rate per Commodity & Urgent Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Pass Rate per Commodity -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                <span class="material-symbols-outlined text-[16px]">pie_chart</span>
                <span>Commodity Pass Rate</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 mt-1">Quality Acceptance Yield</h3>
            <p class="text-xs text-slate-500 mt-0.5">Aggregated across the 3 IQC commodity divisions.</p>

            <div class="mt-6 space-y-5">
                @foreach($commodityStats as $c)
                <div>
                    <div class="flex justify-between items-center text-xs mb-1.5">
                        <span class="font-bold text-slate-800">{{ $c['label'] }}</span>
                        <span class="font-mono font-bold 
                            {{ $c['pass_rate'] >= 90 ? 'text-emerald-600' : ($c['pass_rate'] >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                            {{ $c['pass_rate'] }}% ({{ $c['accepted'] }}/{{ $c['total'] }} Lots)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                            @if($c['key'] === 'ee_part') bg-emerald-500
                            @elseif($c['key'] === 'automotive') bg-teal-500
                            @else bg-purple-500 @endif" 
                            style="width: {{ $c['pass_rate'] }}%;"></div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 text-xs text-indigo-900">
                <div class="font-bold flex items-center gap-1.5 mb-1">
                    <span class="material-symbols-outlined text-[16px] text-indigo-600">verified_user</span>
                    MIL-STD-105E AQL Standard
                </div>
                The IQC department target pass rate is 95.0% for active ICs and 98.0% for passive components.
            </div>
        </div>

        <!-- Urgent MRB Queue Table (2 columns wide) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-amber-600 text-xs font-bold uppercase tracking-wider">
                            <span class="material-symbols-outlined text-[16px]">priority_high</span>
                            <span>Urgent Review</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mt-1">Material Review Board (MRB) Pending Queue</h3>
                        <p class="text-xs text-slate-500">Non-conforming lots submitted via NEST or containing out-of-specification measurements.</p>
                    </div>
                    <a href="{{ route('supervisor.mrb.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                        View All &rarr;
                    </a>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="pb-3">GRN & Lot No</th>
                                <th class="pb-3">Component / Part</th>
                                <th class="pb-3">Inspector</th>
                                <th class="pb-3">Reason / NEST</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($urgentMrbQueue as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 font-mono font-bold text-indigo-600">
                                    {{ $item->receiving->no_grn_iqa ?? '-' }}
                                    <span class="block text-[10px] text-slate-500 font-normal">Lot: {{ $item->receiving->no_lot ?? '-' }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="font-bold text-slate-800">{{ $item->receiving->part->kode_part ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-500 truncate max-w-[140px]">{{ $item->receiving->part->nama_part ?? '-' }}</div>
                                </td>
                                <td class="py-3 text-slate-600">
                                    {{ $item->inspector->name ?? '-' }}
                                </td>
                                <td class="py-3">
                                    @if($item->nestProcess)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            NEST: {{ Str::limit($item->nestProcess->kronologi_masalah, 25) }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            Out-of-Spec
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('supervisor.mrb.show', $item->id) }}" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-xs transition">
                                        Evaluate
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-[28px] text-emerald-400 block mb-1">task_alt</span>
                                    No lots currently awaiting MRB disposition. All inspection queues are clear.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Section: Recent Audit Trail Feed (UC-15) -->
    @if($user->isAdmin())
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                    <span class="material-symbols-outlined text-[16px]">shield_lock</span>
                    <span>System Security & Integrity (UC-15)</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mt-1">Recent Real-Time Audit Trail</h3>
                <p class="text-xs text-slate-500">Immutable audit log tracking user actions, critical changes, and Poka-Yoke violations.</p>
            </div>
            <a href="{{ route('admin.audit-trails.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                View Full Logs &rarr;
            </a>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="pb-3">Timestamp</th>
                        <th class="pb-3">User & IP</th>
                        <th class="pb-3">Action</th>
                        <th class="pb-3">Data Object</th>
                        <th class="pb-3">Details / Outcome</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAudits as $audit)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 font-mono text-slate-500 whitespace-nowrap">
                            {{ $audit->waktu ? $audit->waktu->format('Y-m-d H:i:s') : '-' }}
                        </td>
                        <td class="py-2.5">
                            <span class="font-bold text-slate-800">{{ $audit->user->name ?? 'System' }}</span>
                            <span class="block text-[10px] text-slate-400 font-mono">{{ $audit->alamat_ip }}</span>
                        </td>
                        <td class="py-2.5">
                            @if($audit->aksi === 'POKA_YOKE_REJECTED')
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-300 animate-pulse">
                                    ⚠️ POKA-YOKE BLOCKED
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $audit->aksi }}
                                </span>
                            @endif
                        </td>
                        <td class="py-2.5 text-slate-700 max-w-[200px] truncate">
                            {{ $audit->objek_data }}
                        </td>
                        <td class="py-2.5 text-slate-500 max-w-[250px] truncate">
                            {{ $audit->nilai_sesudah ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-slate-400">No audit trail records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<!-- Chart.js Library from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Pareto Chart
    const paretoCtx = document.getElementById('paretoChart').getContext('2d');
    const paretoLabels = @json($paretoLabels);
    const paretoBarData = @json($paretoBarData);
    const paretoLineData = @json($paretoLineData);

    new Chart(paretoCtx, {
        type: 'bar',
        data: {
            labels: paretoLabels,
            datasets: [
                {
                    label: 'Cumulative %',
                    data: paretoLineData,
                    type: 'line',
                    borderColor: '#f43f5e',
                    borderWidth: 2,
                    pointBackgroundColor: '#f43f5e',
                    pointRadius: 4,
                    yAxisID: 'y1',
                    order: 1
                },
                {
                    label: 'Defect Qty (pcs)',
                    data: paretoBarData,
                    backgroundColor: 'rgba(99, 102, 241, 0.75)',
                    hoverBackgroundColor: '#4f46e5',
                    borderRadius: 8,
                    yAxisID: 'y',
                    order: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Defect Frequency (pcs)', font: { size: 10 } },
                    grid: { color: 'rgba(226, 232, 240, 0.5)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    min: 0,
                    max: 100,
                    title: { display: true, text: 'Cumulative (%)', font: { size: 10 } },
                    grid: { drawOnChartArea: false },
                    ticks: { callback: function(val) { return val + '%'; } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, maxRotation: 25 }
                }
            },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });

    // 2. 7-Day Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendDates = @json($trendDates);
    const trendAccepted = @json($trendAccepted);
    const trendRejected = @json($trendRejected);

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendDates,
            datasets: [
                {
                    label: 'Accepted / Passed',
                    data: trendAccepted,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981'
                },
                {
                    label: 'Defect / MRB',
                    data: trendRejected,
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244, 63, 94, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#f43f5e'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: 'rgba(226, 232, 240, 0.5)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
});
</script>
@endsection
