@extends('layouts.app')

@section('title', 'Inspection History & Archive - Logbook Digital IQC')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <span class="material-symbols-outlined icon-xl">history</span>
                </span>
                <h1 class="text-xl font-bold text-slate-900">Inspection History & Archive — IQC Logbook</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Database of all dimensional test records, lot sampling, and quality authorization status.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <!-- Export CSV Button -->
            <a href="{{ route('inspections.export', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-300 transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Download filtered history as CSV/Excel">
                <span class="material-symbols-outlined icon-sm text-emerald-600">download</span>
                <span>Export CSV</span>
            </a>

            <!-- Full Archive Printable Audit Report (All Records Matching Current Filter) -->
            <a href="{{ route('inspections.print', request()->query()) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 text-xs font-bold border border-slate-300 transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Open full printable audit report without pagination limit">
                <span class="material-symbols-outlined icon-sm text-slate-600">print</span>
                <span>Print Archive (All {{ $inspections->total() }})</span>
            </a>

            <!-- New Inspection Button -->
            <a href="{{ route('inspections.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined icon-sm">add</span>
                <span>New Inspection</span>
            </a>
        </div>
    </div>

    <!-- Quick Status Tabs (Clickable Filter Pills) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        @php
            $currentStatus = request('status', 'all');
            $statusCounts = $statusCounts ?? [
                'all' => \App\Models\Inspection::count(),
                'pending_approval' => \App\Models\Inspection::where('status', 'pending_approval')->count(),
                'approved' => \App\Models\Inspection::where('status', 'approved')->count(),
                'rejected' => \App\Models\Inspection::where('status', 'rejected')->count(),
            ];
        @endphp

        <a href="{{ route('inspections.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
            class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-2 shrink-0 cursor-pointer {{ $currentStatus === 'all' || empty(request('status')) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>All Records</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $currentStatus === 'all' || empty(request('status')) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                {{ $statusCounts['all'] }}
            </span>
        </a>

        <a href="{{ route('inspections.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending_approval'])) }}"
            class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-2 shrink-0 cursor-pointer {{ $currentStatus === 'pending_approval' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            <span>Pending Review</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $currentStatus === 'pending_approval' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                {{ $statusCounts['pending_approval'] }}
            </span>
        </a>

        <a href="{{ route('inspections.index', array_merge(request()->except(['status', 'page']), ['status' => 'approved'])) }}"
            class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-2 shrink-0 cursor-pointer {{ $currentStatus === 'approved' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Approved (In-Spec)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $currentStatus === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                {{ $statusCounts['approved'] }}
            </span>
        </a>

        <a href="{{ route('inspections.index', array_merge(request()->except(['status', 'page']), ['status' => 'rejected'])) }}"
            class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-2 shrink-0 cursor-pointer {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span class="w-2 h-2 rounded-full bg-rose-400"></span>
            <span>Rejected (Out of Spec)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono {{ $currentStatus === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                {{ $statusCounts['rejected'] }}
            </span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white/95 border border-slate-200 p-5 rounded-2xl shadow-md space-y-4">
        <form action="{{ route('inspections.index') }}" method="GET" id="inspectionFilterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                <!-- Search Keyword -->
                <div class="lg:col-span-2">
                    <label class="block text-slate-500 text-[11px] mb-1 font-semibold">Search PO / Lot / Part / Vendor:</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 icon-sm pointer-events-none">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Type keyword and press Enter..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-slate-500 text-[11px] mb-1 font-semibold">Validation Status:</label>
                    <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition cursor-pointer">
                        <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved (Passed)</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected (Defect)</option>
                    </select>
                </div>

                <!-- Part Filter -->
                <div>
                    <label class="block text-slate-500 text-[11px] mb-1 font-semibold">Component Part:</label>
                    <select name="part_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition cursor-pointer">
                        <option value="all">All Components</option>
                        @foreach($parts as $part)
                            <option value="{{ $part->id }}" {{ request('part_id') == $part->id ? 'selected' : '' }}>
                                {{ $part->part_number }} - {{ $part->part_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons: Filter & Reset -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined icon-sm">filter_alt</span>
                        <span>Apply Filter</span>
                    </button>
                    @if(request('search') || (request('status') && request('status') !== 'all') || (request('part_id') && request('part_id') !== 'all') || (request('disposition') && request('disposition') !== 'all') || request('date'))
                        <a href="{{ route('inspections.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 border border-slate-200 transition flex items-center justify-center cursor-pointer" title="Reset Filters">
                            <span class="material-symbols-outlined icon-sm">restart_alt</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Optional Additional Filters Row (Disposition & Date) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs pt-3 border-t border-slate-100">
                <div>
                    <label class="block text-slate-500 text-[11px] mb-1 font-semibold">Final Disposition:</label>
                    <select name="disposition" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition cursor-pointer">
                        <option value="all" {{ request('disposition', 'all') == 'all' ? 'selected' : '' }}>All Dispositions</option>
                        <option value="accept" {{ request('disposition') == 'accept' ? 'selected' : '' }}>ACCEPT (Passed)</option>
                        <option value="return_to_vendor" {{ request('disposition') == 'return_to_vendor' ? 'selected' : '' }}>RTV (Return to Vendor)</option>
                        <option value="scrap" {{ request('disposition') == 'scrap' ? 'selected' : '' }}>SCRAP (Disposal)</option>
                        <option value="mrb" {{ request('disposition') == 'mrb' ? 'selected' : '' }}>MRB (Conditional Review)</option>
                        <option value="rework" {{ request('disposition') == 'rework' ? 'selected' : '' }}>REWORK (100% Sort)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-500 text-[11px] mb-1 font-semibold">Specific Inspection Date:</label>
                    <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition cursor-pointer">
                </div>

                <div class="sm:col-span-2 flex items-end justify-between gap-3 text-[11px] text-slate-400">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @if(request('search') || (request('status') && request('status') !== 'all') || (request('part_id') && request('part_id') !== 'all') || (request('disposition') && request('disposition') !== 'all') || request('date'))
                            <span class="font-bold text-slate-600">Active Filters:</span>
                            @if(request('search'))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    "{{ request('search') }}"
                                </span>
                            @endif
                            @if(request('status') && request('status') !== 'all')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Status: {{ strtoupper(request('status')) }}
                                </span>
                            @endif
                            @if(request('disposition') && request('disposition') !== 'all')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Disp: {{ strtoupper(request('disposition')) }}
                                </span>
                            @endif
                            @if(request('date'))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Date: {{ request('date') }}
                                </span>
                            @endif
                        @else
                            <span>Showing all inspection records ordered by latest date.</span>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-white/90 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">PO & Lot Number</th>
                        <th class="py-3 px-4">Component Part</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4 text-center">Sampling</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Disposition</th>
                        <th class="py-3 px-4 text-center">Authorization</th>
                        <th class="py-3 px-4 text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($inspections as $ins)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono text-slate-400">
                                {{ $ins->inspection_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <div class="font-bold text-slate-900">{{ $ins->po_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $ins->lot_number }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-900">{{ $ins->part->part_name ?? '-' }}</div>
                                <div class="text-[11px] text-indigo-600 font-mono">{{ $ins->part->part_number ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $ins->part->supplier_name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <span class="text-slate-900 font-semibold">{{ $ins->sample_size }}</span> / {{ number_format($ins->lot_quantity) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($ins->status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                        APPROVED
                                    </span>
                                @elseif($ins->status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                        REJECTED
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($ins->disposition === 'accept')
                                    <span class="text-xs font-bold text-emerald-600">ACCEPT</span>
                                @elseif($ins->disposition === 'return_to_vendor')
                                    <span class="text-xs font-bold text-rose-600">RTV</span>
                                @elseif($ins->disposition === 'scrap')
                                    <span class="text-xs font-bold text-purple-700">SCRAP</span>
                                @elseif($ins->disposition === 'mrb')
                                    <span class="text-xs font-bold text-amber-700">MRB</span>
                                @elseif($ins->disposition === 'rework')
                                    <span class="text-xs font-bold text-blue-600">REWORK</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center text-[11px] text-slate-500">
                                <div class="font-medium">Ins: <span class="text-slate-800 font-semibold">{{ $ins->inspector->name ?? 'Inspector' }}</span></div>
                                @if($ins->supervisor)
                                    <div class="text-[10px] font-semibold text-amber-700 mt-0.5">Spv: {{ $ins->supervisor->name }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('inspections.show', $ins->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 border border-slate-300 inline-flex items-center gap-1 font-medium transition">
                                    <span class="material-symbols-outlined icon-sm">visibility</span>
                                    <span>View</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-500 text-xs">
                                No inspection history records match the filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inspections->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/90">
                {{ $inspections->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
