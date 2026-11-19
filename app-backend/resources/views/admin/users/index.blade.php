@extends('layouts.app')
@section('title', 'Manage User Accounts - IQC Digital Logbook')
@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Banner -->
    <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                <span class="material-symbols-outlined text-[26px]">manage_accounts</span>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Manage User Accounts & Access Rights</h1>
                <p class="text-xs text-slate-500 mt-0.5">User credentials management, 6 system sub-roles assignment, work shifts, and activation status.</p>
            </div>
        </div>

        <button type="button" onclick="document.getElementById('addUserModal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">person_add</span>
            <span>+ Create New Account</span>
        </button>
    </div>

    <!-- Role Summary Statistics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 text-center">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase text-slate-400">Total Users</span>
            <div class="text-xl font-black text-slate-800 mt-1">{{ $rolesCount['total'] }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/30">
            <span class="text-[10px] font-bold uppercase text-emerald-700">Active</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $rolesCount['active'] }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-indigo-200 bg-indigo-50/30">
            <span class="text-[10px] font-bold uppercase text-indigo-700">Admin</span>
            <div class="text-xl font-black text-indigo-600 mt-1">{{ $rolesCount['admin'] }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-blue-200 bg-blue-50/30">
            <span class="text-[10px] font-bold uppercase text-blue-700">Logistics</span>
            <div class="text-xl font-black text-blue-600 mt-1">{{ $rolesCount['master_data'] }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-amber-200 bg-amber-50/30">
            <span class="text-[10px] font-bold uppercase text-amber-700">Supervisor</span>
            <div class="text-xl font-black text-amber-600 mt-1">{{ $rolesCount['supervisor'] }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/30">
            <span class="text-[10px] font-bold uppercase text-emerald-700">Inspector EE</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $rolesCount['inspector_ee'] }}</div>
        </div>
        <div class="bg-white p-3.5 rounded-2xl border border-purple-200 bg-purple-50/30">
            <span class="text-[10px] font-bold uppercase text-purple-700">Inspector Auto/WH</span>
            <div class="text-xl font-black text-purple-600 mt-1">{{ $rolesCount['inspector_automotive'] + $rolesCount['inspector_wire_harness'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Name, NIK, Employee ID, Email..." 
                    class="w-full text-xs rounded-xl border-slate-200 py-2.5">
            </div>
            <div>
                <select name="role" class="w-full text-xs rounded-xl border-slate-200 py-2.5">
                    <option value="">All Roles / Sub-Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>System Administrator</option>
                    <option value="master_data" {{ request('role') === 'master_data' ? 'selected' : '' }}>Master Data / Logistics</option>
                    <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>IQC Supervisor</option>
                    <option value="inspector_ee" {{ request('role') === 'inspector_ee' ? 'selected' : '' }}>EE Part Inspector</option>
                    <option value="inspector_automotive" {{ request('role') === 'inspector_automotive' ? 'selected' : '' }}>Automotive Inspector</option>
                    <option value="inspector_wire_harness" {{ request('role') === 'inspector_wire_harness' ? 'selected' : '' }}>Wire Harness Inspector</option>
                </select>
            </div>
            <div>
                <select name="status_aktif" class="w-full text-xs rounded-xl border-slate-200 py-2.5">
                    <option value="">All Account Statuses</option>
                    <option value="1" {{ request('status_aktif') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status_aktif') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex-1 transition cursor-pointer">
                    Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Full Name & NIK</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">System Role (Sub-Role)</th>
                        <th class="py-3 px-4 text-center">Work Shift</th>
                        <th class="py-3 px-4 text-center">Account Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $u->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">NIK: {{ $u->nik }} @if($u->nip)| Employee ID: {{ $u->nip }}@endif</div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-600">
                            {{ $u->email }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border 
                                @if($u->role === 'admin') bg-indigo-50 text-indigo-700 border-indigo-200
                                @elseif($u->role === 'master_data') bg-blue-50 text-blue-700 border-blue-200
                                @elseif($u->role === 'supervisor') bg-amber-50 text-amber-700 border-amber-200
                                @elseif($u->role === 'inspector_ee') bg-emerald-50 text-emerald-700 border-emerald-200
                                @elseif($u->role === 'inspector_automotive') bg-teal-50 text-teal-700 border-teal-200
                                @elseif($u->role === 'inspector_wire_harness') bg-purple-50 text-purple-700 border-purple-200
                                @else bg-slate-100 text-slate-700 border-slate-200 @endif">
                                {{ $u->role_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-700 font-mono">
                            Shift {{ $u->shift ?? '1' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $u->status_aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $u->status_aktif ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <button type="button" onclick="openEditUserModal({{ json_encode($u) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition cursor-pointer" title="Edit Account">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>

                            @if($u->id !== auth()->id())
                            <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition cursor-pointer" title="{{ $u->status_aktif ? 'Deactivate' : 'Activate' }}">
                                    <span class="material-symbols-outlined text-[16px]">{{ $u->status_aktif ? 'block' : 'check_circle' }}</span>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">No user accounts registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100">{{ $users->links() }}</div>
        @endif
    </div>

</div>

<!-- Modal Buat Akun Baru -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">person_add</span>
                Create New User Account
            </h3>
            <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. Serlin Yolanda" class="w-full text-xs rounded-xl border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NIK / Operator ID *</label>
                    <input type="text" name="nik" required placeholder="e.g. INS-EE-002" class="w-full text-xs font-mono rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Employee ID (NIP)</label>
                    <input type="text" name="nip" placeholder="e.g. 3312411105" class="w-full text-xs font-mono rounded-xl border-slate-200">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                <input type="email" name="email" required placeholder="user@iqc.local" class="w-full text-xs font-mono rounded-xl border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">System Role (Sub-Role) *</label>
                    <select name="role" required class="w-full text-xs rounded-xl border-slate-200">
                        <option value="inspector_ee">EE Part Inspector</option>
                        <option value="inspector_automotive">Automotive Inspector</option>
                        <option value="inspector_wire_harness">Wire Harness Inspector</option>
                        <option value="supervisor">IQC Supervisor</option>
                        <option value="master_data">Master Data / Logistics</option>
                        <option value="admin">System Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Work Shift *</label>
                    <select name="shift" required class="w-full text-xs rounded-xl border-slate-200">
                        <option value="1">Shift 1 (Morning)</option>
                        <option value="2">Shift 2 (Afternoon)</option>
                        <option value="3">Shift 3 (Night)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Default Password *</label>
                <input type="password" name="password" required minlength="6" value="password123" class="w-full text-xs font-mono rounded-xl border-slate-200">
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs cursor-pointer">Save Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Akun -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Update User Account: <span id="editUserNik" class="font-mono text-indigo-600"></span></h3>
            <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="editUserForm" method="POST" class="mt-4 space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" id="editUserName" required class="w-full text-xs rounded-xl border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Employee ID (NIP)</label>
                    <input type="text" name="nip" id="editUserNip" class="w-full text-xs font-mono rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="email" id="editUserEmail" required class="w-full text-xs font-mono rounded-xl border-slate-200">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">System Role (Sub-Role) *</label>
                    <select name="role" id="editUserRole" required class="w-full text-xs rounded-xl border-slate-200">
                        <option value="inspector_ee">EE Part Inspector</option>
                        <option value="inspector_automotive">Automotive Inspector</option>
                        <option value="inspector_wire_harness">Wire Harness Inspector</option>
                        <option value="supervisor">IQC Supervisor</option>
                        <option value="master_data">Master Data / Logistics</option>
                        <option value="admin">System Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Work Shift *</label>
                    <select name="shift" id="editUserShift" required class="w-full text-xs rounded-xl border-slate-200">
                        <option value="1">Shift 1 (Morning)</option>
                        <option value="2">Shift 2 (Afternoon)</option>
                        <option value="3">Shift 3 (Night)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Account Activation Status *</label>
                <select name="status_aktif" id="editUserStatus" required class="w-full text-xs rounded-xl border-slate-200">
                    <option value="1">Active (Can Log In)</option>
                    <option value="0">Inactive (Blocked)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Reset Password (Leave blank to keep current)</label>
                <input type="password" name="password" minlength="6" placeholder="Type new password..." class="w-full text-xs font-mono rounded-xl border-slate-200">
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs cursor-pointer">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUserModal(user) {
    document.getElementById('editUserNik').innerText = user.nik;
    document.getElementById('editUserName').value = user.name;
    document.getElementById('editUserNip').value = user.nip || '';
    document.getElementById('editUserEmail').value = user.email;
    document.getElementById('editUserRole').value = user.role;
    document.getElementById('editUserShift').value = user.shift || '1';
    document.getElementById('editUserStatus').value = user.status_aktif ? '1' : '0';
    document.getElementById('editUserForm').action = "/admin/users/" + user.id;
    document.getElementById('editUserModal').classList.remove('hidden');
}
</script>
@endsection