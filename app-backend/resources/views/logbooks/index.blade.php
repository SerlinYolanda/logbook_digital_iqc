@extends('layouts.app')
@section('title', 'Inspection Logbook Digital Archive & Status Pipeline - IQC Portal')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- 1. Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-xs shrink-0">
                <span class="material-symbols-outlined text-[26px]">folder_managed</span>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Inspection Logbook Digital Archive</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Digital Inspection Standard
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Quality inspection records for inbound material lots.</p>
            </div>
        </div>
    </div>

    <!-- 2. Direct Inspection Bar: Quick GRN / Lot Lookup -->
    @if(auth()->user()->isInspector() || auth()->user()->isAdmin())
    <div class="bg-slate-900 rounded-xl p-5 text-white border border-slate-800 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white shrink-0 shadow-sm">
                <span class="material-symbols-outlined text-[24px]">qr_code_scanner</span>
            </div>
            <div>
                <h3 class="text-sm font-bold text-white tracking-wide uppercase">Quick Lot Barcode Scanner</h3>
                <p class="text-xs text-slate-400 mt-0.5">Scan physical barcode or enter IQA GRN / Lot number to directly open inspection sheet.</p>
            </div>
        </div>

        <form action="{{ route('logbooks.create') }}" method="GET" class="flex items-center gap-3 w-full lg:w-auto lg:min-w-[500px]">
            <div class="relative flex-1">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[20px]">barcode_scanner</span>
                <input type="text" name="search" required autocomplete="off"
                    placeholder="Scan barcode or type GRN / Lot No..." 
                    class="w-full pl-11 pr-4 py-3 text-sm font-mono rounded-lg bg-slate-800 border-slate-700 text-white placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <button type="submit" 
                class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm transition shrink-0 flex items-center gap-2 shadow-xs cursor-pointer">
                <span>Find Lot</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </button>
        </form>
    </div>
    @endif

    <!-- 3. Archive Folder Tabs & Summary Bar -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <!-- Folder Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            <!-- Tab All -->
            <a href="{{ route('logbooks.index', ['folder' => 'all']) }}" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 border
                {{ $activeFolder === 'all' ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
                <span class="material-symbols-outlined text-[16px]">folder</span>
                <span>All Archives</span>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono {{ $activeFolder === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $totalLogbooks }}
                </span>
            </a>

            <!-- Tab EE Part -->
            <a href="{{ route('logbooks.index', ['folder' => 'ee_part']) }}" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 border
                {{ $activeFolder === 'ee_part' ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
                <span class="material-symbols-outlined text-[16px]">memory</span>
                <span>EE Part</span>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono {{ $activeFolder === 'ee_part' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $eeCount }}
                </span>
            </a>

            <!-- Tab Automotive -->
            <a href="{{ route('logbooks.index', ['folder' => 'automotive']) }}" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 border
                {{ $activeFolder === 'automotive' ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
                <span class="material-symbols-outlined text-[16px]">directions_car</span>
                <span>Automotive</span>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono {{ $activeFolder === 'automotive' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $autoCount }}
                </span>
            </a>

            <!-- Tab Wire Harness -->
            <a href="{{ route('logbooks.index', ['folder' => 'wire_harness']) }}" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 border
                {{ $activeFolder === 'wire_harness' ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
                <span class="material-symbols-outlined text-[16px]">cable</span>
                <span>Wire Harness</span>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono {{ $activeFolder === 'wire_harness' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $whCount }}
                </span>
            </a>

            <!-- Tab Held / MRB Review -->
            <a href="{{ route('logbooks.index', ['folder' => 'mrb_hold']) }}" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 border
                {{ $activeFolder === 'mrb_hold' ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white hover:bg-slate-50 text-slate-600 border-slate-200' }}">
                <span class="material-symbols-outlined text-[16px]">warning</span>
                <span>Under MRB Review</span>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono {{ $activeFolder === 'mrb_hold' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                    {{ $mrbHoldCount }}
                </span>
            </a>
        </div>

        <!-- Quick Status Pills -->
        <div class="flex items-center gap-2 text-xs flex-wrap">
            <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">
                {{ $acceptedCount }} Passed
            </span>
            <span class="px-2.5 py-1 rounded-md bg-orange-50 text-orange-700 border border-orange-200 font-bold">
                {{ $revisionCount }} Needs Revision
            </span>
            <span class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                {{ $rejectedCount }} RTV / Scrap
            </span>
        </div>
    </div>

    <!-- 5. Filters & Search Toolbar -->
    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs space-y-3">
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

    <!-- 6. Archive View Container (Clean & Flexible) -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- View Toolbar Header -->
        <div class="px-5 py-3.5 bg-slate-50/50 border-b border-slate-200/80 flex items-center justify-between text-xs text-slate-500 flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px] text-slate-400">inventory_2</span>
                <span id="filteredRowCount" class="font-bold text-slate-700">
                    Showing {{ $logbooks->count() }} of {{ $logbooks->total() }} record(s)
                </span>
                @if(request()->hasAny(['search', 'status', 'tanggal_dari']))
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Filtered</span>
                @endif
            </div>

            <!-- View Mode Switcher (Table vs Cards) -->
            <div class="flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                <button type="button" onclick="setLogbookView('table')" id="btnViewTable"
                    class="px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition bg-white text-slate-800 shadow-xs">
                    <span class="material-symbols-outlined text-[15px]">table_rows</span>
                    <span>Table View</span>
                </button>
                <button type="button" onclick="setLogbookView('cards')" id="btnViewCards"
                    class="px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition text-slate-600 hover:text-slate-900">
                    <span class="material-symbols-outlined text-[15px]">grid_view</span>
                    <span>Cards View</span>
                </button>
            </div>
        </div>

        <!-- 1. Table View (Streamlined, Non-Cluttered, High-Signal) -->
        <div id="logbookTableContainer" class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 w-44">GRN & Lot No</th>
                        <th class="py-3.5 px-4">Component & Commodity</th>
                        <th class="py-3.5 px-4 w-52">Supplier & Qty</th>
                        <th class="py-3.5 px-4 w-44">Inspection Date</th>
                        <th class="py-3.5 px-4 w-56">Quality Status</th>
                        <th class="py-3.5 px-4 w-32 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="logbookTableBody">
                    @forelse($logbooks as $log)
                    @php 
                        $rcv = $log->receiving; 
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
                    <tr class="hover:bg-blue-50/30 transition logbook-row group"
                        data-status="{{ $log->status }}"
                        data-search="{{ $searchPayload }}">
                        <!-- GRN & Lot -->
                        <td class="py-3 px-4 whitespace-nowrap align-middle">
                            <div class="font-mono font-bold text-blue-600 text-sm tracking-tight">{{ $rcv->no_grn_iqa ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">Lot: <span class="text-slate-800 font-semibold">{{ $rcv->no_lot ?? '-' }}</span></div>
                        </td>

                        <!-- Component & Commodity -->
                        <td class="py-3 px-4 align-middle">
                            <div class="font-semibold text-slate-900 text-xs sm:text-sm leading-snug">{{ $rcv->part->nama_part ?? '-' }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-slate-500 font-mono flex-wrap">
                                <span>{{ $rcv->part->kode_part ?? '-' }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-[10px] font-sans font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200">
                                    {{ $rcv->part->komoditas_label ?? '-' }}
                                </span>
                            </div>
                        </td>

                        <!-- Supplier & Qty -->
                        <td class="py-3 px-4 align-middle">
                            <div class="font-medium text-slate-800 text-xs">{{ $rcv->supplier->nama_supplier ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                Inbound: <b class="text-slate-700">{{ number_format($rcv->qty_datang) }} pcs</b>
                            </div>
                        </td>

                        <!-- Inspection Date & Inspector -->
                        <td class="py-3 px-4 whitespace-nowrap align-middle">
                            <div class="font-mono text-slate-800 text-xs font-semibold">{{ $log->tanggal_inspeksi ? $log->tanggal_inspeksi->format('d M Y, H:i') : '-' }}</div>
                            <div class="text-slate-500 text-[11px] mt-0.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px] text-slate-400">person</span>
                                <span>{{ $log->inspector->name ?? 'Inspector' }}</span>
                            </div>
                        </td>

                        <!-- Quality Status -->
                        <td class="py-3 px-4 align-middle">
                            @if($log->status === 'accept')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Accepted & Released
                                </span>
                            @elseif($log->status === 'perlu_revisi')
                                <div>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Needs Revision
                                    </span>
                                    @if($log->latestMrbDisposition)
                                        <div class="text-[10px] text-amber-700 font-mono mt-0.5 truncate max-w-[200px]" title="{{ $log->latestMrbDisposition->catatan }}">
                                            {{ $log->latestMrbDisposition->catatan }}
                                        </div>
                                    @endif
                                </div>
                            @elseif($log->status === 'menunggu_penetapan')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    Pending MRB Review
                                </span>
                            @elseif(in_array($log->status, ['return_to_vendor', 'scrap']))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    {{ $log->status_label }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">
                                    {{ $log->status_label }}
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-right whitespace-nowrap align-middle">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('logbooks.show', $log->id) }}" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                    <span>View Sheet</span>
                                    <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                                </a>

                                @if($log->status === 'perlu_revisi' && auth()->user()->isInspector())
                                <a href="{{ route('logbooks.edit', $log->id) }}" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition">
                                    <span class="material-symbols-outlined text-[13px]">edit_note</span>
                                    <span>Revise</span>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <span class="material-symbols-outlined text-[44px] text-slate-300 block mb-2">filter_alt_off</span>
                            <div class="font-bold text-slate-700 text-sm">No matching logbook records found</div>
                            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                                No inspection records match your current filter criteria.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. Cards View (Modern, Flexible Grid Layout) -->
        <div id="logbookCardsContainer" class="hidden p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($logbooks as $log)
            @php 
                $rcv = $log->receiving; 
                $searchPayload = strtolower(
                    ($rcv->no_grn_iqa ?? '') . ' ' .
                    ($rcv->no_lot ?? '') . ' ' .
                    ($rcv->no_po ?? '') . ' ' .
                    ($rcv->part->kode_part ?? '') . ' ' .
                    ($rcv->part->nama_part ?? '') . ' ' .
                    ($rcv->supplier->nama_supplier ?? '') . ' ' .
                    ($log->inspector->name ?? '') . ' ' .
                    $log->status_label
                );
            @endphp
            <div class="logbook-card bg-slate-50/50 border border-slate-200 hover:border-blue-400 hover:shadow-md transition p-4 rounded-xl flex flex-col justify-between"
                data-status="{{ $log->status }}"
                data-search="{{ $searchPayload }}">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono font-bold text-blue-600 text-sm">{{ $rcv->no_grn_iqa ?? '-' }}</span>
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border {{ $log->status_badge_class }}">
                            {{ $log->status_label }}
                        </span>
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm mt-2.5 leading-snug">{{ $rcv->part->nama_part ?? '-' }}</h4>
                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">Part: <b class="text-slate-700">{{ $rcv->part->kode_part ?? '-' }}</b></div>
                    <div class="text-[11px] text-slate-500 mt-1">Supplier: <b class="text-slate-700">{{ $rcv->supplier->nama_supplier ?? '-' }}</b></div>
                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">Lot: <b>{{ $rcv->no_lot ?? '-' }}</b> | Inbound: <b>{{ number_format($rcv->qty_datang) }} pcs</b></div>
                    
                    @if($log->status === 'perlu_revisi' && $log->latestMrbDisposition)
                    <div class="mt-2 p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-[11px] font-mono leading-tight">
                        <b>Revision note:</b> {{ $log->latestMrbDisposition->catatan }}
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-mono text-[11px]">{{ $log->tanggal_inspeksi ? $log->tanggal_inspeksi->format('d M Y') : '-' }}</span>
                    <a href="{{ route('logbooks.show', $log->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                        <span>View Details</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400">
                <span class="material-symbols-outlined text-[44px] text-slate-300 block mb-2">filter_alt_off</span>
                <div class="font-bold text-slate-700 text-sm">No matching logbook records found</div>
            </div>
            @endforelse
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
function setLogbookView(mode) {
    const tableContainer = document.getElementById('logbookTableContainer');
    const cardsContainer = document.getElementById('logbookCardsContainer');
    const btnTable = document.getElementById('btnViewTable');
    const btnCards = document.getElementById('btnViewCards');

    if (mode === 'cards') {
        if (tableContainer) tableContainer.classList.add('hidden');
        if (cardsContainer) cardsContainer.classList.remove('hidden');
        if (btnCards) btnCards.className = "px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition bg-white text-slate-800 shadow-xs";
        if (btnTable) btnTable.className = "px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition text-slate-600 hover:text-slate-900";
        localStorage.setItem('iqc_logbook_view', 'cards');
    } else {
        if (tableContainer) tableContainer.classList.remove('hidden');
        if (cardsContainer) cardsContainer.classList.add('hidden');
        if (btnTable) btnTable.className = "px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition bg-white text-slate-800 shadow-xs";
        if (btnCards) btnCards.className = "px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition text-slate-600 hover:text-slate-900";
        localStorage.setItem('iqc_logbook_view', 'table');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Restore user view preference
    const savedView = localStorage.getItem('iqc_logbook_view');
    if (savedView === 'cards') {
        setLogbookView('cards');
    }

    const statusSelect = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');
    const dateInput = document.getElementById('dateFilter');
    const filterForm = document.getElementById('filterForm');
    const tableRows = document.querySelectorAll('#logbookTableBody tr.logbook-row');
    const cards = document.querySelectorAll('#logbookCardsContainer .logbook-card');
    const countBadge = document.getElementById('filteredRowCount');

    // Instant Client-Side Live Filter (0ms latency for both views)
    function applyInstantClientFilter() {
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

        cards.forEach(function (card) {
            const cardStatus = (card.getAttribute('data-status') || '').toLowerCase().trim();
            const cardSearch = (card.getAttribute('data-search') || '').toLowerCase();

            const matchesStatus = !selectedStatus || cardStatus === selectedStatus;
            const matchesSearch = !searchVal || cardSearch.indexOf(searchVal) !== -1;

            if (matchesStatus && matchesSearch) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
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
