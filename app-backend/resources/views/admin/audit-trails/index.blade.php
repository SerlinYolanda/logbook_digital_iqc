@extends('layouts.app')
@section('title', 'System Audit Trail - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">security</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">System Audit Trail</h1>
                <p class="text-xs text-slate-500 mt-0.5">Catatan riwayat aktivitas pengguna dan proteksi Poka-Yoke.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-right">
                <span class="text-[10px] font-bold uppercase text-rose-600 block">Poka-Yoke Blocked</span>
                <span class="text-xl font-black text-rose-700 font-mono">{{ $pokaYokeViolationsCount }} Incidents</span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-right">
                <span class="text-[10px] font-bold uppercase text-slate-400 block">Total Activities</span>
                <span class="text-xl font-black text-slate-800 font-mono">{{ $totalLogsCount }}</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.audit-trails.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Search Object / Details / IP:</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, GRN, lot, IP..." 
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, GR, lot, IP..." 
                    class="w-full text-xs rounded-xl border-slate-200 py-2">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Action Type:</label>
                <select name="aksi" class="w-full text-xs font-semibold rounded-xl border-slate-200 py-2">
                    <option value="">All Actions</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}" {{ request('aksi') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">User / Actor:</label>
                <select name="user_id" class="w-full text-xs font-semibold rounded-xl border-slate-200 py-2">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->nik }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Date From:</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full text-xs font-mono rounded-xl border-slate-200 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex-1 transition cursor-pointer">
                    Filter Logs
                </button>
                <a href="{{ route('admin.audit-trails.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Trails Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Actor / User</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4">System Action</th>
                        <th class="py-3 px-4">Data Object</th>
                        <th class="py-3 px-4">Previous Value</th>
                        <th class="py-3 px-4">New Value / Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-sans">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition {{ $log->aksi === 'POKA_YOKE_REJECTED' ? 'bg-rose-50/40' : '' }}">
                        <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">
                            {{ $log->waktu ? $log->waktu->format('Y-m-d H:i:s') : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($log->user)
                                <div class="font-bold text-slate-800">{{ $log->user->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $log->user->nik }} ({{ $log->user->role_label }})</div>
                            @else
                                <span class="font-mono text-slate-400">System Kernel</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                            {{ $log->alamat_ip ?? '127.0.0.1' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($log->aksi === 'POKA_YOKE_REJECTED')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300 animate-pulse flex items-center gap-1 w-max">
                                    <span class="material-symbols-outlined text-[13px]">block</span>
                                    <span>POKA-YOKE REJECTED</span>
                                </span>
                            @elseif(in_array($log->aksi, ['LOGIN', 'LOGOUT']))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $log->aksi }}
                                </span>
                            @elseif($log->aksi === 'MRB_DISPOSITION')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    MRB DISPOSITION
                                </span>
                            @elseif($log->aksi === 'NEST_INITIATED')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                    NEST INITIATED
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $log->aksi }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800 max-w-[200px] truncate" title="{{ $log->objek_data }}">
                            {{ $log->objek_data }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 font-mono text-[11px] max-w-[150px] truncate" title="{{ $log->nilai_sebelum }}">
                            {{ $log->nilai_sebelum ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-mono text-[11px] max-w-[240px] truncate" title="{{ $log->nilai_sesudah }}">
                            {{ $log->nilai_sesudah ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No audit trail records matched the filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-100">{{ $logs->links() }}</div>
        @endif
    </div>

</div>
@endsection