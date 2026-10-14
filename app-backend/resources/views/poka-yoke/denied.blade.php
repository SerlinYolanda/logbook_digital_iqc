@extends('layouts.app')

@section('title', 'Access Denied - Poka-Yoke System Guard')

@section('content')
<div class="max-w-2xl mx-auto my-8 space-y-6">

    <div class="bg-white/95 border-2 border-rose-500/40 rounded-3xl p-8 sm:p-10 shadow-2xl relative overflow-hidden backdrop-blur-xl">
        <!-- Ambient Alert Glow -->
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-rose-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center text-center">
            <!-- Warning Shield Icon -->
            <div class="w-20 h-20 rounded-3xl bg-rose-50 border-2 border-rose-200 flex items-center justify-center text-rose-500 shadow-xl shadow-rose-500/20 mb-6 animate-pulse">
                <span class="material-symbols-outlined icon-2xl">shield_lock</span>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-600 text-xs font-mono font-bold tracking-wide uppercase mb-3">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                Poka-Yoke Error Guard Active
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                Access Restricted by System
            </h1>

            <p class="text-xs sm:text-sm text-slate-600 mt-3 leading-relaxed max-w-md">
                {{ $rule_violated ?? 'Poka-Yoke Principle (Automatic Error Prevention) blocks actions that violate IQC standard quality operational procedures.' }}
            </p>

            <!-- Technical Detail Box -->
            <div class="w-full mt-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left text-xs font-mono space-y-1.5 text-slate-400">
                <div class="flex justify-between">
                    <span>Rule Triggered:</span>
                    <span class="text-rose-600 font-bold">POKA-YOKE-RULE-04 (Dual Control)</span>
                </div>
                <div class="flex justify-between">
                    <span>Your Current Role:</span>
                    <span class="text-indigo-600 font-bold uppercase">{{ auth()->user()->role ?? $user_role ?? 'INSPECTOR' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Required Role:</span>
                    <span class="text-amber-700 font-bold">SUPERVISOR / QUALITY HEAD</span>
                </div>
                <div class="flex justify-between">
                    <span>Target Resource:</span>
                    <span class="text-slate-900">{{ $target_resource ?? 'Disposition Panel & Master Data' }}</span>
                </div>
            </div>

            <!-- Resolution Guidance -->
            <div class="w-full mt-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-left text-xs text-amber-700 flex items-start gap-3">
                <span class="material-symbols-outlined text-amber-700 icon-lg shrink-0 mt-0.5">lightbulb</span>
                <div class="leading-relaxed">
                    <strong>Resolution:</strong> If you are a Quality Supervisor, please log out from this account and sign in again using the authorized account NIK: <code class="text-slate-900 font-mono font-bold">SPV-001</code>.
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('dashboard') }}" class="flex-1 sm:flex-none px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-slate-900 font-semibold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined icon-md">dashboard</span>
                    <span>Back to Dashboard</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 font-medium text-xs border border-slate-300 transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined icon-md">swap_horiz</span>
                        <span>Switch Account</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
