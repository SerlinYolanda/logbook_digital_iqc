@extends('layouts.app')
@section('title', 'IQC Inspector Dashboard - Digital IQC Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-12">

    <!-- Inspector Greeting & Shift Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/70 backdrop-blur-md rounded-3xl p-6 sm:p-7 border border-slate-200/60 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[30px]">engineering</span>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        IQC Inspector Dashboard
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
                    Welcome on duty, <span class="font-semibold text-slate-800">{{ auth()->user()->name ?? 'Inspector' }}</span>. Monitor incoming material queue, verify GRN, and conduct MIL-STD-105E sampling inspections.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
            <button type="button" onclick="toggleGrnModal(true)" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 hover:shadow-lg hover:shadow-indigo-500/30 transition-all duration-300 cursor-pointer active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>Register New GRN</span>
            </button>
            <button type="button" onclick="window.location.reload()" title="Refresh Data" class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200/80 text-slate-600 border border-slate-200/70 transition-all duration-200 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">sync</span>
            </button>
        </div>
    </div>

    <!-- 3 Modern Metric Cards with Smooth Hover & Micro-interactions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Antrean Hari Ini -->
        <div class="group bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-indigo-50/50 rounded-full blur-2xl group-hover:bg-indigo-100/50 transition-all duration-500 pointer-events-none"></div>
            <div>
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Today's Incoming Queue
                    </span>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50/80 text-indigo-600 flex items-center justify-center border border-indigo-100/80 shadow-xs group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                        <span class="material-symbols-outlined text-[24px]">inbox</span>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="font-bold text-3xl text-slate-800 tracking-tight">{{ $arrivedTodayCount }}</span>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">GRN Arrived</span>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100/80 flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-50/80 text-indigo-700 border border-indigo-100/80">
                    <span class="material-symbols-outlined text-[14px]">schedule</span> {{ $queueCount }} Waiting at Dock
                </span>
                <span class="text-[11px] text-slate-400 font-medium">IQC Incoming Area</span>
            </div>
        </div>

        <!-- Card 2: Pending SPV Disposition -->
        <div class="group bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-amber-50/50 rounded-full blur-2xl group-hover:bg-amber-100/50 transition-all duration-500 pointer-events-none"></div>
            <div>
                <div class="flex items-start justify-between gap-3 mt-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Pending SPV Disposition
                    </span>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50/80 text-amber-600 flex items-center justify-center border border-amber-100/80 shadow-xs group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300">
                        <span class="material-symbols-outlined text-[24px]">assignment_late</span>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="font-bold text-3xl text-slate-800 tracking-tight">{{ $pendingDispositionCount }}</span>
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Lot Review</span>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100/80 flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                    <span class="material-symbols-outlined text-[14px]">pending_actions</span> {{ $pendingCount }} Pending SPV Review
                </span>
                <span class="text-[11px] text-slate-400 font-medium">Supervisor Review</span>
            </div>
        </div>

        <!-- Card 3: Telah Diinspeksi Hari Ini -->
        <div class="group bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-emerald-50/50 rounded-full blur-2xl group-hover:bg-emerald-100/50 transition-all duration-500 pointer-events-none"></div>
            <div>
                <div class="flex items-start justify-between gap-3 mt-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        Inspected Today
                    </span>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50/80 text-emerald-600 flex items-center justify-center border border-emerald-100/80 shadow-xs group-hover:scale-110 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                        <span class="material-symbols-outlined text-[24px]">verified</span>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="font-bold text-3xl text-slate-800 tracking-tight">{{ $inspectedTodayCount }}</span>
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Lot Tested</span>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100/80 flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span> Quality Compliant
                </span>
                <span class="text-[11px] text-slate-400 font-medium">{{ $approvedCount }} Total Passed Lots</span>
            </div>
        </div>
    </div>

    <!-- Quick Scanner & GRN Verification -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        <!-- Header Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 shrink-0">
                    <span class="material-symbols-outlined text-[22px]">qr_code_scanner</span>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-slate-800 font-bold text-lg tracking-tight">
                            Quick Scanner & GRN Verification
                        </h2>
                        <span class="text-[10px] uppercase font-semibold tracking-wider text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-full px-2.5 py-0.5">
                            Poka-Yoke
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Scan GRN barcode to directly initiate inspection testing form.
                    </p>
                </div>
            </div>
            <!-- Status Badge ("Barcode Sensor Ready") -->
            <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-1 text-xs font-medium flex items-center gap-1.5 self-start sm:self-auto shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Barcode Sensor Ready</span>
            </div>
        </div>

        <!-- Scan Input Form -->
        <form action="{{ route('incoming-materials.scan') }}" method="GET" id="quickScannerForm">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">qr_code_2</span>
                    <input type="text" id="quickScannerInput" name="grn" onkeydown="if(event.key==='Enter'){event.preventDefault(); executeQuickScan();}" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-indigo-500 rounded-xl text-sm font-mono focus:bg-white focus:ring-4 focus:ring-indigo-500/10 text-slate-900 placeholder-slate-400 uppercase tracking-wide transition-all" placeholder="e.g. GRN-20261021-001..." autocomplete="off">
                </div>
                <button type="button" onclick="executeQuickScan()" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0 active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    <span>Verify & Inspect</span>
                </button>
            </div>

            <!-- Sample Tags -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2 flex-wrap">
                <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">lightbulb</span> Sample Barcodes:
                </span>
                <button type="button" onclick="setAndScan('GRN-20261021-001')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs px-2.5 py-1 font-mono transition cursor-pointer">
                    GRN-20261021-001
                </button>
                <button type="button" onclick="setAndScan('GRN-20261021-002')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs px-2.5 py-1 font-mono transition cursor-pointer">
                    GRN-20261021-002
                </button>
                <button type="button" onclick="setAndScan('WH-ENG-901-HARN')" title="Test Poka-Yoke error prevention on Wire Harness component" class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs px-2.5 py-1 font-mono transition cursor-pointer flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>WH-ENG-901-HARN (Wire Harness)</span>
                </button>
                <button type="button" onclick="setAndScan('GRN-WH-20261025-01')" title="Test Poka-Yoke queue blockage on Wire Harness GRN" class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs px-2.5 py-1 font-mono transition cursor-pointer">
                    GRN-WH-20261025-01
                </button>
                <button type="button" onclick="setAndScan('AUTO-CHS-703-BRK')" title="Test Poka-Yoke error prevention on Automotive component" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs px-2.5 py-1 font-mono transition cursor-pointer flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                    <span>AUTO-CHS-703-BRK (Automotive)</span>
                </button>
                <button type="button" onclick="setAndScan('GRN-AUTO-20261025-01')" title="Test Poka-Yoke queue blockage on Automotive GRN" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs px-2.5 py-1 font-mono transition cursor-pointer">
                    GRN-AUTO-20261025-01
                </button>
            </div>
        </form>
    </div>

    <!-- Table Section: Material Inspection Queue -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">

        <!-- Table Header & Controls -->
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Title -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 shrink-0">
                    <span class="material-symbols-outlined text-[20px]">format_list_bulleted</span>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base font-bold text-slate-800 tracking-tight">
                            Material Inspection Queue
                        </h2>
                        <span id="queueBadgeCount" class="bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs px-2.5 py-0.5 rounded-full font-semibold">
                            {{ $queueCount }} Active Queue
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Incoming GRN arrival order at IQC area — ready for testing.
                    </p>
                </div>
            </div>

            <!-- Controls -->
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <div class="relative w-full sm:w-60">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[17px]">search</span>
                    <input type="text" id="queueSearchInput" oninput="filterQueueTable(this.value)"
                        class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 focus:border-indigo-500 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/10 placeholder-slate-400 text-slate-700 transition-all"
                        placeholder="Search Part / Vendor / GRN...">
                </div>
                <button type="button" onclick="resetQueueFilter()" title="Reset Filter"
                    class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 transition cursor-pointer shrink-0">
                    <span class="material-symbols-outlined text-[17px]">refresh</span>
                </button>
                <button type="button" id="registerGrnBtn" onclick="toggleGrnModal(true)"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-1.5 transition-all cursor-pointer shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Register GRN</span>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-slate-100" id="queueTable">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Arrival Time</th>
                        <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider">GRN No.</th>
                        <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Component / Part</th>
                        <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Supplier</th>
                        <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Quantity</th>
                        <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm" id="queueTableBody">
                    @forelse($queue as $item)
                    @php
                        $pNum = strtoupper($item->part->part_number ?? '');
                        $pName = strtoupper($item->part->part_name ?? '');

                        $isWireHarness = str_starts_with($pNum, 'WH-') || str_contains($pName, 'HARNESS') || str_contains($pName, 'WIRING');
                        $isAutomotive = str_starts_with($pNum, 'AUTO-') || str_contains($pName, 'AUTOMOTIVE') || str_contains($pName, 'CHASSIS');

                        $isBlocked = $isWireHarness || $isAutomotive;
                        $blockedCategory = $isWireHarness ? 'Wire Harness (Non-EE)' : ($isAutomotive ? 'Automotive Part (Non-EE)' : '');

                        $startUrl = route('inspections.create', [
                            'incoming_id' => $item->id,
                            'grn'         => $item->grn_number,
                            'part_id'     => $item->part_id,
                            'qty'         => $item->quantity,
                            'lot'         => $item->lot_number,
                            'locked'      => 1,
                        ]);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors duration-150 queue-row {{ $isWireHarness ? 'bg-amber-50/20' : ($isAutomotive ? 'bg-rose-50/20' : '') }}"
                        data-grn="{{ $item->grn_number }}"
                        data-part="{{ $item->part->part_name ?? '' }}"
                        data-vendor="{{ $item->part->supplier_name ?? '' }}"
                        data-url="{{ $isBlocked ? 'javascript:showPokaYokeModal(\'' . $item->grn_number . '\', \'' . $blockedCategory . '\')' : $startUrl }}">

                        {{-- Arrival Time --}}
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-1.5 text-slate-600 text-sm">
                                <span class="material-symbols-outlined text-slate-400 text-[15px]">schedule</span>
                                <span>{{ $item->arrived_at->format('d M Y, H:i') }}</span>
                            </div>
                        </td>

                        {{-- GRN No. --}}
                        <td class="py-4 px-5">
                            <span class="bg-indigo-50 text-indigo-700 font-mono text-xs font-semibold px-2.5 py-1 rounded-md border border-indigo-100/60">
                                {{ $item->grn_number }}
                            </span>
                        </td>

                        {{-- Component / Part --}}
                        <td class="py-4 px-5">
                            <div class="font-semibold text-slate-800 text-sm flex items-center gap-2 flex-wrap">
                                <span>{{ $item->part->part_name ?? '-' }}</span>
                                @if($isWireHarness)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Wire Harness (Non-EE)
                                    </span>
                                @elseif($isAutomotive)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        Automotive Part (Non-EE)
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5 font-mono flex items-center gap-1.5 flex-wrap">
                                @if($item->part->part_number)
                                    <span>PN: {{ $item->part->part_number }}</span>
                                @endif
                                @if($item->lot_number)
                                    <span class="text-slate-300">|</span>
                                    <span>LOT: {{ $item->lot_number }}</span>
                                @endif
                            </div>
                        </td>

                        {{-- Supplier --}}
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-300 shrink-0"></span>
                                <span class="text-slate-600 text-sm">{{ $item->part->supplier_name ?? '-' }}</span>
                            </div>
                        </td>

                        {{-- Quantity --}}
                        <td class="py-4 px-5 text-right">
                            <span class="font-semibold text-slate-800 text-sm">{{ number_format($item->quantity) }}</span>
                            <span class="text-xs text-slate-400 ml-0.5">Pcs</span>
                        </td>

                        {{-- Action --}}
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-1.5 justify-center">
                                @if(auth()->user()->role === 'inspector')
                                    @if($isBlocked)
                                    <button type="button"
                                        onclick="showPokaYokeModal('{{ $item->grn_number }} ({{ $item->part->part_number ?? '' }})', '{{ $blockedCategory }}');"
                                        title="Poka-Yoke error prevention will block this inspection"
                                        class="inline-flex items-center gap-1.5 {{ $isWireHarness ? 'bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white border-amber-300' : 'bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border-rose-300' }} border rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all cursor-pointer">
                                        <span class="material-symbols-outlined text-[15px]">shield_lock</span>
                                        <span>Start Inspection</span>
                                    </button>
                                    @else
                                    <a href="{{ $startUrl }}"
                                        class="inline-flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200/80 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all">
                                        <span class="material-symbols-outlined text-[15px]">play_arrow</span>
                                        <span>Start Inspection</span>
                                    </a>
                                    @endif
                                @else
                                <span class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200/80 px-3 py-1.5 rounded-lg">
                                    Waiting Inspector
                                </span>
                                @endif

                                <form action="{{ route('incoming-materials.destroy', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Delete GRN {{ $item->grn_number }} from queue?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete from queue"
                                        class="ml-2 text-slate-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition-all duration-150 inline-flex items-center justify-center cursor-pointer">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-14">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-400 flex items-center justify-center mb-3 border border-indigo-100">
                                    <span class="material-symbols-outlined text-[26px]">inventory_2</span>
                                </div>
                                <div class="font-semibold text-slate-700 text-sm mb-1">No incoming materials in queue</div>
                                <p class="text-slate-400 text-xs mb-4 leading-relaxed text-center">
                                    All materials have been inspected or no new shipments arrived.
                                </p>
                                <button type="button" onclick="toggleGrnModal(true)"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-medium inline-flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                                    <span class="material-symbols-outlined text-[15px]">add</span>
                                    <span>Register Incoming GRN</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-1.5 text-xs text-slate-400">
                <span class="material-symbols-outlined text-[14px] text-slate-400">verified_user</span>
                <span>Sampling Standard: MIL-STD-105E &mdash; AQL Level II Normal (Major 0.65 / Minor 1.5)</span>
            </div>
            <div class="text-xs text-slate-400 font-mono flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Sync: {{ now()->format('H:i:s') }}</span>
            </div>
        </div>
    </div>

    <!-- Table Section: Riwayat Logbook Terkini (Recent Transactions) -->
    <div class="bg-white border border-slate-200/80 rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
        <div class="p-6 sm:p-7 border-b border-slate-100 flex items-center justify-between gap-4 bg-gradient-to-b from-slate-50/60 to-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100/80">
                    <span class="material-symbols-outlined text-[22px]">history_edu</span>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                        Recent IQC Inspections
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Latest verified inspection test results and supervisor disposition statuses.
                    </p>
                </div>
            </div>
            <a href="{{ route('inspections.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50/80 hover:bg-indigo-100/80 transition-colors">
                <span>View Full Logbook</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">PO / Ref No.</th>
                        <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Component / Part</th>
                        <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Vendor / Supplier</th>
                        <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Lot / Sample</th>
                        <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Disposition Status</th>
                        <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentInspections as $ins)
                        <tr class="hover:bg-indigo-50/20 transition-colors duration-150">
                            <td class="py-4 px-6 font-mono font-bold text-indigo-700 text-xs">
                                {{ $ins->po_number }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800 text-sm">{{ $ins->part->part_name ?? '-' }}</div>
                                <div class="font-mono text-xs text-slate-400 mt-0.5">{{ $ins->part->part_number ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-semibold text-slate-700">{{ $ins->part->supplier_name ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-6 text-center font-mono">
                                <div class="font-bold text-slate-800">{{ number_format($ins->lot_quantity) }} Pcs</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Sample: {{ $ins->sample_size }}</div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($ins->status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> PASS
                                    </span>
                                @elseif($ins->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> REJECT
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> PENDING REVIEW
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <a href="{{ route('inspections.show', $ins->id) }}" class="px-3.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50/80 hover:bg-indigo-100 rounded-xl transition inline-flex items-center gap-1.5 border border-indigo-100 cursor-pointer">
                                    <span class="material-symbols-outlined text-[15px]">visibility</span>
                                    <span>Details</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400 text-xs">
                                No inspection transaction history recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modern Poka-Yoke Rejected Modal Pop-up -->
<div id="pokaYokeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-md hidden p-4 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-rose-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-4 border border-rose-200/80 shadow-xs">
            <span class="material-symbols-outlined text-[32px]">gpp_bad</span>
        </div>
        <h3 class="text-xl font-extrabold text-slate-900 text-center tracking-tight">Poka-Yoke: Material Blocked by System!</h3>
        <p class="text-xs text-rose-600 font-bold text-center mt-1 uppercase tracking-wide">Component Does Not Match EE Part Standard</p>
        
        <div class="bg-rose-50/80 border border-rose-200/80 rounded-2xl p-4 my-5 text-xs text-rose-900 space-y-2 font-mono">
            <div class="flex justify-between">
                <span class="text-slate-500">Material Code:</span>
                <span id="pokaYokeCode" class="font-bold text-rose-700">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Category / Type:</span>
                <span id="pokaYokeCategory" class="font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Prevention Rule:</span>
                <span class="font-bold text-rose-700">Strict Section Separation (Cross-Defect Prevention)</span>
            </div>
        </div>

        <p id="pokaYokeDesc" class="text-xs text-slate-500 text-center leading-relaxed">
            The Poka-Yoke mistake-proofing guard automatically rejected this material because EE Part inspectors are not authorized to test non-EE components.
        </p>

        <div class="mt-6 flex gap-3">
            <button type="button" onclick="closePokaYokeModal()" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                Close & Rescan
            </button>
            <a id="pokaYokeReportBtn" href="{{ route('poka-yoke.denied') }}" class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1 shadow-lg shadow-rose-600/25">
                <span class="material-symbols-outlined text-[16px]">info</span>
                <span>View Full SOP Report</span>
            </a>
        </div>
    </div>
</div>

<!-- Modern GRN Not Found Alert Modal -->
<div id="grnNotFoundModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-md hidden p-4 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-amber-100" style="animation: grnNotFoundBounce 0.35s cubic-bezier(0.34, 1.56, 0.64, 1)">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 border border-amber-200/80 shadow-xs">
            <span class="material-symbols-outlined text-[32px]">search_off</span>
        </div>
        <h3 class="text-xl font-extrabold text-slate-900 text-center tracking-tight">GRN Number Not Found</h3>
        <p class="text-xs text-amber-600 font-bold text-center mt-1 uppercase tracking-wide">Incoming Material Not Registered Yet</p>

        <div class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-4 my-5 text-xs text-amber-900 space-y-2">
            <div class="flex justify-between">
                <span class="text-slate-500">Scanned GRN:</span>
                <span id="grnNotFoundCode" class="font-bold font-mono text-amber-800">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Database Status:</span>
                <span class="font-bold text-amber-700">Not Registered in IQC Queue</span>
            </div>
        </div>

        <p class="text-xs text-slate-500 text-center leading-relaxed">
            This GRN number is not yet recorded as incoming material. Please register incoming material first before starting the inspection.
        </p>

        <div class="mt-6 flex gap-3">
            <button type="button" onclick="closeGrnNotFoundModal()" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                Cancel
            </button>
            <button type="button" onclick="closeGrnNotFoundModal(); toggleGrnModal(true);" class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/25">
                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                <span>Register GRN Now</span>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes grnNotFoundBounce {
    0% { opacity: 0; transform: scale(0.9) translateY(15px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<!-- Modern Register Incoming GRN Modal -->
<div id="registerGrnModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-md hidden p-4 transition-all duration-300">
    <div class="bg-white rounded-3xl max-w-lg w-full p-7 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100/80 shadow-xs">
                    <span class="material-symbols-outlined text-[22px]">inventory_2</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Register Incoming Material (GRN)</h3>
                    <p class="text-xs text-slate-500">Add incoming part arrival into the IQC inspection queue.</p>
                </div>
            </div>
            <button type="button" onclick="toggleGrnModal(false)" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form action="{{ route('incoming-materials.store') }}" method="POST" class="mt-5 space-y-4 text-xs">
            @csrf
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px]">GRN Number *</label>
                    <button type="button" onclick="autoFillGrn()" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 bg-indigo-50 px-2 py-0.5 rounded-lg transition cursor-pointer">
                        <span class="material-symbols-outlined text-[13px]">magic_button</span> Auto Generate
                    </button>
                </div>
                <div class="relative">
                    <input type="text" id="grn_number_input" name="grn_number" value="{{ $nextGrnNumber ?? '' }}" required placeholder="e.g. GRN-20261021-001" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-mono uppercase focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 text-slate-900 font-bold transition-all">
                    <span class="absolute right-3.5 top-3 text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200">Auto</span>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5 text-[11px]">Component / Master Part EE *</label>
                <select name="part_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 text-slate-800 transition-all font-medium">
                    <option value="">-- Select Master Part --</option>
                    @foreach($parts as $p)
                        <option value="{{ $p->id }}">{{ $p->part_number }} — {{ $p->part_name }} ({{ $p->supplier_name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5 text-[11px]">Lot / Batch Number</label>
                    <input type="text" name="lot_number" placeholder="e.g. LOT-STM-01" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-mono uppercase focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 text-slate-800 transition-all">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5 text-[11px]">Lot Quantity (Pcs) *</label>
                    <input type="number" name="quantity" min="1" required placeholder="e.g. 1000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-mono focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 text-slate-800 transition-all">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1.5 text-[11px]">Material Arrival Time *</label>
                <input type="datetime-local" name="arrived_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 text-slate-800 transition-all">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="toggleGrnModal(false)" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold transition flex items-center gap-1.5 shadow-md shadow-indigo-600/20 cursor-pointer active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span>Save to Queue</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const defaultAutoGrn = @json($nextGrnNumber ?? '');

    function autoFillGrn() {
        const input = document.getElementById('grn_number_input');
        if (input && defaultAutoGrn) {
            input.value = defaultAutoGrn;
        }
    }

    function toggleGrnModal(show) {
        const modal = document.getElementById('registerGrnModal');
        if (modal) {
            modal.classList.toggle('hidden', !show);
            if (show) {
                autoFillGrn();
            }
        }
    }

    function executeQuickScan() {
        const input = document.getElementById('quickScannerInput');
        const code = (input.value || '').trim().toUpperCase();
        
        if (!code) {
            input.focus();
            return;
        }

        // Poka-Yoke Logic Check: Separate Wire Harness and Automotive mistake-proofing
        if (code.startsWith('WH-') || code.includes('HARNESS') || code.includes('WIRING') || code.startsWith('GRN-WH-')) {
            showPokaYokeModal(code, 'Wire Harness (Non-EE)');
            return;
        }

        if (code.startsWith('AUTO-') || code.includes('OTOMOTIF') || code.startsWith('GRN-AUTO-')) {
            showPokaYokeModal(code, 'Automotive Part (Non-EE)');
            return;
        }

        if (code.startsWith('MECH-') || code.includes('MEKANIKAL')) {
            showPokaYokeModal(code, 'Mechanical & Stamping (Non-EE)');
            return;
        }

        input.value = code;
        document.getElementById('quickScannerForm').submit();
    }

    function setAndScan(code) {
        const input = document.getElementById('quickScannerInput');
        input.value = code;
        executeQuickScan();
    }

    function showPokaYokeModal(code, category) {
        document.getElementById('pokaYokeCode').innerText = code;
        document.getElementById('pokaYokeCategory').innerText = category;
        const descElem = document.getElementById('pokaYokeDesc');
        if (descElem) {
            if (category.includes('Wire Harness')) {
                descElem.innerText = 'The Poka-Yoke mistake-proofing guard automatically blocked this material because EE Part inspectors are not authorized to test Wire Harness cables. Multi-pin continuity and terminal crimp pull-test must be transferred to the Wire Harness IQC team per SOP WH-SOP-004.';
            } else if (category.includes('Automotive')) {
                descElem.innerText = 'The Poka-Yoke mistake-proofing guard automatically blocked this material because EE Part inspectors are not authorized to test Automotive components. Stamping thickness, tensile strength, and coating tests must be transferred to the Automotive IQC team per SOP AUTO-SOP-012.';
            } else {
                descElem.innerText = 'The Poka-Yoke safety guard automatically blocked this material to prevent cross-section input errors per manufacturing SOP.';
            }
        }
        const reportBtn = document.getElementById('pokaYokeReportBtn');
        if (reportBtn) {
            reportBtn.href = "{{ route('poka-yoke.denied') }}?part_number=" + encodeURIComponent(code) + "&part_name=" + encodeURIComponent(category);
        }
        document.getElementById('pokaYokeModal').classList.remove('hidden');
    }

    function closePokaYokeModal() {
        document.getElementById('pokaYokeModal').classList.add('hidden');
        const input = document.getElementById('quickScannerInput');
        input.value = '';
        input.focus();
    }

    function filterQueueTable(query) {
        const term = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.queue-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const grn = (row.dataset.grn || '').toLowerCase();
            const part = (row.dataset.part || '').toLowerCase();
            const vendor = (row.dataset.vendor || '').toLowerCase();
            
            if (grn.includes(term) || part.includes(term) || vendor.includes(term)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const badge = document.getElementById('queueBadgeCount');
        if (badge) {
            badge.innerText = `${visibleCount} Active Queue`;
        }
    }

    function resetQueueFilter() {
        const searchInput = document.getElementById('queueSearchInput');
        if (searchInput) searchInput.value = '';
        filterQueueTable('');
    }

    function showGrnNotFoundModal(grnCode) {
        document.getElementById('grnNotFoundCode').innerText = grnCode;
        document.getElementById('grnNotFoundModal').classList.remove('hidden');
    }

    function closeGrnNotFoundModal() {
        document.getElementById('grnNotFoundModal').classList.add('hidden');
        const input = document.getElementById('quickScannerInput');
        input.value = '';
        input.focus();
    }

    @if(session('grn_not_found'))
        document.addEventListener('DOMContentLoaded', function() {
            showGrnNotFoundModal(@json(session('grn_not_found')));
        });
    @endif
</script>
@endsection
