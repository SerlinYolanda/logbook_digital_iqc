@extends('layouts.app')

@section('title', 'User Management - Logbook Digital IQC')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/90 border border-slate-200 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <span class="material-symbols-outlined icon-xl">manage_accounts</span>
                </span>
                <h1 class="text-xl font-bold text-slate-900">User Account Management</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Manage credential authorizations and role assignments (Inspector / Supervisor / Admin).
            </p>
        </div>
        @if(auth()->user()->role === 'admin')
        <button onclick="toggleAddUserModal(true)" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-slate-900 font-semibold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined icon-sm">person_add</span>
            <span>Add New User Account</span>
        </button>
        @endif
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white/90 border border-slate-200 p-4 rounded-2xl shadow-md">
        <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block text-slate-400 text-[11px] mb-1 font-medium">Search User / NIK / Email:</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 icon-sm">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Type name or NIK..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-slate-400 text-[11px] mb-1 font-medium">Filter by Role:</label>
                <select name="role" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 transition">
                    <option value="">-- All Roles --</option>
                    <option value="inspector" {{ request('role') === 'inspector' ? 'selected' : '' }}>Inspector</option>
                    <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>Supervisor (SPV)</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-slate-900 font-semibold transition flex items-center justify-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined icon-sm">filter_alt</span> Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-900 transition" title="Reset">
                    <span class="material-symbols-outlined icon-sm">restart_alt</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white/90 border border-slate-200 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">NIK</th>
                        <th class="py-3 px-4">User Name & Email</th>
                        <th class="py-3 px-4 text-center">Assigned Role</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($users as $usr)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                                {{ $usr->nik }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $usr->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $usr->email }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($usr->role === 'supervisor')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        SUPERVISOR (SPV)
                                    </span>
                                @elseif($usr->role === 'admin')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        ADMINISTRATOR
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        INSPECTOR
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 text-emerald-600 text-[11px] font-bold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                @if(auth()->user()->role === 'admin')
                                    <button onclick="openEditUserModal({{ json_encode($usr) }})" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition inline-flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined icon-sm">edit</span> Edit
                                    </button>
                                    @if($usr->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $usr->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition inline-flex items-center gap-1 cursor-pointer">
                                                <span class="material-symbols-outlined icon-sm">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-[10px] text-slate-400 italic">User Record</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No user accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $users->links() }}
        </div>
    </div>
</div>

@if(auth()->user()->role === 'admin')
<!-- Modal Add User (Admin Only) -->
<div id="addUserModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-300 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 icon-md">person_add</span>
                Add New User Account
            </h3>
            <button onclick="toggleAddUserModal(false)" class="text-slate-400 hover:text-slate-900">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Full Name *</label>
                <input type="text" name="name" required placeholder="e.g.: Budi Pratama (Inspektur)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-semibold text-slate-600">NIK (Unique ID) *</label>
                        <button type="button" onclick="autoFillNik()" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-0.5 bg-indigo-50 px-1.5 py-0.5 rounded transition">
                            <span class="material-symbols-outlined text-[12px]">magic_button</span> Auto Fill
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" id="add_nik" name="nik" required placeholder="e.g.: INS-002" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono text-slate-900 font-semibold focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <span class="absolute right-3 top-2.5 text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200">Auto</span>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Role *</label>
                    <select id="add_role" name="role" required onchange="autoFillNik()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        <option value="inspector" selected>Inspector</option>
                        <option value="supervisor">Supervisor (SPV)</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Email *</label>
                <input type="email" id="add_email" name="email" required placeholder="user@iqc.local" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Password *</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5">
            </div>
            <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="toggleAddUserModal(false)" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-slate-900 font-bold shadow-lg shadow-indigo-600/30">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User (Admin Only) -->
<div id="editUserModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white border border-slate-300 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 icon-md">edit</span>
                Edit User Account
            </h3>
            <button onclick="toggleEditUserModal(false)" class="text-slate-400 hover:text-slate-900">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editUserForm" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Full Name *</label>
                <input type="text" id="edit_name" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">NIK (Unique ID) *</label>
                    <input type="text" id="edit_nik" name="nik" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Role *</label>
                    <select id="edit_role" name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5">
                        <option value="inspector">Inspector</option>
                        <option value="supervisor">Supervisor (SPV)</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Email *</label>
                <input type="email" id="edit_email" name="email" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">New Password (leave blank to keep current)</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5">
            </div>
            <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="toggleEditUserModal(false)" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-slate-900 font-bold shadow-lg shadow-indigo-600/30">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    const roleNextNiks = @json($nextNiks ?? [
        'inspector' => 'INS-001',
        'supervisor' => 'SPV-001',
        'admin' => 'ADM-001'
    ]);

    function autoFillNik() {
        const role = document.getElementById('add_role').value;
        const nikInput = document.getElementById('add_nik');
        if (roleNextNiks[role]) {
            nikInput.value = roleNextNiks[role];
        }
    }

    function toggleAddUserModal(show) {
        document.getElementById('addUserModal').classList.toggle('hidden', !show);
        if (show) {
            autoFillNik();
        }
    }
    function toggleEditUserModal(show) {
        document.getElementById('editUserModal').classList.toggle('hidden', !show);
    }
    function openEditUserModal(user) {
        document.getElementById('editUserForm').action = '/admin/users/' + user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_nik').value = user.nik;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_email').value = user.email;
        toggleEditUserModal(true);
    }
</script>
@endif
@endsection
