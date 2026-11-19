@extends('layouts.app')
@section('title', 'Inspection Logbook Digital Archive & Status Pipeline - IQC Portal')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- 1. Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">folder_managed</span>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Inspection Logbook Digital Archive</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Digital Inspection Standard
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Centralized digital repository of lot quality inspections, multi-point verification audits, and MRB review loop.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(auth()->user()->isInspector())
            <a href="{{ route('logbooks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>Record New Logbook</span>
            </a>
            @endif
        </div>
    </div>

    <!-- 2. Direct Inspection Bar: Quick GR / Lot Lookup -->
    @if(auth()->user()->isInspector() || auth()->user()->isAdmin())
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl overflow-hidden border border-indigo-500/30">
        <!-- Background Glow Decor -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-300">Fast Inbound Quality Gate &bull; Poka-Yoke Validated</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-13 h-13 rounded-2xl bg-indigo-600/30 text-indigo-300 border border-indigo-400/30 flex items-center justify-center shadow-inner shrink-0">
                        <span class="material-symbols-outlined text-[28px]">qr_code_scanner</span>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-black tracking-tight text-white">
                            Scan GR or Lot to Directly Inspect
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 mt-0.5">
                            Type or scan any inbound GR barcode (e.g. <span class="font-mono text-indigo-200 font-bold bg-white/10 px-1.5 py-0.5 rounded">GR-202611-0105</span> or <span class="font-mono text-indigo-200 font-bold bg-white/10 px-1.5 py-0.5 rounded">0105</span>) or Lot Number to jump straight into active testing.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Direct Inspect Form: Leads to GR preview before inspect -->
            <form action="{{ route('logbooks.create') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                <div class="relative w-full sm:w-80 lg:w-96">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">barcode_scanner</span>
                    <input type="text" name="search" required autocomplete="off"
                        placeholder="Scan barcode or type GR / Lot No..." 
                        class="w-full pl-11 pr-4 py-4 rounded-2xl bg-white text-slate-900 placeholder:text-slate-400 font-mono text-sm font-bold border-0 focus:ring-4 focus:ring-indigo-400 shadow-inner transition">
                </div>
                <button type="submit" 
                    class="px-7 py-4 rounded-2xl bg-indigo-500 hover:bg-indigo-600 text-white font-black text-sm shrink-0 transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/30 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">search</span>
                    <span>Find GR</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- 3. Logbook Running Lifecycle Stage Pipeline (Visual Guide) -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-5 text-white shadow-md relative overflow-hidden">
        <div class="flex items-center justify-between gap-4 flex-wrap pb-3 border-b border-indigo-900/60 text-xs">
            <span class="font-bold uppercase tracking-wider text-indigo-300 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">alt_route</span>
                Running Logbook Inspection Lifecycle (SOP Flow)
            </span>
            <span class="text-[11px] text-slate-400">Standardized ISO 9001 & MIL-STD-105E Quality Gate</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4 text-xs">
            <!-- Stage 1 -->
            <div class="bg-white/5 rounded-2xl p-3 border border-white/10 flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-indigo-600/60 text-white flex items-center justify-center font-bold text-xs shrink-0">1</div>
                <div>
                    <div class="font-bold text-slate-200">Inbound & FIFO Queue</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Logistics registers GR. Queue aging tracked (Normal &lt;12h, Priority &lt;24h, Overdue).</div>
                </div>
            </div>

            <!-- Stage 2 -->
            <div class="bg-white/5 rounded-2xl p-3 border border-white/10 flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-blue-600/60 text-white flex items-center justify-center font-bold text-xs shrink-0">2</div>
                <div>
                    <div class="font-bold text-slate-200">3-Point Digital Inspection</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Visual packaging gate, expiry/date code verify, and sample measurement tolerance check.</div>
                </div>
            </div>

            <!-- Stage 3 -->
            <div class="bg-white/5 rounded-2xl p-3 border border-white/10 flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-amber-600/60 text-white flex items-center justify-center font-bold text-xs shrink-0">3</div>
                <div>
                    <div class="font-bold text-slate-200">MRB Hold (If Defect/Out-of-Spec)</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Non-conformance held for Supervisor evaluation: Accept, RTV, Concession, or Revise.</div>
                </div>
            </div>

            <!-- Stage 4 -->
            <div class="bg-white/5 rounded-2xl p-3 border border-white/10 flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-emerald-600/60 text-white flex items-center justify-center font-bold text-xs shrink-0">4</div>
                <div>
                    <div class="font-bold text-slate-200">Release & Warehouse Transfer</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Accepted lot cleared for production line; rejected lot quarantined for Return to Vendor.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Archive Folder Tabs (Categorized Digital Binders) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <!-- Tab All -->
        <a href="{{ route('logbooks.index', ['folder' => 'all']) }}" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 border
            {{ $activeFolder === 'all' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-500/20' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
            <span class="material-symbols-outlined text-[16px]">folder</span>
            <span>All Archives</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeFolder === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                {{ $totalLogbooks }}
            </span>
        </a>

        <!-- Tab EE Part -->
        <a href="{{ route('logbooks.index', ['folder' => 'ee_part']) }}" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 border
            {{ $activeFolder === 'ee_part' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm shadow-emerald-500/20' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
            <span class="material-symbols-outlined text-[16px]">memory</span>
            <span>EE Part Archive</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeFolder === 'ee_part' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                {{ $eeCount }}
            </span>
        </a>

        <!-- Tab Automotive -->
        <a href="{{ route('logbooks.index', ['folder' => 'automotive']) }}" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 border
            {{ $activeFolder === 'automotive' ? 'bg-teal-600 text-white border-teal-600 shadow-sm shadow-teal-500/20' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
            <span class="material-symbols-outlined text-[16px]">directions_car</span>
            <span>Automotive Archive</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeFolder === 'automotive' ? 'bg-white/20 text-white' : 'bg-teal-50 text-teal-700' }}">
                {{ $autoCount }}
            </span>
        </a>

        <!-- Tab Wire Harness -->
        <a href="{{ route('logbooks.index', ['folder' => 'wire_harness']) }}" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 border
            {{ $activeFolder === 'wire_harness' ? 'bg-purple-600 text-white border-purple-600 shadow-sm shadow-purple-500/20' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
            <span class="material-symbols-outlined text-[16px]">cable</span>
            <span>Wire Harness Archive</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeFolder === 'wire_harness' ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-700' }}">
                {{ $whCount }}
            </span>
        </a>

        <!-- Tab Held / MRB Review -->
        <a href="{{ route('logbooks.index', ['folder' => 'mrb_hold']) }}" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition shrink-0 border
            {{ $activeFolder === 'mrb_hold' ? 'bg-amber-600 text-white border-amber-600 shadow-sm shadow-amber-500/20' : 'bg-white hover:bg-slate-50 text-amber-700 border-amber-200' }}">
            <span class="material-symbols-outlined text-[16px]">warning</span>
            <span>Held / MRB Review</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeFolder === 'mrb_hold' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }} {{ $mrbHoldCount > 0 ? 'animate-pulse' : '' }}">
                {{ $mrbHoldCount }}
            </span>
        </a>
    </div>

    <!-- 4. KPI Counters for Statuses -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-center">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-400">Total Records</span>
            <div class="text-xl font-black text-slate-800 mt-1">{{ $totalLogbooks }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-500">Draft</span>
            <div class="text-xl font-black text-slate-700 mt-1">{{ $draftCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-amber-200 shadow-xs bg-amber-50/30">
            <span class="text-[10px] font-bold uppercase text-amber-700">Pending MRB</span>
            <div class="text-xl font-black text-amber-600 mt-1">{{ $waitingMrbCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-orange-200 shadow-xs bg-orange-50/30">
            <span class="text-[10px] font-bold uppercase text-orange-700">Needs Revision</span>
            <div class="text-xl font-black text-orange-600 mt-1 {{ $revisionCount > 0 ? 'animate-pulse' : '' }}">{{ $revisionCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-200 shadow-xs bg-emerald-50/30">
            <span class="text-[10px] font-bold uppercase text-emerald-700">Accepted</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $acceptedCount }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-rose-200 shadow-xs bg-rose-50/30">
            <span class="text-[10px] font-bold uppercase text-rose-700">RTV / Scrap</span>
            <div class="text-xl font-black text-rose-600 mt-1">{{ $rejectedCount }}</div>
        </div>
    </div>

    <!-- 5. Filters & Search Toolbar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs space-y-3">
        <form id="filterForm" method="GET" action="{{ route('logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center">
            <input type="hidden" name="folder" id="folderInput" value="{{ $activeFolder }}">
            
            <!-- Search bar (col-span-4) -->
            <div class="sm:col-span-4 relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                <input type="text" id="searchInput" name="search" value="{{ request('search') }}" 
                    placeholder="Search by GR, Lot, PO, Part, Supplier, Inspector..." 
                    autocomplete="off"
                    class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-7 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                @if(request('search'))
                    <a href="{{ route('logbooks.index', array_merge(request()->except('search'), ['folder' => $activeFolder])) }}" 
                       class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-sm font-bold" title="Clear search">&times;</a>
                @endif
            </div>

            <!-- Status dropdown (col-span-2) -->
            <div class="sm:col-span-2">
                <select id="statusFilter" name="status" onchange="this.form.submit()" 
                    class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 bg-white font-medium text-slate-700 cursor-pointer transition">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="menunggu_penetapan" {{ request('status') === 'menunggu_penetapan' ? 'selected' : '' }}>Pending MRB</option>
                    <option value="perlu_revisi" {{ request('status') === 'perlu_revisi' ? 'selected' : '' }}>Needs Revision</option>
                    <option value="accept" {{ request('status') === 'accept' ? 'selected' : '' }}>Accepted</option>
                    <option value="return_to_vendor" {{ request('status') === 'return_to_vendor' ? 'selected' : '' }}>RTV</option>
                    <option value="scrap" {{ request('status') === 'scrap' ? 'selected' : '' }}>Scrap</option>
                    <option value="rework" {{ request('status') === 'rework' ? 'selected' : '' }}>Rework</option>
                </select>
            </div>

            <!-- Supplier dropdown (col-span-3) -->
            <div class="sm:col-span-3">
                <select id="supplierFilter" name="supplier_id" onchange="this.form.submit()" 
                    class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 bg-white font-medium text-slate-700 cursor-pointer transition">
                    <option value="">All Suppliers</option>
                    @if(isset($suppliers))
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ Str::limit($sup->nama_supplier, 24) }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Date filter (col-span-2) -->
            <div class="sm:col-span-2">
                <input type="date" id="dateFilter" name="tanggal_dari" value="{{ request('tanggal_dari') }}" onchange="this.form.submit()"
                    class="w-full text-xs rounded-xl border border-slate-200 px-2.5 py-2.5 focus:ring-2 focus:ring-indigo-500 text-slate-700" title="Filter by Inspection Date">
            </div>

            <!-- Actions (col-span-1) -->
            <div class="sm:col-span-1 flex items-center gap-1.5">
                @if(request()->hasAny(['search', 'status', 'supplier_id', 'tanggal_dari']) || $activeFolder !== 'all')
                <a href="{{ route('logbooks.index', ['folder' => 'all']) }}" 
                    class="w-full inline-flex items-center justify-center p-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition border border-rose-200" title="Reset all filters">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                </a>
                @else
                <button type="submit" class="w-full inline-flex items-center justify-center p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition shadow-xs" title="Apply filter">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                </button>
                @endif
            </div>
        </form>

        <!-- Active Filter Indicator Bar -->
        @if(request()->hasAny(['search', 'status', 'supplier_id', 'tanggal_dari']) || ($activeFolder && $activeFolder !== 'all'))
        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100 text-xs">
            <span class="text-slate-400 font-bold uppercase text-[10px] flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">tune</span>
                Active Filters:
            </span>

            @if($activeFolder && $activeFolder !== 'all')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold border border-slate-200">
                    <span>Folder: <strong>{{ ucwords(str_replace('_', ' ', $activeFolder)) }}</strong></span>
                    <a href="{{ route('logbooks.index', array_merge(request()->except('folder'), ['folder' => 'all'])) }}" class="text-slate-400 hover:text-slate-700 font-bold ml-1" title="Show all folders">&times;</a>
                </span>
            @endif

            @if(request('status'))
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-semibold border border-indigo-200">
                    <span>Status: <strong>{{ ucwords(str_replace('_', ' ', request('status'))) }}</strong></span>
                    <a href="{{ route('logbooks.index', array_merge(request()->except('status'), ['folder' => $activeFolder])) }}" class="text-indigo-400 hover:text-indigo-700 font-bold ml-1" title="Remove status filter">&times;</a>
                </span>
            @endif

            @if(request('supplier_id') && isset($suppliers))
                @php $activeSup = $suppliers->firstWhere('id', request('supplier_id')); @endphp
                @if($activeSup)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-semibold border border-indigo-200">
                    <span>Supplier: <strong>{{ $activeSup->nama_supplier }}</strong></span>
                    <a href="{{ route('logbooks.index', array_merge(request()->except('supplier_id'), ['folder' => $activeFolder])) }}" class="text-indigo-400 hover:text-indigo-700 font-bold ml-1" title="Remove supplier filter">&times;</a>
                </span>
                @endif
            @endif

            @if(request('search'))
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-semibold border border-indigo-200">
                    <span>Search: "<strong>{{ request('search') }}</strong>"</span>
                    <a href="{{ route('logbooks.index', array_merge(request()->except('search'), ['folder' => $activeFolder])) }}" class="text-indigo-400 hover:text-indigo-700 font-bold ml-1" title="Remove keyword">&times;</a>
                </span>
            @endif

            @if(request('tanggal_dari'))
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-semibold border border-indigo-200">
                    <span>Date: <strong>{{ request('tanggal_dari') }}</strong></span>
                    <a href="{{ route('logbooks.index', array_merge(request()->except('tanggal_dari'), ['folder' => $activeFolder])) }}" class="text-indigo-400 hover:text-indigo-700 font-bold ml-1" title="Remove date">&times;</a>
                </span>
            @endif

            <a href="{{ route('logbooks.index', ['folder' => 'all']) }}" class="text-[11px] text-rose-600 hover:text-rose-800 font-bold ml-auto flex items-center gap-0.5">
                <span class="material-symbols-outlined text-[13px]">clear_all</span>
                <span>Clear All</span>
            </a>
        </div>
        @endif
    </div>

    <!-- 6. Table of Logbooks (Redesigned & Neater) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50/50 border-b border-slate-200/80 flex items-center justify-between text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px] text-slate-400">table_rows</span>
                <span id="filteredRowCount" class="font-bold text-slate-700">
                    Showing {{ $logbooks->count() }} of {{ $logbooks->total() }} record(s)
                </span>
                @if(request()->hasAny(['search', 'status', 'tanggal_dari']))
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">Filtered</span>
                @endif
            </div>
            <div class="text-[11px] text-slate-400">
                Sorted by most recent inspection first
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">GRN & Lot No</th>
                        <th class="py-3.5 px-4">Component & Commodity</th>
                        <th class="py-3.5 px-4">Supplier & Origin</th>
                        <th class="py-3.5 px-4">Inspection Checks</th>
                        <th class="py-3.5 px-4">Tgl Keluar / Rilis & Inspector</th>
                        <th class="py-3.5 px-4">Running Status</th>
                        <th class="py-3.5 px-4">MRB Disposition</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="logbookTableBody">
                    @forelse($logbooks as $log)
                    @php 
                        $rcv = $log->receiving; 
                        $hasMeasFail = $log->measurements->where('status_toleransi', 'fail')->count() > 0;
                        $searchPayload = strtolower(
                            ($rcv->no_grn_iqa ?? '') . ' ' .
                            ($rcv->no_lot ?? '') . ' ' .
                            ($rcv->no_po ?? '') . ' ' .
                            ($rcv->part->kode_part ?? '') . ' ' .
                            ($rcv->part->nama_part ?? '') . ' ' .
                            ($rcv->supplier->nama_supplier ?? '') . ' ' .
                            ($log->inspector->name ?? '') . ' ' .
                            $log->status_label . ' ' .
                            ($log->catatan ?? '') . ' ' .
                            ($log->visual_notes ?? '') . ' ' .
                            ($log->date_code ?? '')
                        );
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition logbook-row"
                        data-status="{{ $log->status }}"
                        data-search="{{ $searchPayload }}">
                        <!-- GRN & Lot -->
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-indigo-600 text-sm block">{{ $rcv->no_grn_iqa ?? '-' }}</span>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">Lot: <b class="text-slate-600">{{ $rcv->no_lot ?? '-' }}</b></div>
                            <div class="text-[10px] text-slate-400 font-mono">PO: {{ $rcv->no_po ?? '-' }}</div>
                            <div class="text-[10px] text-slate-500 flex items-center gap-1 mt-1 font-sans">
                                <span class="material-symbols-outlined text-[13px] text-blue-600">login</span>
                                <span>Masuk: <b class="text-slate-700 font-mono">{{ $rcv->tanggal_terima ? $rcv->tanggal_terima->format('d M Y') : '-' }}</b></span>
                            </div>
                        </td>

                        <!-- Component & Commodity -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 text-xs">{{ $rcv->part->kode_part ?? '-' }}</div>
                            <div class="text-[10px] text-slate-500 truncate max-w-[130px]">{{ $rcv->part->nama_part ?? '-' }}</div>
                            <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold mt-1
                                @if($rcv->part->kategori_komoditas === 'ee_part') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($rcv->part->kategori_komoditas === 'automotive') bg-teal-50 text-teal-700 border border-teal-200
                                @else bg-purple-50 text-purple-700 border border-purple-200 @endif">
                                {{ $rcv->part->komoditas_label ?? '-' }}
                            </span>
                        </td>

                        <!-- Supplier & Origin -->
                        <td class="py-3.5 px-4 text-slate-700">
                            <div class="font-bold text-slate-800">{{ $rcv->supplier->nama_supplier ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $rcv->supplier->negara_asal ?? 'Indonesia' }}</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">Inbound: {{ number_format($rcv->qty_datang) }} pcs</div>
                        </td>

                        <!-- Quality Verification Checks Summary -->
                        <td class="py-3.5 px-4">
                            <div class="space-y-1">
                                <!-- Measurement Check -->
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] text-slate-400">Measurement:</span>
                                    @if($hasMeasFail)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800">Out-of-Spec</span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">{{ $log->qty_sampel }} Smp (PASS)</span>
                                    @endif
                                </div>

                                <!-- Visual Check -->
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] text-slate-400">Visual Gate:</span>
                                    @if($log->hasVisualFail())
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800">Visual FAIL</span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Visual OK</span>
                                    @endif
                                </div>

                                <!-- Shelf Life / Expiry Check -->
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] text-slate-400">Date Code:</span>
                                    @if($log->is_expired)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800 animate-pulse">EXPIRED</span>
                                    @elseif($log->date_code)
                                        <span class="font-mono text-[10px] text-slate-600 font-semibold">{{ $log->date_code }}</span>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-mono">Verified</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Tgl Selesai / Keluar & Inspector -->
                        <td class="py-3.5 px-4 text-slate-600">
                            <div class="text-xs font-bold text-slate-900 font-mono flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-emerald-600">logout</span>
                                {{ $log->tanggal_inspeksi ? $log->tanggal_inspeksi->format('d M Y, H:i') : '-' }}
                            </div>
                            <div class="font-semibold text-slate-700 text-[11px] mt-1">{{ $log->inspector->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $log->inspector->role_label ?? 'Inspector' }}</div>
                        </td>

                        <!-- Running Status -->
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $log->status_badge_class }}">
                                @if($log->status === 'accept')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @elseif($log->status === 'menunggu_penetapan')
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                @elseif($log->status === 'perlu_revisi')
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                @elseif(in_array($log->status, ['return_to_vendor', 'scrap']))
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                @endif
                                {{ $log->status_label }}
                            </span>
                        </td>

                        <!-- MRB Disposition Notes -->
                        <td class="py-3.5 px-4">
                            @if($log->latestMrbDisposition)
                                <div class="font-bold text-slate-800 text-[11px]">{{ $log->latestMrbDisposition->decision_label }}</div>
                                <div class="text-[10px] text-slate-500 truncate max-w-[140px]" title="{{ $log->latestMrbDisposition->catatan }}">
                                    {{ $log->latestMrbDisposition->catatan }}
                                </div>
                            @elseif($log->status === 'menunggu_penetapan')
                                <span class="text-amber-700 font-semibold text-[10px] flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px]">hourglass_top</span>
                                    Under SPV Review
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('logbooks.show', $log->id) }}" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                <span class="material-symbols-outlined text-[13px]">visibility</span>
                                <span>View</span>
                            </a>

                            @if($log->status === 'perlu_revisi' && auth()->user()->isInspector())
                            <a href="{{ route('logbooks.edit', $log->id) }}" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-xs transition">
                                <span class="material-symbols-outlined text-[13px]">edit_note</span>
                                <span>Revise</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <span class="material-symbols-outlined text-[44px] text-slate-300 block mb-2">filter_alt_off</span>
                            <div class="font-bold text-slate-700 text-sm">No matching logbook records found</div>
                            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                                No inspection records match your current filter criteria 
                                @if(request('status'))
                                    (Status: <strong class="text-slate-700">{{ ucwords(str_replace('_', ' ', request('status'))) }}</strong>)
                                @endif
                                @if(request('search'))
                                    (Search: <strong class="text-slate-700">"{{ request('search') }}"</strong>)
                                @endif
                                in folder <strong class="text-slate-700">{{ ucwords(str_replace('_', ' ', $activeFolder)) }}</strong>.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('logbooks.index', ['folder' => 'all']) }}" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition border border-indigo-200">
                                    <span class="material-symbols-outlined text-[15px]">refresh</span>
                                    <span>Reset All Filters & View All Archives</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logbooks->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $logbooks->links() }}
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');
    const dateInput = document.getElementById('dateFilter');
    const filterForm = document.getElementById('filterForm');
    const tableRows = document.querySelectorAll('#logbookTableBody tr.logbook-row');
    const countBadge = document.getElementById('filteredRowCount');

    // Instant Client-Side Live Filter (0ms latency)
    function applyInstantClientFilter() {
        if (!tableRows.length) return;

        const selectedStatus = statusSelect ? statusSelect.value.toLowerCase().trim() : '';
        const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let visibleCount = 0;

        tableRows.forEach(function (row) {
            const rowStatus = (row.getAttribute('data-status') || '').toLowerCase().trim();
            const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();

            const matchesStatus = !selectedStatus || rowStatus === selectedStatus;
            const matchesSearch = !searchVal || rowSearch.indexOf(searchVal) !== -1;

            if (matchesStatus && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (countBadge) {
            countBadge.textContent = 'Showing ' + visibleCount + ' record(s) matching filter';
        }
    }

    // Trigger instant filter and submit when status changes
    if (statusSelect) {
        statusSelect.addEventListener('change', function () {
            applyInstantClientFilter();
            filterForm.submit();
        });
    }

    // Real-time live filtering as user types in search bar
    if (searchInput) {
        let debounceTimer = null;
        searchInput.addEventListener('input', function () {
            applyInstantClientFilter();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                filterForm.submit();
            }, 650);
        });
    }

    // Auto submit on date select
    if (dateInput) {
        dateInput.addEventListener('change', function () {
            filterForm.submit();
        });
    }
});
</script>
@endpush
@endsection
