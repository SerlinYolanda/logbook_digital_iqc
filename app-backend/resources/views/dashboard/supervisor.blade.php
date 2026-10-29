@extends('layouts.app')

@section('title', 'IQC Supervisor Dashboard - Logbook Digital IQC')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Welcome Header for Supervisor -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <span class="material-symbols-outlined icon-xl">admin_panel_settings</span>
                </span>
                <h1 class="text-xl font-bold text-slate-900">IQC Supervisor Control Center</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Welcome back, <span class="font-semibold text-slate-700">{{ Auth::user()->name ?? 'Supervisor' }}</span>. Monitor MRB dispositions, supplier compliance, and team inspection throughput.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                Supervisor Oversight Active
            </span>
        </div>
    </div>

    <!-- Section 1: Executive KPI Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Pending MRB Dispositions -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pending MRB Dispositions</span>
                    <h3 class="text-3xl font-extrabold text-amber-600 mt-1">1 <span class="text-xs font-normal text-slate-500">Lot Review</span></h3>
                </div>
                <div class="p-3 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100">
                    <span class="material-symbols-outlined text-2xl">pending_actions</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Requires approval decision</span>
                <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Action Needed</span>
            </div>
        </div>

        <!-- Card 2: Supplier Quality Index -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Supplier Pass Rate (MTD)</span>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">96.4%</h3>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                    <span class="material-symbols-outlined text-2xl">verified</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Active vendors compliant</span>
                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Stable</span>
            </div>
        </div>

        <!-- Card 3: Today's Total Inspections -->
        <div class="bg-white/90 border border-slate-200 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Today's Inspection Throughput</span>
                    <h3 class="text-3xl font-extrabold text-indigo-600 mt-1">6 <span class="text-xs font-normal text-slate-500">Lots Tested</span></h3>
                </div>
                <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <span class="material-symbols-outlined text-2xl">bar_chart</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Shift output status</span>
                <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">On Track</span>
            </div>
        </div>
    </div>

    <!-- Section 2: MRB / Defect Review Queue Table -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl space-y-4">
        <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600 icon-md">gavel</span>
                    Material Review Board (MRB) Escalate Queue
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Review out-of-spec lots submitted by inspectors and execute formal disposition decisions.</p>
            </div>
            <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold w-fit">
                1 Pending Escalation
            </span>
        </div>

        <div class="overflow-x-auto px-6 pb-6">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200 font-semibold">
                    <tr>
                        <th class="py-3 px-4">GRN / PO Number</th>
                        <th class="py-3 px-4">Part Details</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Defect Summary</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Supervisor Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">GRN-20261004-003</td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800">Part #4 - Main Casing</div>
                            <div class="text-[10px] text-slate-400">Lot Qty: 5,000 pcs</div>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-700">PT Tanjung Sauh Power</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-1 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[10px] inline-block">
                                Out of Spec (Dimension Deviation)
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                WAITING REVIEW
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <button type="button" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] shadow transition">
                                Approve Rework
                            </button>
                            <button type="button" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-[11px] shadow transition">
                                Return to Vendor
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection