@extends('layouts.app')
@section('title', 'GRN Tracking & Receiving Dock Backlog — Digital IQC')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- ── 1. Page Header: Clear Distinction Between Operational Backlog & Master Data ── --}}
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white
                            flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
                    <span class="material-symbols-outlined text-[30px]">local_shipping</span>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                            GRN Tracking & Receiving Dock Backlog
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold
                                     bg-rose-50 text-rose-700 border border-rose-200/80">
                            <span class="material-symbols-outlined text-[13px]">admin_panel_settings</span>
                            Admin Access Only
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold
                                     bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                            Live Material Inflow
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-3xl leading-relaxed">
                        Live receiving log of physical shipments arrived at the dock. Tracks lot batches, GRN barcodes,
                        arrival timestamps, and IQC testing backlogs — 
                        <span class="text-slate-700 font-medium">separate from Master Data</span>
                        (which manages engineering drawings, tolerances, and nominal specs).
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5 flex-wrap shrink-0 self-start lg:self-center">
                <button type="button" onclick="toggleRegisterModal(true)"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700
                               text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition cursor-pointer active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[17px]">add_circle</span>
                    <span>Register Arrived Shipment</span>
                </button>

                <a href="{{ route('incoming-materials.export', request()->query()) }}"
                   title="Export currently filtered backlog as CSV spreadsheet"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200
                          text-slate-700 text-xs font-semibold border border-slate-200 transition">
                    <span class="material-symbols-outlined text-[17px]">download</span>
                    <span>Export CSV</span>
                </a>

                <a href="{{ route('master-data.index') }}"
                   title="Go to Engineering Component Master Data & AQL standards"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100
                          text-purple-700 text-xs font-semibold border border-purple-200 transition">
                    <span class="material-symbols-outlined text-[17px]">category</span>
                    <span>Part Master Data</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Operational Backlog Metrics (4 Metric Cards) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Card 1: Active Backlog (Waiting Inspection) --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Active Backlog
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                        <span class="material-symbols-outlined text-[22px]">pending_actions</span>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-extrabold text-3xl text-amber-600">{{ $counts['waiting'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Lots Awaiting IQC</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                @if($counts['overdue'] > 0)
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                        <span class="material-symbols-outlined text-[13px]">warning</span>
                        {{ $counts['overdue'] }} Overdue (>24h)
                    </span>
                @else
                    <span class="text-emerald-600 font-semibold text-[11px] flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">check_circle</span> SLA Compliant
                    </span>
                @endif
                <span class="text-slate-400 font-mono text-[11px]">{{ number_format($counts['waiting_qty']) }} Pcs</span>
            </div>
        </div>

        {{-- Card 2: Total Shipments Arrived --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-600"></div>
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Total Arrived Lots
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                        <span class="material-symbols-outlined text-[22px]">inventory_2</span>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-extrabold text-3xl text-slate-800">{{ $counts['all'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">GRN Shipments</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Total Received Volume:</span>
                <span class="font-bold text-slate-700 font-mono">{{ number_format($counts['total_qty']) }} Pcs</span>
            </div>
        </div>

        {{-- Card 3: Inspected & Cleared --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Inspected & Cleared
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                        <span class="material-symbols-outlined text-[22px]">verified</span>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-extrabold text-3xl text-emerald-600">{{ $counts['inspected'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Tested Lots</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                @php
                    $clearRate = $counts['all'] > 0 ? round(($counts['inspected'] / $counts['all']) * 100, 1) : 100;
                @endphp
                <span class="text-slate-400">Clearance Rate:</span>
                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[11px]">
                    {{ $clearRate }}% Cleared
                </span>
            </div>
        </div>

        {{-- Card 4: Dock Arrivals Today --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-cyan-500"></div>
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Today's Inflow
                    </span>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                        <span class="material-symbols-outlined text-[22px]">today</span>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="font-extrabold text-3xl text-blue-700">{{ $counts['today'] }}</span>
                    <span class="text-xs font-semibold text-slate-500">Arrived Today</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Receiving Dock:</span>
                <span class="font-semibold text-slate-700">Dock Bay 01 & 02</span>
            </div>
        </div>

    </div>

    {{-- ── 3. Visual Trend & Backlog SLA Bar ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Chart: 7-Day Arrival vs Cleared --}}
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">
                        <span class="material-symbols-outlined text-[18px]">show_chart</span>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">7-Day Dock Arrival Flow & Backlog</h2>
                        <p class="text-xs text-slate-400">Daily incoming shipments vs cleared inspection lots.</p>
                    </div>
                </div>
                <span class="text-xs text-slate-400">Past 7 Days</span>
            </div>
            <canvas id="dockArrivalChart" height="80"></canvas>
        </div>

        {{-- SLA Breakdown & Quick Info --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
                        <span class="material-symbols-outlined text-[18px]">timer</span>
                    </span>
                    <h3 class="text-sm font-bold text-slate-900">Backlog SLA Aging Target</h3>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Incoming materials must be inspected within <strong>24 hours</strong> of dock arrival per standard operating procedure (SOP IQC-001).
                </p>

                {{-- SLA Visual Bar --}}
                <div class="mt-5 space-y-3">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 font-medium">Cleared vs Active</span>
                            <span class="font-bold text-slate-800">{{ $clearRate }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden flex">
                            <div class="bg-emerald-500 h-2.5" style="width: {{ $clearRate }}%"></div>
                            <div class="bg-amber-400 h-2.5" style="width: {{ 100 - $clearRate }}%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Queue Capacity</span>
                            <span class="text-sm font-bold text-slate-800">{{ $counts['waiting'] }} / 50</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Avg Wait Time</span>
                            <span class="text-sm font-bold text-slate-800">~ 3.2 Hours</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span class="flex items-center gap-1 font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Receiving Bay Active
                </span>
                <a href="#quickScanSection" class="text-indigo-600 font-semibold hover:text-indigo-800">
                    Quick Barcode Scan ↓
                </a>
            </div>
        </div>
    </div>

    {{-- ── 4. Filter & Search Controls ── --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">

        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a href="{{ route('incoming-materials.index', array_merge(request()->except(['status', 'page']))) }}"
               class="px-3.5 py-2 rounded-xl font-bold transition flex items-center gap-1.5 shrink-0
                      {{ !request('status') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>All Arrived Items</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request('status') ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-700' }}">
                    {{ $counts['all'] }}
                </span>
            </a>

            <a href="{{ route('incoming-materials.index', array_merge(request()->except('page'), ['status' => 'waiting'])) }}"
               class="px-3.5 py-2 rounded-xl font-bold transition flex items-center gap-1.5 shrink-0
                      {{ request('status') === 'waiting' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' }}">
                <span class="material-symbols-outlined text-[15px]">pending_actions</span>
                <span>Active Backlog (Waiting)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'waiting' ? 'bg-white/25 text-white' : 'bg-amber-200 text-amber-900' }}">
                    {{ $counts['waiting'] }}
                </span>
            </a>

            <a href="{{ route('incoming-materials.index', array_merge(request()->except('page'), ['status' => 'overdue'])) }}"
               class="px-3.5 py-2 rounded-xl font-bold transition flex items-center gap-1.5 shrink-0
                      {{ request('status') === 'overdue' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}">
                <span class="material-symbols-outlined text-[15px]">warning</span>
                <span>Overdue Backlog (>24h)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'overdue' ? 'bg-white/25 text-white' : 'bg-rose-200 text-rose-900' }}">
                    {{ $counts['overdue'] }}
                </span>
            </a>

            <a href="{{ route('incoming-materials.index', array_merge(request()->except('page'), ['status' => 'inspected'])) }}"
               class="px-3.5 py-2 rounded-xl font-bold transition flex items-center gap-1.5 shrink-0
                      {{ request('status') === 'inspected' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' }}">
                <span class="material-symbols-outlined text-[15px]">check_circle</span>
                <span>Inspected & Cleared</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'inspected' ? 'bg-white/25 text-white' : 'bg-emerald-200 text-emerald-900' }}">
                    {{ $counts['inspected'] }}
                </span>
            </a>
        </div>

        {{-- Search & Refinement Bar --}}
        <form method="GET" action="{{ route('incoming-materials.index') }}"
              class="flex flex-col sm:flex-row gap-3 flex-wrap items-center">

            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            {{-- Search Bar --}}
            <div class="relative flex-1 min-w-[240px] w-full">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search GRN, Lot Number, Part Number, Component..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 focus:border-indigo-500
                              focus:ring-4 focus:ring-indigo-500/10 rounded-xl text-xs text-slate-700 placeholder-slate-400 transition">
            </div>

            {{-- Supplier Filter --}}
            <select name="supplier"
                    class="border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-xs text-slate-700
                           focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition min-w-[170px] w-full sm:w-auto">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup }}" {{ request('supplier') === $sup ? 'selected' : '' }}>
                        {{ $sup }}
                    </option>
                @endforeach
            </select>

            {{-- Date Range --}}
            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       title="Arrival Date From"
                       class="border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-xs text-slate-700
                              focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition">
                <span class="text-xs text-slate-400">to</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       title="Arrival Date To"
                       class="border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-xs text-slate-700
                              focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition">
            </div>

            {{-- Submit & Clear --}}
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2 text-xs font-bold
                               flex items-center gap-1.5 transition shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    Filter
                </button>

                @if(request()->hasAny(['search', 'supplier', 'date_from', 'date_to', 'status']))
                    <a href="{{ route('incoming-materials.index') }}"
                       class="border border-slate-200 hover:bg-slate-100 text-slate-600 rounded-xl px-3 py-2
                              text-xs font-semibold flex items-center gap-1 transition">
                        <span class="material-symbols-outlined text-[15px]">close</span>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── 5. Detailed Backlog Table: Arrived Shipments ── --}}
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">

        {{-- Table Banner --}}
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3
                    bg-gradient-to-b from-slate-50/70 to-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <span class="material-symbols-outlined text-[20px]">format_list_bulleted</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">
                        Arrived Material Backlog Records
                    </h2>
                    <p class="text-xs text-slate-400">
                        Physical lots received at dock awaiting or processed through IQC sampling verification.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 font-medium">
                    Showing <span class="font-bold text-slate-700">{{ $materials->firstItem() ?? 0 }}–{{ $materials->lastItem() ?? 0 }}</span>
                    of <span class="font-bold text-slate-700">{{ $materials->total() }}</span> Shipments
                </span>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                        <th class="py-3 px-5">Arrival & Aging SLA</th>
                        <th class="py-3 px-5">GRN Barcode</th>
                        <th class="py-3 px-5">Arrived Component & Supplier</th>
                        <th class="py-3 px-5">Lot Batch</th>
                        <th class="py-3 px-5 text-right">Received Qty</th>
                        <th class="py-3 px-5 text-center">Backlog Status</th>
                        <th class="py-3 px-5 text-center">Inspection Record</th>
                        <th class="py-3 px-5">Receiving Officer</th>
                        <th class="py-3 px-5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($materials as $item)
                    @php
                        $isWaiting = $item->status === 'waiting';
                        $hoursAgo = $item->arrived_at ? $item->arrived_at->diffInHours(now()) : 0;
                        $isOverdue = $isWaiting && $hoursAgo >= 24;
                        $isPriority = $isWaiting && $hoursAgo >= 12 && $hoursAgo < 24;

                        // Poka-Yoke category check
                        $pNum = strtoupper($item->part->part_number ?? '');
                        $pName = strtoupper($item->part->part_name ?? '');
                        $isBlocked = str_starts_with($pNum, 'WH-') || str_starts_with($pNum, 'AUTO-') ||
                                     str_contains($pName, 'HARNESS') || str_contains($pName, 'AUTOMOTIVE');
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition group {{ $isOverdue ? 'bg-rose-50/20' : '' }}">

                        {{-- 1. Arrival Date & SLA Aging --}}
                        <td class="py-3.5 px-5">
                            <div class="font-bold text-slate-800 font-mono text-[11px]">
                                {{ $item->arrived_at ? $item->arrived_at->format('d/m/Y') : '-' }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $item->arrived_at ? $item->arrived_at->format('H:i') . ' WIB' : '-' }}
                            </div>

                            {{-- Aging SLA Badge --}}
                            <div class="mt-1.5">
                                @if($isWaiting)
                                    @if($isOverdue)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                                                     bg-rose-100 text-rose-800 border border-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                                            Overdue ({{ $hoursAgo }}h)
                                        </span>
                                    @elseif($isPriority)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                                                     bg-amber-100 text-amber-800 border border-amber-300">
                                            Priority ({{ $hoursAgo }}h)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium
                                                     bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Fresh (< 12h)
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium
                                                 bg-slate-100 text-slate-600 border border-slate-200">
                                        ✓ Handled
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- 2. GRN Barcode --}}
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-slate-400 text-[16px]">qr_code_2</span>
                                <span class="font-mono font-bold text-slate-900 text-xs">
                                    {{ $item->grn_number }}
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">
                                Dock Bay 01
                            </span>
                        </td>

                        {{-- 3. Component & Supplier --}}
                        <td class="py-3.5 px-5">
                            <div class="font-bold text-slate-800 text-xs leading-tight">
                                {{ $item->part->part_name ?? '—' }}
                            </div>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span class="font-mono text-[10px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-1.5 py-0.2 rounded">
                                    {{ $item->part->part_number ?? '-' }}
                                </span>
                                <span class="text-[10px] text-slate-500 flex items-center gap-1">
                                    <span class="w-1 h-1 rounded-full bg-slate-400"></span>
                                    {{ $item->part->supplier_name ?? 'Vendor' }}
                                </span>
                            </div>
                        </td>

                        {{-- 4. Lot Batch --}}
                        <td class="py-3.5 px-5">
                            <span class="font-mono font-bold text-slate-700 text-xs bg-slate-100 px-2 py-1 rounded-md border border-slate-200">
                                {{ $item->lot_number ?? 'LOT-N/A' }}
                            </span>
                        </td>

                        {{-- 5. Received Quantity --}}
                        <td class="py-3.5 px-5 text-right font-mono">
                            <span class="font-extrabold text-slate-900 text-sm">
                                {{ number_format($item->quantity) }}
                            </span>
                            <span class="text-[10px] text-slate-400 ml-0.5">Pcs</span>
                        </td>

                        {{-- 6. Backlog Status --}}
                        <td class="py-3.5 px-5 text-center">
                            @if($isWaiting)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold
                                             bg-amber-50 text-amber-700 border border-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    In Queue
                                </span>
                                <span class="text-[10px] text-slate-400 block mt-1">
                                    Awaiting Sampling
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold
                                             bg-emerald-50 text-emerald-700 border border-emerald-300">
                                    <span class="material-symbols-outlined text-[12px]">check</span>
                                    Inspected
                                </span>
                            @endif
                        </td>

                        {{-- 7. Inspection Linkage --}}
                        <td class="py-3.5 px-5 text-center">
                            @if($item->inspection_id)
                                <a href="{{ route('inspections.show', $item->inspection_id) }}"
                                   class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    <span class="material-symbols-outlined text-[14px]">description</span>
                                    <span>INS-{{ str_pad($item->inspection_id, 4, '0', STR_PAD_LEFT) }}</span>
                                </a>
                                @if($item->inspection)
                                    @php
                                        $inspStatus = $item->inspection->status;
                                        $statusClass = match($inspStatus) {
                                            'approved' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                                            'rejected' => 'text-rose-700 bg-rose-50 border-rose-200',
                                            'pending_approval' => 'text-amber-700 bg-amber-50 border-amber-200',
                                            default => 'text-slate-600 bg-slate-100 border-slate-200',
                                        };
                                    @endphp
                                    <span class="block mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold uppercase border {{ $statusClass }}">
                                        {{ str_replace('_', ' ', $inspStatus) }}
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-300 font-mono text-[11px]">—</span>
                            @endif
                        </td>

                        {{-- 8. Receiving Officer --}}
                        <td class="py-3.5 px-5 text-slate-600">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-slate-400 text-[14px]">person</span>
                                <span>{{ $item->registrar->name ?? 'Dock Officer' }}</span>
                            </div>
                        </td>

                        {{-- 9. Operational Actions --}}
                        <td class="py-3.5 px-5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($isWaiting)
                                    @if(auth()->user()->role === 'inspector')
                                        @if($isBlocked)
                                            <a href="{{ route('poka-yoke.denied', ['part_number' => $item->part->part_number ?? '', 'part_name' => $item->part->part_name ?? '', 'grn' => $item->grn_number]) }}"
                                               title="Poka-Yoke Guard: Blocked from EE inspection"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100
                                                      text-rose-700 border border-rose-200 text-xs font-bold transition">
                                                <span class="material-symbols-outlined text-[14px]">shield</span>
                                                Blocked
                                            </a>
                                        @else
                                            <a href="{{ route('inspections.create', ['incoming_id' => $item->id, 'grn' => $item->grn_number, 'part_id' => $item->part_id, 'qty' => $item->quantity, 'lot' => $item->lot_number, 'locked' => 1]) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700
                                                      text-white text-xs font-bold transition shadow-sm active:scale-95">
                                                <span class="material-symbols-outlined text-[14px]">play_arrow</span>
                                                Inspect
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-[11px] text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">
                                            Awaiting Tester
                                        </span>
                                    @endif

                                    <form action="{{ route('incoming-materials.destroy', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Remove GRN {{ $item->grn_number }} from receiving backlog?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Remove from backlog"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                                            <span class="material-symbols-outlined text-[15px]">delete</span>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('inspections.show', $item->inspection_id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200
                                              text-slate-700 text-xs font-semibold transition">
                                        <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        Report
                                    </a>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-16 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-400 flex items-center justify-center mb-3">
                                    <span class="material-symbols-outlined text-[30px]">inbox</span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700">No Arrived Shipments Found</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-xs leading-relaxed">
                                    No incoming materials match your selected filters. Register a new dock arrival or clear filter criteria.
                                </p>
                                <button type="button" onclick="toggleRegisterModal(true)"
                                        class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition">
                                    + Register Incoming GRN
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($materials->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $materials->links() }}
        </div>
        @endif

        {{-- Footer --}}
        <div class="px-6 py-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-400 bg-slate-50/30">
            <span class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[15px] text-slate-400">verified_user</span>
                MIL-STD-105E Sampling Standard &bull; AQL Level II Normal
            </span>
            <span class="font-mono">
                Receiving Log Updated: {{ now()->format('d/m/Y H:i:s') }}
            </span>
        </div>
    </div>

    {{-- ── 6. Inline Quick Scanner Section ── --}}
    <div id="quickScanSection" class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <span class="material-symbols-outlined text-[22px]">qr_code_scanner</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Dockside Quick GRN Barcode Scanner</h3>
                    <p class="text-xs text-slate-400">Scan physical shipment tag to instantly verify lot and open inspection form.</p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5 self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Optical Barcode Scanner Ready
            </span>
        </div>

        <form action="{{ route('incoming-materials.scan') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">qr_code_2</span>
                <input type="text" name="grn"
                       placeholder="Scan or type GRN barcode (e.g. GRN-20261021-001)..."
                       class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-indigo-500 rounded-xl text-xs font-mono uppercase tracking-wide focus:bg-white focus:ring-4 focus:ring-indigo-500/10 text-slate-900 transition">
            </div>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 py-2.5 text-xs font-bold transition flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">search</span>
                <span>Scan & Inspect</span>
            </button>
        </form>
    </div>

</div>

{{-- ── 7. Register New Arrived Shipment Modal ── --}}
<div id="registerShipmentModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 relative animate-in fade-in duration-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <span class="material-symbols-outlined text-[22px]">add_box</span>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Register Arrived Shipment</h3>
                    <p class="text-xs text-slate-400">Log incoming shipment to dock backlog.</p>
                </div>
            </div>
            <button type="button" onclick="toggleRegisterModal(false)"
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form action="{{ route('incoming-materials.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf

            {{-- GRN Code --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    GRN Number / Barcode *
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">qr_code</span>
                    <input type="text" name="grn_number" value="{{ $nextGrnNumber }}" required
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl text-xs font-mono font-bold uppercase focus:ring-4 focus:ring-indigo-500/10 text-slate-900">
                </div>
            </div>

            {{-- Component Part Selection --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Component Part (From Master Data) *
                </label>
                <select name="part_id" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl text-xs text-slate-800 focus:ring-4 focus:ring-indigo-500/10">
                    <option value="">Select Part Number...</option>
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}">
                            {{ $part->part_number }} — {{ $part->part_name }} ({{ $part->supplier_name }})
                        </option>
                    @endforeach
                </select>
                <span class="text-[10px] text-slate-400 mt-1 block">
                    Part specs and tolerance limits will be pulled from Component Master Data.
                </span>
            </div>

            {{-- Lot Number & Quantity --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Supplier Lot Number
                    </label>
                    <input type="text" name="lot_number" placeholder="e.g. LOT-TDK-2026-X"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl text-xs font-mono focus:ring-4 focus:ring-indigo-500/10 text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Received Qty (Pcs) *
                    </label>
                    <input type="number" name="quantity" min="1" placeholder="e.g. 5000" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl text-xs font-mono font-bold focus:ring-4 focus:ring-indigo-500/10 text-slate-800">
                </div>
            </div>

            {{-- Arrival Date & Time --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Dock Arrival Timestamp *
                </label>
                <input type="datetime-local" name="arrived_at" value="{{ now()->format('Y-m-d\TH:i') }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl text-xs focus:ring-4 focus:ring-indigo-500/10 text-slate-800">
            </div>

            {{-- Buttons --}}
            <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="toggleRegisterModal(false)"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-md shadow-indigo-600/20 cursor-pointer">
                    Confirm & Add to Backlog
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── 8. Chart.js & Operational Scripts ── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function toggleRegisterModal(show) {
    const modal = document.getElementById('registerShipmentModal');
    if (!modal) return;
    if (show) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
    }
}

(function () {
    const labels   = @json($trend->pluck('date'));
    const waitData = @json($trend->pluck('waiting'));
    const clearData = @json($trend->pluck('cleared'));

    const ctx = document.getElementById('dockArrivalChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'In Queue (Backlog)',
                    data: waitData,
                    backgroundColor: 'rgba(245, 158, 11, 0.75)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 1.5,
                    borderRadius: 6,
                },
                {
                    label: 'Inspected (Cleared)',
                    data: clearData,
                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1.5,
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 11, family: 'Inter' }, boxWidth: 12, padding: 14 }
                },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11, family: 'Inter' } } },
                y: {
                    stacked: true,
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
