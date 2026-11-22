@extends('layouts.app')
@section('title', 'Quality Inspection Logbook - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-xs shrink-0">
                <span class="material-symbols-outlined text-[26px]">assignment</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">IQC Quality Inspection Sheet</h1>
                <p class="text-xs text-slate-500">Record incoming material quality verification and parameter measurements.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-lg text-xs font-bold border
                @if(auth()->user()->role === 'inspector_ee') bg-emerald-50 text-emerald-700 border-emerald-200
                @elseif(auth()->user()->role === 'inspector_automotive') bg-teal-50 text-teal-700 border-teal-200
                @elseif(auth()->user()->role === 'inspector_wire_harness') bg-purple-50 text-purple-700 border-purple-200
                @else bg-indigo-50 text-indigo-700 border-indigo-200 @endif">
                Commodity Gate: {{ auth()->user()->role_label }}
            </span>
            <a href="{{ route('logbooks.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                Back to History
            </a>
        </div>
    </div>

    @if(!$receiving)
    <!-- Step 1: Select Material from Available Inbound Queue -->
    <div class="space-y-6">

        <!-- Quick Lot Barcode Scanner -->
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
                    <input type="text" name="search" value="{{ request('search') }}" required autofocus autocomplete="off"
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

        <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between gap-4 flex-wrap pb-2 border-b border-slate-100">
                <div>
                    @if(request('search'))
                        <h3 class="text-base font-bold text-slate-900">Search Results for GRN / Lot: "<span class="font-mono text-blue-600">{{ request('search') }}</span>"</h3>
                    @else
                        <h3 class="text-base font-bold text-slate-900">Available Inbound Queue ({{ auth()->user()->role_label }})</h3>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if(request('search'))
                        <a href="{{ route('logbooks.create') }}" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 transition flex items-center gap-1">
                            <span>Reset Search</span>
                            <span class="material-symbols-outlined text-[14px]">close</span>
                        </a>
                    @endif
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                        <input type="text" id="queueFilterInput" onkeyup="filterQueueTable(this.value)" placeholder="Quick filter queue..."
                            class="pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition w-44 sm:w-56">
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shrink-0">
                        <span id="queueCountDisplay">{{ $pendingReceivings->count() }}</span> Lot(s) Available
                    </span>
                </div>
            </div>

            <!-- Inbound Queue Data Table (Memanjang ke Bawah / Full-Width Enterprise Row Table) -->
            <div class="overflow-x-auto rounded-lg border border-slate-200/80">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">GRN & Lot No</th>
                            <th class="py-3 px-4">Component & Commodity</th>
                            <th class="py-3 px-4">Supplier & Origin</th>
                            <th class="py-3 px-4 text-center">Inbound Qty</th>
                            <th class="py-3 px-4">Arrival & Aging</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="inboundQueueTableBody">
                        @forelse($pendingReceivings as $item)
                        @php
                            $searchPayload = strtolower(
                                ($item->no_grn_iqa ?? '') . ' ' .
                                ($item->no_lot ?? '') . ' ' .
                                ($item->no_po ?? '') . ' ' .
                                ($item->part->kode_part ?? '') . ' ' .
                                ($item->part->nama_part ?? '') . ' ' .
                                ($item->part->komoditas_label ?? '') . ' ' .
                                ($item->supplier->nama_supplier ?? '') . ' ' .
                                ($item->supplier->negara_asal ?? '') . ' ' .
                                ($item->aging_status ?? '')
                            );
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition group queue-row" data-search="{{ $searchPayload }}">
                            <!-- GRN & Lot -->
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-xs sm:text-sm font-bold text-blue-600 bg-white px-2 py-0.5 rounded border border-blue-100 inline-block">{{ $item->no_grn_iqa }}</span>
                                <div class="text-[11px] text-slate-500 font-mono mt-1">
                                    Lot: <strong class="text-slate-800">{{ $item->no_lot }}</strong>
                                    @if($item->no_po)
                                        <span class="text-slate-300 mx-1">|</span> PO: <span class="text-slate-600">{{ $item->no_po }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Component & Commodity -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 text-xs sm:text-sm">{{ $item->part->nama_part ?? '-' }}</div>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <span class="font-mono text-xs text-slate-500">Part: <strong class="text-slate-700">{{ $item->part->kode_part ?? '-' }}</strong></span>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $item->part->komoditas_label }}
                                    </span>
                                </div>
                            </td>

                            <!-- Supplier & Origin -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 text-xs">{{ $item->supplier->nama_supplier ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $item->supplier->negara_asal ?? 'Indonesia' }}</div>
                            </td>

                            <!-- Inbound Qty -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono font-bold text-slate-800 text-xs px-2.5 py-1 rounded-md bg-slate-100 border border-slate-200 inline-block">
                                    {{ number_format($item->qty_datang) }} pcs
                                </span>
                            </td>

                            <!-- Arrival Date & Queue Aging -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $item->aging_badge_class }}">
                                    {{ $item->aging_status }}
                                </span>
                                <div class="text-[11px] text-slate-400 font-mono mt-1">
                                    {{ $item->tanggal_terima ? $item->tanggal_terima->format('d M Y, H:i') : '-' }}
                                </div>
                            </td>

                            <!-- Action Button -->
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('logbooks.create', ['receiving_id' => $item->id]) }}" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition group-hover:shadow-sm shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">play_arrow</span>
                                    <span>Inspect This Lot &rarr;</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                    <span class="material-symbols-outlined text-[28px]">search_off</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-700">
                                    @if(request('search'))
                                        No Inbound Material Found
                                    @else
                                        Queue Is Currently Empty
                                    @endif
                                </h4>
                                <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                                    @if(request('search'))
                                        No material matching GR / Lot Number "<strong class="font-mono text-slate-700">{{ request('search') }}</strong>" was found in your available queue ({{ auth()->user()->role_label }}).
                                    @else
                                        No inbound materials currently awaiting inspection for your commodity.
                                    @endif
                                </p>
                                @if(request('search'))
                                <div class="mt-3">
                                    <a href="{{ route('logbooks.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 transition">
                                        <span class="material-symbols-outlined text-[15px]">refresh</span>
                                        <span>Reset Search & View All Queue</span>
                                    </a>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="queueNoMatchMessage" class="hidden py-8 text-center text-slate-400 text-xs">
                <span class="material-symbols-outlined text-[24px] text-slate-300 block mb-1">filter_alt_off</span>
                No matching material found in queue for "<span id="queueQuerySpan" class="font-semibold text-slate-700"></span>".
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
        <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-blue-600 flex items-center justify-center font-bold font-mono">
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

        @php
            $isWireHarness = ($receiving->part->kategori_komoditas ?? '') === 'wire_harness';
        @endphp

        <!-- 1. Visual & Packaging Inspection Gate -->
        <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">visibility</span>
                    Visual & Packaging Inspection Gate
                </h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold border border-slate-200">
                        <span class="material-symbols-outlined text-[15px] text-blue-600">verified</span>
                        @if($isWireHarness)
                            Standard: IPC/WHMA-A-620 Cable & Wire Quality
                        @else
                            Standard: MIL-STD-105E Visual Level II
                        @endif
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-3">
                @if($isWireHarness)
                <!-- Wire Harness Check 1: Spool & Bundle Packaging / Spec Labeling -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">inventory_2</span>
                            <span>Spool & Spec Labeling</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            Spool/bundle intact, AWG gauge spec, color code & lot label verified against PO.
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

                <!-- Wire Harness Check 2: Moisture & Static Shield (Exempt for Wire) -->
                <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-700 font-bold text-xs">
                            <span class="material-symbols-outlined text-slate-500 text-[18px]">shield</span>
                            <span>Moisture / ESD (Exempt)</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            Non-ESD/MSL Wire Commodity. Moisture barrier bag & desiccant HIC are exempt.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-200 text-slate-700">
                            <span class="material-symbols-outlined text-[14px]">check</span>
                            EXEMPT (N/A)
                        </span>
                        <input type="hidden" name="visual_esd_moisture" value="na">
                        <span class="text-[10px] text-slate-400 font-medium">Passive Hardware</span>
                    </div>
                </div>

                <!-- Wire Harness Check 3: Cable Insulation & Outer Jacket Integrity -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">cable</span>
                            <span>Insulation & Jacket Integrity</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            No cuts, tears, burns, pinholes, exposed copper strands, or jacket damage.
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

                <!-- Wire Harness Check 4: Connector Housing & Terminal Crimp -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">electrical_services</span>
                            <span>Housing & Terminal Crimp</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            Housing not cracked, locking lance intact, terminal retention secure (no push-out).
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
                @else
                <!-- Check 1: Packaging & Label Integrity (EE / Standard) -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">inventory_2</span>
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
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">shield</span>
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
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">category</span>
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
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">cable</span>
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
                @endif
            </div>

            <!-- Visual Warning Alert -->
            <div id="visualFailAlert" class="hidden p-3.5 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[18px] text-rose-600 shrink-0">report_problem</span>
                <div>
                    <b>Visual Non-Conformance Alert:</b> One or more visual/packaging parameters have been marked as FAIL. Upon submission, this lot will be automatically routed to <b>Pending MRB Review</b>.
                </div>
            </div>
        </div>

        <!-- 2. Shelf-Life & Expiration Date Code Verification -->
        <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">event_available</span>
                    @if($isWireHarness)
                        Batch / Date Code Verification
                    @else
                        Date Code & Shelf-Life Verification
                    @endif
                </h3>
                @if($isWireHarness)
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        NON-PERISHABLE (EXEMPT FROM EXPIRY)
                    </span>
                @else
                    <div id="shelfLifeStatusBadge" class="hidden px-3 py-1 rounded-xl text-xs font-bold border">
                        <!-- Dynamically populated by JS -->
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">
                        @if($isWireHarness)
                            Spool / Batch Code (DOM):
                        @else
                            Date Code / Lot Code (DOM Code):
                        @endif
                    </label>
                    <input type="text" name="date_code" placeholder="{{ $isWireHarness ? 'e.g. BATCH-2405-A or SPOOL#12' : 'e.g. 2418, 2024-W18, or LOT26B' }}"
                        class="w-full text-xs font-mono rounded-lg border border-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-blue-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">
                        {{ $isWireHarness ? 'Printed production batch or spool ID on wire harness label.' : 'Printed production date code on reel or IC label.' }}
                    </span>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">
                        @if($isWireHarness)
                            Assembly / Extrusion Date (Optional):
                        @else
                            Manufacturing Date (Optional):
                        @endif
                    </label>
                    <input type="date" name="manufacturing_date" id="mfgDateInput"
                        class="w-full text-xs rounded-lg border border-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-blue-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">
                        {{ $isWireHarness ? 'Factory wire extrusion or crimping assembly date.' : 'Factory production or packaging date.' }}
                    </span>
                </div>

                @if($isWireHarness)
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <span class="text-slate-700 font-bold text-xs block mb-1">Shelf-Life Expiration Limit:</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-200 text-slate-700">
                            <span class="material-symbols-outlined text-[13px]">all_inclusive</span>
                            Non-Perishable / Exempt
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Wire harness & raw copper assemblies do not suffer MSL solder degradation.</p>
                    <input type="hidden" name="expiration_date" id="expDateInput" value="">
                </div>
                @else
                <div>
                    <label class="block text-slate-700 font-bold text-xs mb-1">Expiration Date / Solderability Limit:</label>
                    <input type="date" name="expiration_date" id="expDateInput" onchange="evaluateShelfLife()"
                        class="w-full text-xs rounded-lg border border-slate-200 py-2.5 px-3 focus:ring-2 focus:ring-blue-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Maximum shelf-life date before solder degradation.</span>
                </div>
                @endif
            </div>

            @if(!$isWireHarness)
            <!-- Expiry Poka-Yoke Warning Banner -->
            <div id="expiryWarningAlert" class="hidden p-3.5 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center gap-2.5 animate-pulse">
                <span class="material-symbols-outlined text-[20px] text-rose-600 shrink-0">timer_off</span>
                <div>
                    <b>POKA-YOKE WARNING: MATERIAL EXPIRED!</b> The selected expiration date is in the past. This material is past its certified shelf-life and will automatically be held for <b>Material Review Board (MRB) Disposition</b> upon submission.
                </div>
            </div>

            <div id="nearExpiryAlert" class="hidden p-3.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-800 text-xs flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[18px] text-amber-600 shrink-0">alarm</span>
                <div>
                    <b>Near Expiry Notice:</b> This material has less than 30 days of shelf-life remaining. Prioritize for immediate assembly line usage.
                </div>
            </div>
            @endif
        </div>

        <!-- 3. Sample Measurements Table -->
        <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">straighten</span>
                    Dimensional & Parameter Measurement Sheet
                </h3>
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
                            <td class="py-3 px-3 text-center font-mono text-blue-600 font-bold">
                                {{ $param->satuan }}
                            </td>
                            @for($s = 1; $s <= 5; $s++)
                            <td class="py-2 px-2 text-center sample-col sample-col-{{ $s }}">
                                <input type="number" step="any"
                                    name="measurements[{{ $param->id }}][{{ $s }}]"
                                    data-min="{{ $param->batas_min }}"
                                    data-max="{{ $param->batas_max }}"
                                    class="w-20 text-center text-xs font-mono py-1.5 px-2 rounded-lg border border-slate-200 measurement-input focus:ring-2 focus:ring-blue-500"
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

            <div id="outOfSpecAlert" class="hidden mt-4 p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-rose-600 shrink-0">report_problem</span>
                <div>
                    <b>Out-of-Spec Warning:</b> Sample measurement(s) detected outside nominal tolerance limits. Upon submission, this logbook will automatically route to <b>Pending MRB Review</b> for supervisor disposition.
                </div>
            </div>
        </div>

        <!-- Submission Action Buttons -->
        <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500">
                Ensure all measurements are taken using ISO 9001 calibrated instruments.
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="button" onclick="submitAs('draft')" 
                    class="px-5 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex-1 sm:flex-none">
                    Save as Draft
                </button>
                <button type="button" onclick="submitAs('submit')" 
                    class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition flex-1 sm:flex-none">
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

function filterQueueTable(val) {
    const q = (val || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.queue-row');
    let visibleCount = 0;
    rows.forEach(r => {
        const text = r.getAttribute('data-search') || '';
        if (!q || text.includes(q)) {
            r.classList.remove('hidden');
            visibleCount++;
        } else {
            r.classList.add('hidden');
        }
    });

    const noMatch = document.getElementById('queueNoMatchMessage');
    const span = document.getElementById('queueQuerySpan');
    const countDisplay = document.getElementById('queueCountDisplay');

    if (countDisplay) {
        countDisplay.textContent = visibleCount;
    }

    if (noMatch) {
        if (visibleCount === 0 && rows.length > 0) {
            noMatch.classList.remove('hidden');
            if (span) span.textContent = val;
        } else {
            noMatch.classList.add('hidden');
        }
    }
}
</script>
@endsection

