@extends('layouts.app')
@section('title', 'Output & Inspection Reports - Logbook Digital IQC')
@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 icon-lg">bar_chart</span>
                Quality Inspection Output & Formal Reports
            </h1>
            <p class="text-xs text-slate-500 mt-1">Official quality verification documents and ISO 9001 compliance logs for EE Part.</p>
        </div>
        
        <div class="flex items-center gap-2.5">
            <a href="{{ route('reports.export', request()->query()) }}" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm shadow-emerald-600/20">
                <span class="material-symbols-outlined text-[16px]">file_download</span>
                <span>Export Excel (CSV)</span>
            </a>
            <button onclick="window.print()" class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm shadow-indigo-600/20 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">print</span>
                <span>Print Official Report</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Period:</label>
                <select name="period" onchange="this.form.submit()" class="w-full text-xs font-semibold !py-2 !px-3 border-slate-300 rounded-lg">
                    <option value="" {{ request('period') == '' ? 'selected' : '' }}>All Periods</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Status:</label>
                <select name="status" onchange="this.form.submit()" class="w-full text-xs font-semibold !py-2 !px-3 border-slate-300 rounded-lg">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Pass / Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Reject / Out of Spec</option>
                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending SPV Review</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Disposition:</label>
                <select name="disposition" onchange="this.form.submit()" class="w-full text-xs font-semibold !py-2 !px-3 border-slate-300 rounded-lg">
                    <option value="all" {{ request('disposition') == 'all' ? 'selected' : '' }}>All Dispositions</option>
                    <option value="accept" {{ request('disposition') == 'accept' ? 'selected' : '' }}>ACCEPT (Passed)</option>
                    <option value="return_to_vendor" {{ request('disposition') == 'return_to_vendor' ? 'selected' : '' }}>RTV (Return to Vendor)</option>
                    <option value="scrap" {{ request('disposition') == 'scrap' ? 'selected' : '' }}>SCRAP (Internal Disposal)</option>
                    <option value="mrb" {{ request('disposition') == 'mrb' ? 'selected' : '' }}>MRB (Conditional)</option>
                    <option value="rework" {{ request('disposition') == 'rework' ? 'selected' : '' }}>REWORK (100% Sort)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Component Part:</label>
                <select name="part_id" onchange="this.form.submit()" class="w-full text-xs font-semibold !py-2 !px-3 border-slate-300 rounded-lg">
                    <option value="all" {{ request('part_id') == 'all' ? 'selected' : '' }}>All Components</option>
                    @foreach($parts as $p)
                        <option value="{{ $p->id }}" {{ request('part_id') == $p->id ? 'selected' : '' }}>{{ $p->part_number }} - {{ $p->part_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition flex items-center justify-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span> Filter
                </button>
                @if(request('period') || request('status') || request('disposition') || request('part_id'))
                    <a href="{{ route('reports.index') }}" class="py-2 px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Reset Filter">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Formal Inspection Summary Sheet Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 print:p-0 print:border-none print:shadow-none" id="printable-report">
        <!-- Sheet Header -->
        <div class="border-b-2 border-slate-800 pb-5 mb-6">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined icon-xl">memory</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 uppercase tracking-wide">IQC QUALITY INSPECTION SUMMARY REPORT</h2>
                        <p class="text-xs font-bold text-indigo-700 tracking-wider uppercase mt-0.5">EE PART • LOGBOOK DIGITAL IQC PORTAL</p>
                    </div>
                </div>
                <div class="text-right text-xs text-slate-500 font-mono">
                    <p><span class="font-bold text-slate-700">Date Generated:</span> {{ now()->format('d M Y, H:i') }} WIB</p>
                    <p><span class="font-bold text-slate-700">Doc Control:</span> IQC-REP-{{ now()->format('Ym') }}-{{ str_pad(count($inspections), 3, '0', STR_PAD_LEFT) }}</p>
                    <p><span class="font-bold text-slate-700">Standard:</span> ISO 9001:2015 & MIL-STD-105E</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-5 pt-4 border-t border-slate-200 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Active User</span>
                    <span class="font-bold text-slate-800">{{ auth()->user()->name ?? 'User' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Department & Line</span>
                    <span class="font-bold text-slate-800">EE Part Quality Control</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Total Lot Transactions</span>
                    <span class="font-bold text-indigo-600">{{ count($inspections) }} Lots Inspected</span>
                </div>
            </div>
        </div>

        <!-- Table Summary -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3">No</th>
                        <th class="py-3 px-3">PO & Lot Number</th>
                        <th class="py-3 px-3">Part Code & Name</th>
                        <th class="py-3 px-3">Vendor / Supplier</th>
                        <th class="py-3 px-3 text-center">Sample / Lot</th>
                        <th class="py-3 px-3 text-center">Date</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Disposition</th>
                        <th class="py-3 px-3">Authorization</th>
                        <th class="py-3 px-3">Evaluation Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($inspections as $index => $ins)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3 font-mono text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-3 px-3 font-mono">
                                <div class="font-bold text-indigo-700">{{ $ins->po_number }}</div>
                                <div class="text-[10px] text-slate-400">{{ $ins->lot_number }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900">{{ $ins->part->part_name ?? '-' }}</div>
                                <div class="font-mono text-[10px] text-indigo-600">{{ $ins->part->part_number ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-3 font-medium">{{ $ins->part->supplier_name ?? '-' }}</td>
                            <td class="py-3 px-3 text-center font-mono">
                                <div><strong class="text-slate-900">{{ $ins->sample_size }}</strong> / {{ number_format($ins->lot_quantity) }}</div>
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-500">{{ \Carbon\Carbon::parse($ins->inspection_date)->format('d/m/Y') }}</td>
                            <td class="py-3 px-3 text-center">
                                @if($ins->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> PASS
                                    </span>
                                @elseif($ins->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> REJECT
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($ins->disposition === 'accept')
                                    <span class="text-[11px] font-bold text-emerald-600">ACCEPT</span>
                                @elseif($ins->disposition === 'return_to_vendor')
                                    <span class="text-[11px] font-bold text-rose-600">RTV</span>
                                @elseif($ins->disposition === 'scrap')
                                    <span class="text-[11px] font-bold text-purple-700">SCRAP</span>
                                @elseif($ins->disposition === 'mrb')
                                    <span class="text-[11px] font-bold text-amber-700">MRB</span>
                                @elseif($ins->disposition === 'rework')
                                    <span class="text-[11px] font-bold text-blue-600">REWORK</span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-[11px] text-slate-500">
                                <div>Ins: <span class="text-slate-800 font-semibold">{{ $ins->inspector->name ?? '-' }}</span></div>
                                @if($ins->supervisor)
                                    <div class="text-[10px] font-semibold text-amber-700">Spv: {{ $ins->supervisor->name }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-xs text-slate-600 max-w-xs truncate">
                                {{ $ins->disposition_notes ?? ($ins->status === 'approved' ? 'Meets drawing tolerance specifications' : 'Awaiting SPV disposition evaluation') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-slate-400">No inspection data matches the selected filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @php
            $spvUser = \App\Models\User::where('role', 'supervisor')->first();
        @endphp

        <!-- Approval & Digital Signature Stamp Area -->
        <div class="mt-10 pt-6 border-t border-slate-200 grid grid-cols-2 gap-8 text-center text-xs">
            <!-- Left: Inspector -->
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                <p class="font-bold text-slate-500 uppercase text-[10px] tracking-wider mb-2">Tested & Recorded By (IQC Inspector)</p>
                <div class="h-16 flex items-center justify-center">
                    <div class="border border-indigo-300 bg-indigo-50/60 rounded-lg px-4 py-1.5 text-indigo-700 text-[11px] font-bold inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">verified</span>
                        DIGITALLY VERIFIED ({{ auth()->user()->nik ?? 'IQC' }})
                    </div>
                </div>
                <p class="font-bold text-slate-800 mt-2">{{ auth()->user()->name ?? 'Inspector' }}</p>
                <p class="text-[10px] text-slate-400">IQC Quality Inspector</p>
            </div>

            <!-- Right: Quality Supervisor -->
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                <p class="font-bold text-slate-500 uppercase text-[10px] tracking-wider mb-2">Authorized By (Quality Supervisor)</p>
                <div class="h-16 flex items-center justify-center">
                    <div class="border border-amber-300 bg-amber-50/60 rounded-lg px-4 py-1.5 text-amber-800 text-[11px] font-bold inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">verified_user</span>
                        SPV QUALITY AUTHORIZED
                    </div>
                </div>
                <p class="font-bold text-slate-800 mt-2">{{ $spvUser->name ?? 'IQC Supervisor' }}</p>
                <p class="text-[10px] text-slate-400">Section Head / IQC Supervisor</p>
            </div>
        </div>
    </div>
</div>
@endsection
