@extends('layouts.app')
@section('title', 'Dashboard - Logbook Digital IQC Portal')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Executive Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Executive Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring mutu incoming material, throughput inspeksi, dan antrean real-time.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if($user->isMasterData() || $user->isAdmin())
            <a href="{{ route('receivings.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                <span>Register GRN</span>
            </a>
            @endif

            @if($user->isInspector())
            <a href="{{ route('logbooks.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                <span class="material-symbols-outlined text-[16px]">add_task</span>
                <span>Mulai Inspeksi</span>
            </a>
            @endif

            @if($user->isSupervisor())
            <a href="{{ route('supervisor.mrb.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs transition">
                <span class="material-symbols-outlined text-[16px]">gavel</span>
                <span>Review MRB ({{ $mrbPendingCount }})</span>
            </a>
            @endif

            <button type="button" onclick="window.location.reload()" title="Refresh Data" class="p-2 rounded-lg bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 transition">
                <span class="material-symbols-outlined text-[18px]">refresh</span>
            </button>
        </div>
    </div>

    <!-- Alert Banner if Inspector has Revisions -->
    @if($user->isInspector() && count($myRevisionQueue) > 0)
    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="material-symbols-outlined text-[20px] text-amber-600">warning</span>
            <span><b>Perhatian:</b> Terdapat {{ count($myRevisionQueue) }} logbook yang memerlukan revisi dari Supervisor.</span>
        </div>
        <a href="{{ route('logbooks.edit', $myRevisionQueue[0]->id) }}" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold transition shrink-0">
            Perbaiki Sekarang &rarr;
        </a>
    </div>
    @endif

    <!-- Key Performance Indicators (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Inbound Queue -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold">
                <span>INBOUND QUEUE</span>
                <span class="material-symbols-outlined text-[18px]">conveyor_belt</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-slate-900 tracking-tight">{{ $waitingQueue }}</span>
                <span class="text-xs text-slate-500 font-medium">Lots Antre</span>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Total Inbound: <b class="text-slate-800">{{ $totalInbound }}</b></span>
                <span class="text-blue-600 font-medium">{{ $inProgress }} proses</span>
            </div>
        </div>

        <!-- KPI 2: Quality Pass Rate -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold">
                <span>QUALITY ACCEPTANCE</span>
                <span class="material-symbols-outlined text-[18px] text-emerald-600">verified</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-600 tracking-tight">{{ $acceptedCount }}</span>
                <span class="text-xs text-slate-500 font-medium">Lots Lolos</span>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Pass Rate: <b class="text-emerald-700 font-bold">{{ $passRate }}%</b></span>
                <span>Total: <b class="text-slate-800">{{ $totalLogbooks }}</b></span>
            </div>
        </div>

        <!-- KPI 3: MRB Queue -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold">
                <span>MRB & NEST ESCALATION</span>
                <span class="material-symbols-outlined text-[18px] text-amber-500">gavel</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-amber-600 tracking-tight">{{ $mrbPendingCount }}</span>
                <span class="text-xs text-slate-500 font-medium">Butuh Review</span>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>{{ $revisionsCount }} Perlu Revisi</span>
                <span class="text-rose-600 font-semibold">{{ $rejectedCount }} RTV / Scrap</span>
            </div>
        </div>

        <!-- KPI 4: Queue Aging -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs font-semibold">
                <span>QUEUE AGING</span>
                <span class="material-symbols-outlined text-[18px] text-slate-400">schedule</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold {{ $overdueAgingCount > 0 ? 'text-rose-600' : 'text-slate-900' }} tracking-tight">{{ $overdueAgingCount }}</span>
                <span class="text-xs text-slate-500 font-medium">Lots Overdue (&gt;24h)</span>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>{{ $normalAgingCount }} Normal (&lt;12h)</span>
                <span>{{ $prioritasAgingCount }} Prioritas</span>
            </div>
        </div>
    </div>

    <!-- Inspector Quick Barcode Scanner -->
    @if($user->isInspector())
    <div class="bg-slate-900 rounded-xl p-4 text-white border border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white shrink-0">
                <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-white tracking-wide uppercase">Quick Lot Barcode Scanner</h3>
                <p class="text-[11px] text-slate-400">Scan barcode atau ketik nomor GRN untuk membuka form inspeksi.</p>
            </div>
        </div>

        <form action="{{ route('logbooks.scan') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto md:min-w-[420px]">
            <div class="relative flex-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px]">barcode_scanner</span>
                <input type="text" name="code" required autofocus
                    placeholder="Scan barcode / no. GRN (contoh: GRN-202611-001)..."
                    class="w-full pl-9 pr-3 py-2 text-xs font-mono rounded-lg bg-slate-800 border-slate-700 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shrink-0 flex items-center gap-1.5">
                <span>Periksa</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </button>
        </form>
    </div>
    @endif

    <!-- Material Flow & Throughput Monitoring Section -->
    <!-- Material Flow & Throughput Section -->
    <div class="space-y-4">
        <!-- Section Controls & Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Material Flow & Quality Throughput</h2>
                <p class="text-xs text-slate-500">Pergerakan material harian (inbound vs outbound) dan analisis pareto cacat.</p>
            </div>
            <!-- Tab Buttons -->
            <div class="flex items-center gap-1 p-1 rounded-lg bg-slate-100 border border-slate-200 self-start sm:self-auto">
                <button type="button" id="tabFlowBtn" onclick="switchDashboardTab('flow')" 
                    class="px-3 py-1.5 rounded-md text-xs font-semibold bg-white text-slate-900 shadow-xs border border-slate-200/80 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-blue-600">show_chart</span>
                    <span>Material Flow</span>
                </button>
                <button type="button" id="tabParetoBtn" onclick="switchDashboardTab('pareto')" 
                    class="px-3 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[15px] text-slate-400">bar_chart</span>
                    <span>Defect Pareto</span>
                </button>
            </div>
        </div>

        <!-- VIEW 1: Material Flow & Lifecycle Donut -->
        <div id="viewFlow" class="grid grid-cols-1 lg:grid-cols-3 gap-6 transition-all duration-300">
            <!-- Main Flow Chart (2 Cols) -->
            <div class="lg:col-span-2 bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-slate-400">calendar_month</span>
                                Periode: {{ $flowDates[0] ?? '' }} – {{ end($flowDates) ?? '' }} (7 Hari Terakhir)
                            </span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1">Tren Arus Material (Inbound vs Outbound vs Backlog)</h3>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-medium text-slate-600 shrink-0">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> Inbound
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Outbound (Cleared)
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Backlog
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 relative h-64 w-full">
                        <canvas id="materialFlowChart"></canvas>
                    </div>
                </div>

                <!-- Footer Summary KPI Cards -->
                <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-3 gap-3 text-center">
                    <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-200/80">
                        <span class="text-[10px] font-bold uppercase text-slate-500 block">Total Inbound</span>
                        <span class="text-sm font-bold text-slate-900 font-mono">{{ $sumInboundLots }} Lots</span>
                        <span class="text-[10px] text-slate-400 block">({{ number_format($sumInboundQty) }} pcs)</span>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-200/80">
                        <span class="text-[10px] font-bold uppercase text-emerald-700 block">Total Rilis (Cleared)</span>
                        <span class="text-sm font-bold text-emerald-700 font-mono">{{ $sumOutboundLots }} Lots</span>
                        <span class="text-[10px] text-slate-400 block">({{ number_format($sumOutboundQty) }} pcs)</span>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-200/80">
                        <span class="text-[10px] font-bold uppercase text-blue-700 block">Clearance Rate</span>
                        <span class="text-sm font-bold text-blue-700 font-mono">{{ $flowClearanceRate }}%</span>
                        <span class="text-[10px] text-slate-400 block">Rasio Penyelesaian</span>
                    </div>
                </div>
            </div>

            <!-- Inbound Status Lifecycle (Donut Chart) -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold text-slate-900">Distribusi Status Inbound</h3>
                        <span class="text-[11px] text-slate-400 font-medium">Per: {{ now()->translatedFormat('d M Y') }}</span>
                    </div>

                    <div class="mt-4 relative h-48 w-full flex items-center justify-center">
                        <canvas id="statusDonutChart"></canvas>
                        <!-- Center Dynamic Gauge Card in Donut -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-20 h-20 rounded-full bg-slate-50 border border-slate-200 flex flex-col items-center justify-center p-2 text-center shadow-xs">
                                <span class="text-xl font-bold text-slate-900 font-mono leading-none">{{ $totalInbound }}</span>
                                <span class="text-[9px] font-semibold uppercase text-slate-400 tracking-wider mt-1">Total Lots</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Breakdown List -->
                    <div class="mt-4 divide-y divide-slate-100 text-xs">
                        <div class="flex items-center justify-between py-2">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Accepted / Released
                            </span>
                            <span class="font-bold text-slate-900 font-mono">{{ $statusBreakdown['released'] }} Lots <span class="text-slate-400 font-normal">({{ $totalInbound > 0 ? round(($statusBreakdown['released'] / $totalInbound) * 100, 1) : 0 }}%)</span></span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> Under Inspection (WIP)
                            </span>
                            <span class="font-bold text-slate-900 font-mono">{{ $statusBreakdown['in_inspection'] }} Lots <span class="text-slate-400 font-normal">({{ $totalInbound > 0 ? round(($statusBreakdown['in_inspection'] / $totalInbound) * 100, 1) : 0 }}%)</span></span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Waiting in Queue
                            </span>
                            <span class="font-bold text-slate-900 font-mono">{{ $statusBreakdown['waiting'] }} Lots <span class="text-slate-400 font-normal">({{ $totalInbound > 0 ? round(($statusBreakdown['waiting'] / $totalInbound) * 100, 1) : 0 }}%)</span></span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="w-2 h-2 rounded-full bg-orange-500"></span> Pending MRB Review
                            </span>
                            <span class="font-bold text-slate-900 font-mono">{{ $statusBreakdown['mrb_hold'] }} Lots <span class="text-slate-400 font-normal">({{ $totalInbound > 0 ? round(($statusBreakdown['mrb_hold'] / $totalInbound) * 100, 1) : 0 }}%)</span></span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Rejected / RTV / Scrap
                            </span>
                            <span class="font-bold text-slate-900 font-mono">{{ $statusBreakdown['rejected'] }} Lots <span class="text-slate-400 font-normal">({{ $totalInbound > 0 ? round(($statusBreakdown['rejected'] / $totalInbound) * 100, 1) : 0 }}%)</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW 2: Defect Pareto Analysis (Secondary Tab) -->
        <div id="viewPareto" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-6 transition-all duration-300">
            <!-- Defect Pareto Analysis Chart (Chart.js) -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Analisis Pareto Cacat (80/20 Rule)</h3>
                            <span class="text-[11px] text-slate-400">Periode MTD: {{ now()->startOfMonth()->translatedFormat('d M') }} – {{ now()->translatedFormat('d M Y') }}</span>
                        </div>
                        <span class="font-bold text-xs text-blue-600 font-mono">Total Cacat: {{ array_sum($paretoBarData) }} pcs</span>
                    </div>

                    <div class="mt-4 relative h-64 w-full">
                        <canvas id="paretoChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- 7-Day Trend Line Chart -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Tren Hasil Inspeksi (7 Hari)</h3>
                            <span class="text-[11px] text-slate-400">Periode: {{ $trendDates[0] ?? '' }} – {{ end($trendDates) ?? '' }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-500">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Accepted</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> MRB / Reject</span>
                        </div>
                    </div>

                    <div class="mt-4 relative h-64 w-full">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commodity Workload & Inbound Ready-for-Inspection Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Card 1: Commodity Workload & Quality Yield Summary -->
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Workload & Yield per Komoditas</h3>
                    <span class="text-[11px] text-slate-400">MTD</span>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach($commodityStats as $c)
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200/80">
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <span class="font-semibold text-slate-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full 
                                    @if($c['key'] === 'ee_part') bg-emerald-500
                                    @elseif($c['key'] === 'automotive') bg-blue-500
                                    @else bg-purple-500 @endif"></span>
                                {{ $c['label'] }}
                            </span>
                            <span class="font-mono font-bold 
                                {{ $c['pass_rate'] >= 90 ? 'text-emerald-600' : ($c['pass_rate'] >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                                {{ $c['pass_rate'] }}% Pass
                            </span>
                        </div>
                        
                        <!-- 3-Metric Inbound / Cleared / Queue Grid -->
                        <div class="grid grid-cols-3 gap-1.5 text-center my-2 text-xs">
                            <div class="bg-white py-1 px-1 rounded border border-slate-200/80">
                                <span class="text-[9px] text-slate-400 font-medium block">Inbound</span>
                                <span class="font-bold text-slate-800 font-mono">{{ $c['inbound'] }}</span>
                            </div>
                            <div class="bg-white py-1 px-1 rounded border border-slate-200/80">
                                <span class="text-[9px] text-emerald-600 font-medium block">Cleared</span>
                                <span class="font-bold text-emerald-600 font-mono">{{ $c['completed'] }}</span>
                            </div>
                            <div class="bg-white py-1 px-1 rounded border border-slate-200/80">
                                <span class="text-[9px] text-amber-600 font-medium block">Queue</span>
                                <span class="font-bold text-amber-600 font-mono">{{ $c['waiting'] }}</span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden mt-1.5">
                            <div class="h-full rounded-full transition-all duration-500
                                @if($c['key'] === 'ee_part') bg-emerald-500
                                @elseif($c['key'] === 'automotive') bg-blue-500
                                @else bg-purple-500 @endif" 
                                style="width: {{ $c['pass_rate'] }}%;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>Standard: MIL-STD-105E</span>
                <span>Target: &ge; 95%</span>
            </div>
        </div>

        <!-- Card 2: Incoming Materials Ready for Inspection (Inbound Queue) - 2 Cols Wide -->
        <div class="lg:col-span-2 bg-white rounded-xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Antrean Inbound Siap Inspeksi</h3>
                        <p class="text-[11px] text-slate-400">Prioritas FIFO berdasarkan waktu kedatangan dock.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($user->isSupervisor() && $mrbPendingCount > 0)
                        <a href="{{ route('supervisor.mrb.index') }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold hover:bg-amber-100 transition">
                            <span class="material-symbols-outlined text-[14px]">gavel</span>
                            <span>MRB ({{ $mrbPendingCount }})</span>
                        </a>
                        @endif
                        <a href="{{ route('receivings.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                            Semua Inbound &rarr;
                        </a>
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                                <th class="pb-2">GRN / Lot</th>
                                <th class="pb-2">Komponen</th>
                                <th class="pb-2">Supplier</th>
                                <th class="pb-2 text-center">Qty</th>
                                <th class="pb-2">Waktu Masuk</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($incomingBacklogQueue as $rcv)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2.5 font-mono font-semibold text-blue-600">
                                    {{ $rcv->no_grn_iqa }}
                                    <span class="block text-[10px] text-slate-400 font-normal">Lot: {{ $rcv->no_lot ?? '-' }}</span>
                                </td>
                                <td class="py-2.5">
                                    <div class="font-semibold text-slate-800">{{ $rcv->part->kode_part ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[130px]">{{ $rcv->part->nama_part ?? '-' }}</div>
                                </td>
                                <td class="py-2.5 text-slate-600">
                                    <div class="font-medium text-slate-800 text-[11px]">{{ $rcv->supplier->nama_supplier ?? $rcv->supplier->kode_supplier ?? '-' }}</div>
                                </td>
                                <td class="py-2.5 text-center font-mono font-semibold text-slate-800">
                                    {{ number_format($rcv->qty_datang) }}
                                </td>
                                <td class="py-2.5">
                                    <div class="text-slate-700 text-[11px]">{{ $rcv->tanggal_terima ? $rcv->tanggal_terima->format('d M, H:i') : '-' }}</div>
                                    <span class="text-[10px] font-medium {{ $rcv->aging_hours > 24 ? 'text-rose-600 font-bold' : ($rcv->aging_hours > 12 ? 'text-amber-600' : 'text-slate-400') }}">
                                        {{ $rcv->aging_hours }}h ({{ $rcv->aging_status }})
                                    </span>
                                </td>
                                <td class="py-2.5 text-right">
                                    @if($user->isInspector())
                                        @if($user->canInspectCommodity($rcv->part->kategori_komoditas))
                                        <a href="{{ route('logbooks.create', ['receiving_id' => $rcv->id]) }}" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium text-[11px] shadow-xs transition">
                                            <span>Inspeksi</span>
                                        </a>
                                        @else
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-400 text-[10px]" title="Terkunci untuk komoditas ini">
                                            Terkunci
                                        </span>
                                        @endif
                                    @else
                                    <a href="{{ route('logbooks.create', ['receiving_id' => $rcv->id]) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-[11px] transition">
                                        <span>Inspeksi</span>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                                    Tidak ada antrean material yang menunggu inspeksi.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table Footer Legend -->
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> &lt;12h Normal</span>
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> 12-24h Prioritas</span>
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> &gt;24h Overdue</span>
                </div>
                <span>FIFO Aging Benchmark</span>
            </div>
        </div>
    </div>

    <!-- Admin Section: Recent Audit Trail Feed -->
    @if($user->isAdmin())
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Audit Trail Terkini</h3>
                <p class="text-[11px] text-slate-400">Log integritas & aktivitas keamanan sistem.</p>
            </div>
            <a href="{{ route('admin.audit-trails.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                Semua Log &rarr;
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
function switchDashboardTab(tab) {
    const viewFlow = document.getElementById('viewFlow');
    const viewPareto = document.getElementById('viewPareto');
    const tabFlowBtn = document.getElementById('tabFlowBtn');
    const tabParetoBtn = document.getElementById('tabParetoBtn');

    if (tab === 'flow') {
        viewFlow.classList.remove('hidden');
        viewPareto.classList.add('hidden');
        tabFlowBtn.className = "px-3.5 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm transition flex items-center gap-1.5";
        tabParetoBtn.className = "px-3.5 py-1.5 rounded-xl text-xs font-bold bg-transparent hover:bg-slate-200 text-slate-600 transition flex items-center gap-1.5";
    } else {
        viewFlow.classList.add('hidden');
        viewPareto.classList.remove('hidden');
        tabFlowBtn.className = "px-3.5 py-1.5 rounded-xl text-xs font-bold bg-transparent hover:bg-slate-200 text-slate-600 transition flex items-center gap-1.5";
        tabParetoBtn.className = "px-3.5 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm transition flex items-center gap-1.5";
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // ----------------------------------------------------
    // 1. Material Flow & Throughput Chart (Inbound vs Outbound vs Backlog)
    // ----------------------------------------------------
    const flowCtx = document.getElementById('materialFlowChart');
    if (flowCtx) {
        const flowDates = @json($flowDates);
        const flowInboundLots = @json($flowInboundLots);
        const flowInboundQty = @json($flowInboundQty);
        const flowOutboundLots = @json($flowOutboundLots);
        const flowOutboundQty = @json($flowOutboundQty);
        const flowBacklogLots = @json($flowBacklogLots);

        new Chart(flowCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: flowDates,
                datasets: [
                    {
                        label: 'Inbound Lots (Dock Arrivals)',
                        data: flowInboundLots,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#4f46e5',
                        order: 2
                    },
                    {
                        label: 'Outbound Lots (IQC Cleared)',
                        data: flowOutboundLots,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                        order: 1
                    },
                    {
                        label: 'Pending Backlog (WIP Queue)',
                        data: flowBacklogLots,
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#f59e0b',
                        order: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        title: { display: true, text: 'Lot Volume', font: { size: 10, weight: 'bold' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const idx = ctx.dataIndex;
                                const dsLabel = ctx.dataset.label;
                                const val = ctx.parsed.y;
                                if (ctx.datasetIndex === 0) {
                                    return `${dsLabel}: ${val} Lots (${flowInboundQty[idx]?.toLocaleString() ?? 0} pcs)`;
                                } else if (ctx.datasetIndex === 1) {
                                    return `${dsLabel}: ${val} Lots (${flowOutboundQty[idx]?.toLocaleString() ?? 0} pcs)`;
                                }
                                return `${dsLabel}: ${val} Lots`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ----------------------------------------------------
    // 2. Status Peninjauan Kedatangan (Donut Chart)
    // ----------------------------------------------------
    const donutCtx = document.getElementById('statusDonutChart');
    if (donutCtx) {
        const breakdown = @json($statusBreakdown);
        new Chart(donutCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Accepted / Released', 'Under Inspection (WIP)', 'Waiting in Queue', 'Pending MRB Review', 'Rejected / RTV / Scrap'],
                datasets: [{
                    data: [
                        breakdown.released ?? 0,
                        breakdown.in_inspection ?? 0,
                        breakdown.waiting ?? 0,
                        breakdown.mrb_hold ?? 0,
                        breakdown.rejected ?? 0
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#f59e0b',
                        '#f97316',
                        '#ef4444'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    borderRadius: 6,
                    spacing: 4,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const val = ctx.parsed;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${val} Lots (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ----------------------------------------------------
    // 3. Defect Pareto Chart (Tab Analisis Cacat)
    // ----------------------------------------------------
    const paretoCtx = document.getElementById('paretoChart');
    if (paretoCtx) {
        const paretoLabels = @json($paretoLabels);
        const paretoBarData = @json($paretoBarData);
        const paretoLineData = @json($paretoLineData);

        new Chart(paretoCtx.getContext('2d'), {
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
    }

    // ----------------------------------------------------
    // 4. 7-Day Trend Chart
    // ----------------------------------------------------
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        const trendDates = @json($trendDates);
        const trendAccepted = @json($trendAccepted);
        const trendRejected = @json($trendRejected);

        new Chart(trendCtx.getContext('2d'), {
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
    }
});
</script>
@endsection
