@extends('layouts.app')
@section('title', 'Quality Inspection Logbook - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">assignment</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">IQC Quality Inspection Logbook Form</h1>
                <p class="text-xs text-slate-500 mt-0.5">Testing dimensional, electrical & visual parameters against nominal tolerance specifications.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold border
                @if(auth()->user()->role === 'inspector_ee') bg-emerald-50 text-emerald-700 border-emerald-200
                @elseif(auth()->user()->role === 'inspector_automotive') bg-teal-50 text-teal-700 border-teal-200
                @elseif(auth()->user()->role === 'inspector_wire_harness') bg-purple-50 text-purple-700 border-purple-200
                @else bg-indigo-50 text-indigo-700 border-indigo-200 @endif">
                Sub-Role: {{ auth()->user()->role_label }}
            </span>
            <a href="{{ route('logbooks.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                Back to History
            </a>
        </div>
    </div>

    @if(!$receiving)
    <!-- Step 1: Select Material from Available Inbound Queue -->
    <div class="space-y-6">

        <!-- Prominent High-Visibility Quick Barcode Scanner Banner -->
        <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl overflow-hidden border border-indigo-500/30">
            <!-- Background Glow Decor -->
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600/30 text-indigo-300 border border-indigo-400/30 flex items-center justify-center shadow-inner shrink-0">
                        <span class="material-symbols-outlined text-[30px]">qr_code_scanner</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Barcode Scanner Ready
                            </span>
                            <span class="text-xs text-indigo-200 font-semibold">Authorized: {{ auth()->user()->role_label }}</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black mt-1 text-white tracking-tight">
                            Quick Inbound Barcode Scanner
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 mt-0.5">
                            Scan physical traveler tag or type the <strong>GR Number</strong> (e.g. <span class="font-mono text-indigo-200">GR-202611-0105</span> or <span class="font-mono text-indigo-200">0105</span>) or <strong>Lot Number</strong> to jump directly into parameter measurement.
                        </p>
                    </div>
                </div>

                <!-- Large Industrial Scanner Search Form -->
                <form action="{{ route('logbooks.create') }}" method="GET" class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative w-full flex-1">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[22px]">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" required autofocus autocomplete="off"
                            placeholder="Search or scan GR Number (e.g. GR-202611-0105 or 0105) or Lot Number..." 
                            class="w-full pl-12 pr-10 py-4 rounded-2xl text-slate-900 placeholder:text-slate-400 text-sm sm:text-base font-mono font-bold border-0 focus:ring-4 focus:ring-indigo-400 shadow-inner bg-white">
                        @if(request('search'))
                            <a href="{{ route('logbooks.create') }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 font-bold text-lg" title="Clear search">&times;</a>
                        @endif
                    </div>
                    <button type="submit" 
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-indigo-500 hover:bg-indigo-600 text-white font-black text-sm sm:text-base shrink-0 transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/30 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                        <span>Find GR</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Section Divider -->
        <div class="flex items-center gap-4 py-2">
            <div class="h-px bg-slate-200 flex-1"></div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                @if(request('search'))
                    Search Result
                @else
                    Or Select Material from Inbound FIFO Queue
                @endif
            </span>
            <div class="h-px bg-slate-200 flex-1"></div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between gap-4 flex-wrap pb-2 border-b border-slate-100">
                <div>
                    @if(request('search'))
                        <h3 class="text-base font-bold text-slate-900">Search Result for GR / Lot: "<span class="font-mono text-indigo-600">{{ request('search') }}</span>"</h3>
                        <p class="text-xs text-slate-500">Review the material specifications and supplier below before opening inspection sheet.</p>
                    @else
                        <h3 class="text-base font-bold text-slate-900">Available Inbound Queue ({{ auth()->user()->role_label }})</h3>
                        <p class="text-xs text-slate-500">Pick any pending arrival batch below to proceed with quality inspection.</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if(request('search'))
                        <a href="{{ route('logbooks.create') }}" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 transition flex items-center gap-1">
                            <span>Reset Search</span>
                            <span class="material-symbols-outlined text-[14px]">close</span>
                        </a>
                    @endif
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $pendingReceivings->count() }} Lot(s) Available
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                @forelse($pendingReceivings as $item)
                <div class="p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 hover:shadow-md transition bg-slate-50/50 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start gap-2">
                            <span class="font-mono text-sm font-bold text-indigo-600 bg-white px-2 py-0.5 rounded border border-indigo-100">{{ $item->no_grn_iqa }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $item->aging_badge_class }}">
                                {{ $item->aging_status }}
                            </span>
                        </div>
                        <h3 class="font-bold text-sm text-slate-800 mt-2.5">{{ $item->part->nama_part ?? '-' }}</h3>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">Part: <b class="text-slate-700">{{ $item->part->kode_part ?? '-' }}</b></div>
                        <div class="text-xs text-slate-500 mt-1">Supplier: <b class="text-slate-700">{{ $item->supplier->nama_supplier ?? '-' }}</b></div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">Lot: <b>{{ $item->no_lot }}</b> | Qty: <b>{{ number_format($item->qty_datang) }} pcs</b></div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200 flex justify-between items-center">
                        <span class="text-[11px] font-semibold text-slate-500">{{ $item->part->komoditas_label }}</span>
                        <a href="{{ route('logbooks.create', ['receiving_id' => $item->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition">
                            <span class="material-symbols-outlined text-[15px]">play_arrow</span>
                            <span>Inspect This Lot &rarr;</span>
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-16 text-center">
                    <div class="w-16 h-16 rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-outlined text-[38px]">search_off</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-800">
                        @if(request('search'))
                            No Inbound Material Found
                        @else
                            Queue Is Currently Empty
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        @if(request('search'))
                            No material matching GR / Lot Number "<strong class="font-mono text-slate-700">{{ request('search') }}</strong>" was found in your available queue ({{ auth()->user()->role_label }}).
                        @else
                            No inbound materials currently awaiting inspection for your commodity.
                        @endif
                    </p>
                    @if(request('search'))
                    <div class="mt-4">
                        <a href="{{ route('logbooks.create') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold border border-indigo-200 transition">
                            <span class="material-symbols-outlined text-[16px]">refresh</span>
                            <span>Reset Search & View All Queue</span>
                        </a>
                    </div>
                    @endif
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @else
    <!-- Step 2: Material Selected - Inspection Form with Parameter Table -->
    <form action="{{ route('logbooks.store') }}" method="POST" id="inspectionForm" class="space-y-6">
        @csrf
        <input type="hidden" name="receiving_id" value="{{ $receiving->id }}">
        <input type="hidden" name="action_type" id="actionTypeInput" value="submit">

        <!-- Material Details Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold font-mono">
                        GR
                    </div>
                    <div>
                        <div class="font-mono font-bold text-slate-900 text-sm">{{ $receiving->no_grn_iqa }}</div>
                        <div class="text-xs text-slate-500">PO No: <span class="font-mono text-slate-700 font-semibold">{{ $receiving->no_po }}</span> | Lot: <span class="font-mono text-slate-700 font-bold">{{ $receiving->no_lot }}</span></div>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs flex-wrap">
                    <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400">Inbound Qty:</span>
                        <b class="text-slate-800 font-mono">{{ number_format($receiving->qty_datang) }} pcs</b>
                    </div>
                    <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400">Commodity:</span>
                        <b class="text-indigo-700">{{ $receiving->part->komoditas_label }}</b>
                    </div>
                    <div class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400">Standard:</span>
                        <b class="text-slate-700">{{ $receiving->part->standar_aql }}</b>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-2 text-xs">
                <div>
                    <span class="text-slate-400 font-medium">Component Name:</span>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $receiving->part->nama_part }}</div>
                    <div class="font-mono text-slate-500 mt-0.5">{{ $receiving->part->kode_part }}</div>
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Supplier & Country:</span>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $receiving->supplier->nama_supplier }}</div>
                    <div class="text-slate-500 mt-0.5">{{ $receiving->supplier->negara_asal }}</div>
                </div>
                <div>
                    <label class="block text-slate-700 font-bold mb-1">Test Sample Size (Sampling Size):</label>
                    <select name="qty_sampel" id="sampleSizeSelect" class="w-full text-xs rounded-xl border-slate-200 py-2">
                        <option value="5" selected>5 Samples (Level II Standard)</option>
                        <option value="8">8 Samples</option>
                        <option value="13">13 Samples</option>
                        <option value="20">20 Samples</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 1. Visual & Packaging Inspection Gate -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                        <span>Visual & Packaging Inspection Gate</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Physical Packaging, Labeling & Visual Verification</h3>
                    <p class="text-xs text-slate-500">Inspect physical condition before taking dimensional measurements. Any visual failure triggers MRB review.</p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 text-slate-700 font-bold border border-slate-200">
                        <span class="material-symbols-outlined text-[15px] text-indigo-600">verified</span>
                        Standard: MIL-STD-105E Visual Level II
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-3">
                <!-- Check 1: Packaging & Label Integrity -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-indigo-600 text-[18px]">inventory_2</span>
                            <span>Packaging & Labeling</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            Reel/box sealed, undamaged, clean shipping mark matching PO & Lot.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="radio" name="visual_packaging" value="pass" checked onchange="checkVisualStatus()"
                                class="text-emerald-600 focus:ring-emerald-500">
                            <span>PASS</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 cursor-pointer">
                            <input type="radio" name="visual_packaging" value="fail" onchange="checkVisualStatus()"
                                class="text-rose-600 focus:ring-rose-500">
                            <span>FAIL</span>
                        </label>
                    </div>
                </div>

                <!-- Check 2: ESD Protection & Moisture Barrier Bag -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-indigo-600 text-[18px]">shield</span>
                            <span>ESD & Moisture (HIC)</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            Anti-static bag sealed, desiccant present, HIC card is blue (not pink).
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="radio" name="visual_esd_moisture" value="pass" checked onchange="checkVisualStatus()"
                                class="text-emerald-600 focus:ring-emerald-500">
                            <span>PASS</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 cursor-pointer">
                            <input type="radio" name="visual_esd_moisture" value="na" onchange="checkVisualStatus()"
                                class="text-slate-600 focus:ring-slate-500">
                            <span>N/A</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 cursor-pointer">
                            <input type="radio" name="visual_esd_moisture" value="fail" onchange="checkVisualStatus()"
                                class="text-rose-600 focus:ring-rose-500">
                            <span>FAIL</span>
                        </label>
                    </div>
                </div>

                <!-- Check 3: Physical Body Condition -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-indigo-600 text-[18px]">category</span>
                            <span>Body Integrity</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            No cracks, chipping, mold flash, burns, or physical deformation.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="radio" name="visual_body" value="pass" checked onchange="checkVisualStatus()"
                                class="text-emerald-600 focus:ring-emerald-500">
                            <span>PASS</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 cursor-pointer">
                            <input type="radio" name="visual_body" value="fail" onchange="checkVisualStatus()"
                                class="text-rose-600 focus:ring-rose-500">
                            <span>FAIL</span>
                        </label>
                    </div>
                </div>

                <!-- Check 4: Lead Pin / Terminal Condition -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-indigo-600 text-[18px]">cable</span>
                            <span>Lead Pin & Solderability</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            Pins straight (no bent leads), plating bright, no oxidation/tarnish.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 cursor-pointer">
                            <input type="radio" name="visual_terminal" value="pass" checked onchange="checkVisualStatus()"
                                class="text-emerald-600 focus:ring-emerald-500">
                            <span>PASS</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-700 cursor-pointer">
                            <input type="radio" name="visual_terminal" value="fail" onchange="checkVisualStatus()"
                                class="text-rose-600 focus:ring-rose-500">
                            <span>FAIL</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Visual Warning Alert -->
            <div id="visualFailAlert" class="hidden p-3.5 rounded-2xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[18px] text-rose-600 shrink-0">report_problem</span>
                <div>
                    <b>Visual Non-Conformance Alert:</b> One or more visual/packaging parameters have been marked as FAIL. Upon submission, this lot will be automatically routed to <b>Pending MRB Review</b>.
                </div>
            </div>
        </div>

        <!-- 2. Shelf-Life & Expiration Date Code Verification -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[16px]">event_available</span>
                        <span>Shelf-Life & Expiration Verification</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Date Code & Expiration Date Check</h3>
                    <p class="text-xs text-slate-500">Verify manufacturer date code and solderability / chemical expiration date.</p>
                </div>
                <div id="shelfLifeStatusBadge" class="hidden px-3 py-1 rounded-xl text-xs font-bold border">
                    <!-- Dynamically populated by JS -->
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">Date Code / Lot Code (DOM Code):</label>
                    <input type="text" name="date_code" placeholder="e.g. 2418, 2024-W18, or LOT26B"
                        class="w-full text-xs font-mono rounded-xl border border-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-indigo-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Printed production date code on reel or IC label.</span>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">Manufacturing Date (Optional):</label>
                    <input type="date" name="manufacturing_date" id="mfgDateInput"
                        class="w-full text-xs rounded-xl border border-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-indigo-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Factory production or packaging date.</span>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">Expiration Date / Solderability Limit:</label>
                    <input type="date" name="expiration_date" id="expDateInput" onchange="evaluateShelfLife()"
                        class="w-full text-xs rounded-xl border border-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-indigo-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Maximum shelf-life date before solder degradation.</span>
                </div>
            </div>

            <!-- Expiry Poka-Yoke Warning Banner -->
            <div id="expiryWarningAlert" class="hidden p-3.5 rounded-2xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center gap-2.5 animate-pulse">
                <span class="material-symbols-outlined text-[20px] text-rose-600 shrink-0">timer_off</span>
                <div>
                    <b>POKA-YOKE WARNING: MATERIAL EXPIRED!</b> The selected expiration date is in the past. This material is past its certified shelf-life and will automatically be held for <b>Material Review Board (MRB) Disposition</b> upon submission.
                </div>
            </div>

            <div id="nearExpiryAlert" class="hidden p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-800 text-xs flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[18px] text-amber-600 shrink-0">alarm</span>
                <div>
                    <b>Near Expiry Notice:</b> This material has less than 30 days of shelf-life remaining. Prioritize for immediate assembly line usage.
                </div>
            </div>
        </div>

        <!-- 3. Sample Measurements Table -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[16px]">straighten</span>
                        <span>Dimensional & Physical Parameter Measurement Sheet</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Record Sample Measurement Values</h3>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> PASS (In Tolerance)
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> FAIL (Out-of-Spec)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                            <th class="py-3 px-3">Inspection Parameter</th>
                            <th class="py-3 px-3 text-center">Nominal</th>
                            <th class="py-3 px-3 text-center">Min Limit</th>
                            <th class="py-3 px-3 text-center">Max Limit</th>
                            <th class="py-3 px-3 text-center">Unit</th>
                            @for($s = 1; $s <= 5; $s++)
                                <th class="py-3 px-3 text-center sample-col sample-col-{{ $s }}">S-{{ $s }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($receiving->part->parameters as $param)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-3 font-bold text-slate-800">
                                {{ $param->nama_parameter }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-700">
                                {{ $param->nominal !== null ? number_format($param->nominal, 2) : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-500">
                                {{ $param->batas_min !== null ? number_format($param->batas_min, 2) : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-500">
                                {{ $param->batas_max !== null ? number_format($param->batas_max, 2) : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-indigo-600 font-bold">
                                {{ $param->satuan }}
                            </td>
                            @for($s = 1; $s <= 5; $s++)
                            <td class="py-2 px-2 text-center sample-col sample-col-{{ $s }}">
                                <input type="number" step="any"
                                    name="measurements[{{ $param->id }}][{{ $s }}]"
                                    data-min="{{ $param->batas_min }}"
                                    data-max="{{ $param->batas_max }}"
                                    class="w-20 text-center text-xs font-mono py-1.5 px-2 rounded-xl border border-slate-200 measurement-input focus:ring-2 focus:ring-indigo-500"
                                    placeholder="{{ $param->nominal ?? '0.00' }}"
                                    oninput="validateInput(this)">
                            </td>
                            @endfor
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-slate-400">
                                No specification parameters registered for this component. Please contact Master Data.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="outOfSpecAlert" class="hidden mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-rose-600 shrink-0">report_problem</span>
                <div>
                    <b>Out-of-Spec Warning:</b> Sample measurement(s) detected outside nominal tolerance limits. Upon submission, this logbook will automatically route to <b>Pending MRB Review</b> for supervisor disposition.
                </div>
            </div>
        </div>

        <!-- Submission Action Buttons -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                Ensure all measurements are taken using ISO 9001 calibrated instruments.
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="button" onclick="submitAs('draft')" 
                    class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex-1 sm:flex-none">
                    Save as Draft
                </button>
                <button type="button" onclick="submitAs('submit')" 
                    class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition flex-1 sm:flex-none">
                    Submit Inspection Logbook &rarr;
                </button>
            </div>
        </div>
    </form>
    @endif

</div>

<script>
function checkVisualStatus() {
    const checks = ['visual_packaging', 'visual_esd_moisture', 'visual_body', 'visual_terminal'];
    let hasFail = false;
    checks.forEach(name => {
        const checkedRadio = document.querySelector(`input[name="${name}"]:checked`);
        if (checkedRadio && checkedRadio.value === 'fail') {
            hasFail = true;
        }
    });

    const visualAlert = document.getElementById('visualFailAlert');
    if (visualAlert) {
        if (hasFail) visualAlert.classList.remove('hidden');
        else visualAlert.classList.add('hidden');
    }
}

function evaluateShelfLife() {
    const expInput = document.getElementById('expDateInput');
    const badge = document.getElementById('shelfLifeStatusBadge');
    const expiryWarning = document.getElementById('expiryWarningAlert');
    const nearExpiry = document.getElementById('nearExpiryAlert');

    if (!expInput.value) {
        badge.classList.add('hidden');
        expiryWarning.classList.add('hidden');
        nearExpiry.classList.add('hidden');
        return;
    }

    const expDate = new Date(expInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const diffDays = Math.ceil((expDate - today) / (1000 * 60 * 60 * 24));

    badge.classList.remove('hidden', 'bg-rose-100', 'text-rose-800', 'border-rose-300', 'bg-amber-100', 'text-amber-800', 'border-amber-300', 'bg-emerald-100', 'text-emerald-800', 'border-emerald-300');

    if (diffDays < 0) {
        // EXPIRED
        badge.className = "px-3 py-1 rounded-xl text-xs font-bold border bg-rose-100 text-rose-800 border-rose-300 animate-pulse";
        badge.innerText = `EXPIRED (${Math.abs(diffDays)} days past limit)`;
        expiryWarning.classList.remove('hidden');
        nearExpiry.classList.add('hidden');
    } else if (diffDays <= 30) {
        // Near Expiry
        badge.className = "px-3 py-1 rounded-xl text-xs font-bold border bg-amber-100 text-amber-800 border-amber-300";
        badge.innerText = `NEAR EXPIRY (${diffDays} days remaining)`;
        expiryWarning.classList.add('hidden');
        nearExpiry.classList.remove('hidden');
    } else {
        // Normal
        badge.className = "px-3 py-1 rounded-xl text-xs font-bold border bg-emerald-100 text-emerald-800 border-emerald-300";
        badge.innerText = `VALID SHELF-LIFE (${diffDays} days remaining)`;
        expiryWarning.classList.add('hidden');
        nearExpiry.classList.add('hidden');
    }
}

function validateInput(input) {
    const min = parseFloat(input.dataset.min);
    const max = parseFloat(input.dataset.max);
    const val = parseFloat(input.value);

    if (isNaN(val)) {
        input.classList.remove('bg-emerald-50', 'border-emerald-500', 'text-emerald-700', 'bg-rose-50', 'border-rose-500', 'text-rose-700');
        input.classList.add('border-slate-200');
    } else if ((!isNaN(min) && val < min) || (!isNaN(max) && val > max)) {
        input.classList.remove('bg-emerald-50', 'border-emerald-500', 'text-emerald-700', 'border-slate-200');
        input.classList.add('bg-rose-50', 'border-rose-500', 'text-rose-700', 'font-bold');
    } else {
        input.classList.remove('bg-rose-50', 'border-rose-500', 'text-rose-700', 'border-slate-200');
        input.classList.add('bg-emerald-50', 'border-emerald-500', 'text-emerald-700', 'font-bold');
    }

    checkAllOutOfSpec();
}

function checkAllOutOfSpec() {
    const inputs = document.querySelectorAll('.measurement-input');
    let hasFail = false;
    inputs.forEach(inp => {
        if (inp.classList.contains('border-rose-500')) {
            hasFail = true;
        }
    });
    const alertBox = document.getElementById('outOfSpecAlert');
    if (alertBox) {
        if (hasFail) alertBox.classList.remove('hidden');
        else alertBox.classList.add('hidden');
    }
}

function submitAs(type) {
    document.getElementById('actionTypeInput').value = type;
    document.getElementById('inspectionForm').submit();
}
</script>
@endsection

