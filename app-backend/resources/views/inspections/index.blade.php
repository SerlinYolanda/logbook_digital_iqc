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
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 text-xs border border-slate-300 transition flex items-center gap-1.5">
                <span class="material-symbols-outlined icon-sm">print</span>
                <span>Print Archive</span>
            </button>
            <a href="{{ route('inspections.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-slate-900 font-semibold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5">
                <span class="material-symbols-outlined icon-sm">add</span>
                <span>New Inspection</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white/90 border border-slate-200 p-4 rounded-2xl shadow-md">
        <form action="{{ route('inspections.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <!-- Search Keyword -->
            <div>
                <label class="block text-slate-400 text-[11px] mb-1 font-medium">Search PO / Lot / Part:</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-500 icon-sm">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Type keyword..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-slate-400 text-[11px] mb-1 font-medium">Validation Status:</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                    <option value="">-- All Status --</option>
                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved (Passed)</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <!-- Part Filter -->
            <div>
                <label class="block text-slate-400 text-[11px] mb-1 font-medium">Filter Component Part:</label>
                <select name="part_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                    <option value="">-- All Components --</option>
                    @foreach($parts as $part)
                        <option value="{{ $part->id }}" {{ request('part_id') == $part->id ? 'selected' : '' }}>
                            {{ $part->part_number }} - {{ $part->part_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-slate-900 font-semibold transition flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined icon-sm">filter_alt</span>
                    Filter
                </button>
                <a href="{{ route('inspections.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-900 transition" title="Reset">
                    <span class="material-symbols-outlined icon-sm">restart_alt</span>
                </a>
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
