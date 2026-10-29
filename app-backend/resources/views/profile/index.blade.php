@extends('layouts.app')
@section('title', 'User Profile - Logbook Digital IQC')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Main Profile Card -->
    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-200">
        <!-- Cover Banner -->
        <div class="h-32 bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 w-full relative">
            <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 16px 16px;"></div>
            <div class="absolute bottom-3 right-6 text-[10px] font-mono font-bold text-slate-300 bg-black/50 px-3 py-1 rounded-full border border-white/15 uppercase tracking-widest backdrop-blur-xs">
                EE Part • Quality Control
            </div>
        </div>
        
        <div class="px-6 md:px-8 pb-8 pt-4 relative">
            <!-- Profile Avatar & Identity Row (Separated from banner overlap) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <!-- Avatar Badge (Negative margin ONLY on the avatar itself) -->
                    <div class="-mt-16 w-24 h-24 rounded-3xl bg-white p-1.5 shadow-xl border-2 border-white ring-1 ring-slate-200/80 shrink-0">
                        <div class="w-full h-full bg-gradient-to-tr from-indigo-600 to-blue-500 rounded-2xl text-white flex items-center justify-center text-3xl font-black tracking-tight shadow-inner">
                            @php
                                $name = auth()->user()->name ?? 'User';
                                $words = explode(' ', trim($name));
                                $initials = '';
                                if (count($words) >= 2) {
                                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                } else {
                                    $initials = strtoupper(substr($name, 0, 2));
                                }
                            @endphp
                            {{ $initials }}
                        </div>
                    </div>
                    
                    <!-- Name, Title, and ID (Cleanly in white background with zero clipping) -->
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                                {{ auth()->user()->name ?? 'User Account' }}
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide
                                {{ auth()->user()->role === 'admin' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 
                                   (auth()->user()->role === 'supervisor' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200') }}">
                                {{ ucfirst(auth()->user()->role ?? 'User') }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-mono font-medium mt-1 flex items-center gap-2">
                            <span>Employee NIK: <strong class="text-slate-800">{{ auth()->user()->nik ?? '-' }}</strong></span>
                            <span>•</span>
                            <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Active Session
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-2.5">
                    <form method="POST" action="{{ route('logout') }}" class="inline m-0">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl border border-rose-200 transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                            <span class="material-symbols-outlined text-[16px] text-rose-600">logout</span>
                            <span>Log Out</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Profile Info Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 p-5 bg-slate-50 rounded-2xl border border-slate-200/80">
                <!-- Position / Role -->
                <div class="p-3.5 bg-white rounded-xl border border-slate-200/60 shadow-xs">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">POSITION / ROLE</label>
                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-600 text-[18px]">badge</span> 
                        <span>{{ ucfirst(auth()->user()->role ?? 'Inspector') }}</span>
                    </div>
                </div>
                
                <!-- Department -->
                <div class="p-3.5 bg-white rounded-xl border border-slate-200/60 shadow-xs">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">DEPARTMENT</label>
                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">precision_manufacturing</span> 
                        <span>EE Part</span>
                    </div>
                </div>

                <!-- Registered Email -->
                <div class="p-3.5 bg-white rounded-xl border border-slate-200/60 shadow-xs">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">REGISTERED EMAIL</label>
                    <div class="text-xs font-bold text-slate-800 flex items-center gap-2 font-mono truncate" title="{{ auth()->user()->email ?? 'user@iqc.local' }}">
                        <span class="material-symbols-outlined text-slate-400 text-[18px] shrink-0">mail</span> 
                        <span class="truncate">{{ auth()->user()->email ?? 'user@iqc.local' }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Container -->
            <div class="mt-6 border border-slate-200 rounded-2xl p-5 bg-white shadow-xs">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600 text-[18px]">history</span>
                    <span>Recent System & Audit Activity</span>
                </h3>
                <div class="space-y-3.5">
                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[16px]">login</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 leading-tight">System Session Active</p>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5">Logged in as {{ ucfirst(auth()->user()->role ?? 'Inspector') }} ({{ now()->format('d M Y, H:i') }} WIB)</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-indigo-600 text-[16px]">fact_check</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 leading-tight">IQC Quality Authorization Status</p>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5">Authorized for EE Part dimensional testing, poka-yoke scan, and logbook transactions.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
