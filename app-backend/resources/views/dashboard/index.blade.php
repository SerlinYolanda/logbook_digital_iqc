@extends('layouts.app')
@section('title', 'Dashboard - Logbook Digital IQC')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- 3 Dashboard Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider w-40 leading-snug">Incoming Material Queue Today</h3>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-100">
                    <span class="material-symbols-outlined text-indigo-600">inbox</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-slate-900">{{ $arrivedTodayCount }}</span>
                    <span class="text-sm font-semibold text-slate-600">GRN</span>
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">schedule</span> Arrived Today</span>
                    <span class="text-[10px] text-slate-500">• {{ $queueCount }} Waiting at IQC Dock</span>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="absolute top-0 left-0 w-full h-1 bg-amber-400"></div>
            <div class="flex items-start justify-between mt-1">
                <h3 class="text-[11px] font-bold text-amber-700 uppercase tracking-wider flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Awaiting Supervisor Disposition</h3>
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center border border-amber-100">
                    <span class="material-symbols-outlined text-amber-600">assignment_late</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-amber-500">{{ $pendingDispositionCount }}</span>
                    <span class="text-sm font-semibold text-amber-700">Lot</span>
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100 flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">error</span> Needs Review</span>
                    <span class="text-[10px] text-amber-700 font-medium">• {{ $pendingCount }} Awaiting Decision</span>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl p-5 border border-emerald-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-start justify-between mt-1">
                <h3 class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Inspected Today</h3>
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center border border-emerald-200">
                    <span class="material-symbols-outlined text-emerald-600">done_all</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-emerald-500">{{ $inspectedTodayCount }}</span>
                    <span class="text-sm font-semibold text-emerald-700">Lot</span>
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">check</span> Pass {{ $passRate }}%</span>
                    <span class="text-[10px] text-emerald-700 font-medium">• {{ $approvedCount }} Total Passed Lots</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Poka-Yoke Scanner Block -->
    <div class="bg-slate-900 rounded-2xl overflow-hidden shadow-xl border border-slate-800 relative">
        <div class="p-6 md:p-8 relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center border border-indigo-500/30">
                        <span class="material-symbols-outlined text-indigo-400">qr_code_scanner</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">Quick Scanner <span class="bg-indigo-900 text-indigo-300 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-indigo-700 tracking-wide">Automated Verification</span></h2>
                        <p class="text-xs text-slate-400 mt-1">Automatic inter-divisional material input error prevention (Poka-Yoke System).</p>
                    </div>
                </div>
                <div class="px-3 py-1.5 rounded-full bg-emerald-900/40 border border-emerald-800/50 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-emerald-400 tracking-wide uppercase">Barcode Sensor Ready</span>
                </div>
            </div>

            <form action="{{ route('incoming-materials.scan') }}" method="GET" id="quickScannerForm" class="bg-white rounded-xl p-6 mt-6 shadow-lg border border-slate-100 relative">
                <div class="flex items-center justify-between mb-3">
                    <label for="quickScannerInput" class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Scan / Search Incoming Material GRN Number</label>
                    <span class="text-[10px] text-slate-400 font-medium">Press Enter or click the button to verify</span>
                </div>
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-300">qr_code_2</span>
                        <input type="text" id="quickScannerInput" name="grn" onkeydown="if(event.key==='Enter'){event.preventDefault(); executeQuickScan();}" class="w-full pl-12 pr-4 py-3.5 !bg-slate-50 !border-slate-200 rounded-xl font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500 text-slate-800 placeholder-slate-400 uppercase" placeholder="Scan or type GRN number..." autocomplete="off">
                    </div>
                    <button type="button" onclick="executeQuickScan()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3.5 rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined icon-sm">search</span> Search & Start Inspection
                    </button>
                </div>
            </form>
        </div>
        <!-- Background pattern -->
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
    </div>

    <!-- Table Section: Inspection Queue -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-3">
                    Material Inspection Queue
                    <span id="queueBadgeCount" class="bg-slate-100 text-slate-600 border border-slate-200 text-[10px] px-2.5 py-0.5 rounded-full font-bold tracking-wide">{{ $queueCount }} Active Queue</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1">Incoming material testing priority based on Good Receipt Note (GRN) arrival order.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 icon-sm">search</span>
                    <input type="text" id="queueSearchInput" oninput="filterQueueTable(this.value)" class="pl-9 pr-4 py-2 !border-slate-200 rounded-lg text-xs w-64 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Search part / vendor / GRN...">
                </div>
                <button type="button" onclick="resetQueueFilter()" title="Refresh Queue" class="p-2 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-50 transition cursor-pointer">
                    <span class="material-symbols-outlined icon-sm">sync</span>
                </button>
                <button type="button" id="registerGrnBtn" onclick="toggleGrnModal(true)" class="px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                    <span class="material-symbols-outlined icon-sm">add_circle</span> Register GRN
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="queueTable">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Arrival Time</th>
                        <th class="py-3 px-5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">GRN Number</th>
                        <th class="py-3 px-5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Part Name</th>
                        <th class="py-3 px-5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Vendor</th>
                        <th class="py-3 px-5 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Lot Qty</th>
                        <th class="py-3 px-5 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm" id="queueTableBody">
                    @forelse($queue as $item)
                    @php
                        $startUrl = route('inspections.create', [
                            'incoming_id' => $item->id,
                            'grn' => $item->grn_number,
                            'part_id' => $item->part_id,
                            'qty' => $item->quantity,
                            'lot' => $item->lot_number,
                            'locked' => 1,
                        ]);
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition queue-row" data-grn="{{ $item->grn_number }}" data-part="{{ $item->part->part_name ?? '' }}" data-vendor="{{ $item->part->supplier_name ?? '' }}" data-url="{{ $startUrl }}">
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-1.5 text-slate-600 font-mono text-xs">
                                <span class="material-symbols-outlined text-[14px]">schedule</span> {{ $item->arrived_at->format('d M, H:i') }} WIB
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold font-mono rounded border border-indigo-100">{{ $item->grn_number }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <div class="font-bold text-slate-900 text-sm">{{ $item->part->part_name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $item->part->part_number ?? '-' }}@if($item->lot_number) • {{ $item->lot_number }}@endif</div>
                        </td>
                        <td class="py-4 px-5 text-slate-600 text-xs font-medium">
                            <span class="inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> {{ $item->part->supplier_name ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-5 text-right font-mono font-bold text-sm text-slate-700">
                            {{ number_format($item->quantity) }} <span class="text-xs text-slate-400 font-sans font-medium">Pcs</span>
                        </td>
                        <td class="py-4 px-5 text-center">
                            <div class="flex items-center gap-1.5 justify-center">
                                @if(auth()->user()->role === 'inspector')
                                <a href="{{ $startUrl }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow transition flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined icon-sm">play_arrow</span> Start Inspection
                                </a>
                                @else
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded">Waiting Inspector</span>
                                @endif
                                <form action="{{ route('incoming-materials.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove {{ $item->grn_number }} from the queue?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Remove from queue" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 border border-slate-200 transition inline-flex cursor-pointer">
                                        <span class="material-symbols-outlined icon-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-slate-400 text-xs">
                            <div class="max-w-md mx-auto flex flex-col items-center">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mb-3 border border-indigo-100">
                                    <span class="material-symbols-outlined" style="font-size:26px">inventory_2</span>
                                </div>
                                <div class="font-bold text-slate-700 text-sm mb-1">No incoming material waiting for inspection</div>
                                <p class="text-slate-400 text-xs mb-4">You have registered master parts. Click below to register an incoming delivery (GRN) to the IQC testing queue.</p>
                                <button type="button" onclick="toggleGrnModal(true)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold inline-flex items-center gap-2 shadow-lg shadow-indigo-600/20 transition cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">add_circle</span> Register Incoming GRN
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">
                <span class="material-symbols-outlined text-[14px]">shield</span> Standar Sampling IQC: MIL-STD-105E (AQL Level II Normal, Major 0.65 / Minor 1.5)
            </div>
            <div class="text-[10px] font-mono text-slate-400">
                Last Sync: {{ now()->format('H:i') }} WIB
            </div>
        </div>
    </div>

    <!-- Riwayat Logbook Terkini Section -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600">history_edu</span>
                    Recent Logbook Transactions
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Verified inspection results and supervisor disposition status.</p>
            </div>
            <a href="{{ route('inspections.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                <span>View All Logbook</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">PO / GRN</th>
                        <th class="py-3 px-4">Component / Part</th>
                        <th class="py-3 px-4">Vendor</th>
                        <th class="py-3 px-4 text-center">Lot / Sample</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentInspections as $ins)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-700">{{ $ins->po_number }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $ins->part->part_name ?? '-' }}</div>
                                <div class="font-mono text-[10px] text-slate-400">{{ $ins->part->part_number ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">{{ $ins->part->supplier_name ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <div>{{ number_format($ins->lot_quantity) }}</div>
                                <div class="text-[10px] text-slate-400">SMP: {{ $ins->sample_size }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($ins->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> PASS
                                    </span>
                                @elseif($ins->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> REJECT
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('inspections.show', $ins->id) }}" class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">visibility</span> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-slate-400">No inspection transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Poka-Yoke Access Denied Modal Pop-up -->
<div id="pokaYokeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-200 animate-in fade-in zoom-in-95 duration-200">
        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 border border-rose-200">
            <span class="material-symbols-outlined icon-xl">gpp_bad</span>
        </div>
        <h3 class="text-lg font-bold text-slate-900 text-center">Poka-Yoke: Material Rejected!</h3>
        <p class="text-xs text-rose-600 font-bold text-center mt-1 uppercase tracking-wide">Material Does Not Match EE Part Division</p>
        
        <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 my-4 text-xs text-rose-800 space-y-2 font-mono">
            <div class="flex justify-between">
                <span class="text-slate-500">Material Code:</span>
                <span id="pokaYokeCode" class="font-bold text-rose-700">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Division Category:</span>
                <span id="pokaYokeCategory" class="font-bold text-slate-700">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Prevention Rule:</span>
                <span class="font-bold text-rose-700">Strict Separation</span>
            </div>
        </div>

        <p class="text-xs text-slate-500 text-center leading-relaxed">
            The Poka-Yoke system has automatically blocked access. Please redirect this material to the appropriate division portal per manufacturing SOP.
        </p>

        <div class="mt-6 flex gap-3">
            <button type="button" onclick="closePokaYokeModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Close & Re-scan
            </button>
        </div>
    </div>
</div>

<!-- GRN Not Found Alert Modal -->
<div id="grnNotFoundModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-amber-200" style="animation: grnNotFoundBounce 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)">
        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 border border-amber-200">
            <span class="material-symbols-outlined" style="font-size:32px">search_off</span>
        </div>
        <h3 class="text-lg font-bold text-slate-900 text-center">GRN Not Found</h3>
        <p class="text-xs text-amber-600 font-bold text-center mt-1 uppercase tracking-wide">No Matching Incoming Material Record</p>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 my-4 text-xs text-amber-900 space-y-2">
            <div class="flex justify-between">
                <span class="text-slate-500">Scanned GRN:</span>
                <span id="grnNotFoundCode" class="font-bold font-mono text-amber-700">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Status:</span>
                <span class="font-bold text-amber-700">Not Registered in IQC System</span>
            </div>
        </div>

        <p class="text-xs text-slate-500 text-center leading-relaxed">
            This GRN number has not been registered as incoming material. Please register the GRN first through <strong>"Register GRN"</strong> before starting the inspection.
        </p>

        <div class="mt-5 flex gap-3">
            <button type="button" onclick="closeGrnNotFoundModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                Close & Re-scan
            </button>
            <button type="button" onclick="closeGrnNotFoundModal(); toggleGrnModal(true);" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/20">
                <span class="material-symbols-outlined icon-sm">add_circle</span> Register GRN Now
            </button>
        </div>
    </div>
</div>

<style>
@keyframes grnNotFoundBounce {
    0% { opacity: 0; transform: scale(0.85) translateY(20px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

<!-- Register Incoming GRN Modal -->
<div id="registerGrnModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <span class="material-symbols-outlined icon-md">inventory_2</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Register Incoming Material GRN</h3>
                    <p class="text-[11px] text-slate-500">Add received parts/lots to the IQC testing queue.</p>
                </div>
            </div>
            <button type="button" onclick="toggleGrnModal(false)" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined icon-sm">close</span>
            </button>
        </div>

        <form action="{{ route('incoming-materials.store') }}" method="POST" class="mt-4 space-y-4 text-xs">
            @csrf
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider">GRN Number *</label>
                    <button type="button" onclick="autoFillGrn()" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-0.5 bg-indigo-50 px-1.5 py-0.5 rounded transition">
                        <span class="material-symbols-outlined text-[12px]">magic_button</span> Auto Generate
                    </button>
                </div>
                <div class="relative">
                    <input type="text" id="grn_number_input" name="grn_number" value="{{ $nextGrnNumber ?? '' }}" required placeholder="e.g.: GRN-20261004-001" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono uppercase focus:border-indigo-500 focus:ring-indigo-500 text-slate-900 font-bold">
                    <span class="absolute right-3 top-2.5 text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200">Auto</span>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Part / Component *</label>
                <select name="part_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                    <option value="">-- Select Master Part --</option>
                    @foreach($parts as $p)
                        <option value="{{ $p->id }}">{{ $p->part_number }} - {{ $p->part_name }} ({{ $p->supplier_name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Lot Number</label>
                    <input type="text" name="lot_number" placeholder="e.g.: LOT-001" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono uppercase focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Quantity (Pcs) *</label>
                    <input type="number" name="quantity" min="1" required placeholder="1000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wider mb-1">Arrival Time *</label>
                <input type="datetime-local" name="arrived_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 text-slate-800">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="toggleGrnModal(false)" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition flex items-center gap-1.5 shadow-md shadow-indigo-600/20 cursor-pointer">
                    <span class="material-symbols-outlined icon-sm">save</span>
                    Add to Queue
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

        // Poka-Yoke Logic Check (client-side division guard)
        if (code.startsWith('AUTO-') || code.includes('OTOMOTIF')) {
            showPokaYokeModal(code, 'Automotive Division (Non-EE)');
            return;
        }

        if (code.startsWith('MECH-') || code.includes('MEKANIKAL')) {
            showPokaYokeModal(code, 'Mechanical & Stamping Division (Non-EE)');
            return;
        }

        // Submit to server-side scan endpoint for proper GRN validation
        // The server will check the DB: found → redirect to inspection form (pre-synced),
        // not found → redirect back with 'grn_not_found' flash to trigger the alert modal.
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

    // GRN Not Found modal controls
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

    // Auto-trigger GRN Not Found modal from server-side session flash
    @if(session('grn_not_found'))
        document.addEventListener('DOMContentLoaded', function() {
            showGrnNotFoundModal(@json(session('grn_not_found')));
        });
    @endif
</script>
@endsection


