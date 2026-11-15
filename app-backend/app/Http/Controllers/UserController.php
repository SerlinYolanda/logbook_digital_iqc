<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * UC-14: Kelola Akun Pengguna (Actor: Admin Sistem)
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('status_aktif')) {
            $query->where('status_aktif', $request->status_aktif == '1');
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('nik', 'like', $search)
                  ->orWhere('nip', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        $users = $query->orderBy('name')->paginate(12)->withQueryString();

        $rolesCount = [
            'total'                  => User::count(),
            'active'                 => User::where('status_aktif', true)->count(),
            'admin'                  => User::where('role', 'admin')->count(),
            'master_data'            => User::where('role', 'master_data')->count(),
            'supervisor'             => User::where('role', 'supervisor')->count(),
            'inspector_ee'           => User::where('role', 'inspector_ee')->count(),
            'inspector_automotive'   => User::where('role', 'inspector_automotive')->count(),
            'inspector_wire_harness' => User::where('role', 'inspector_wire_harness')->count(),
        ];

        return view('admin.users.index', compact('users', 'rolesCount'));
    }

    /**
     * Store newly created user (UC-14)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'nik'      => 'required|string|unique:users,nik|max:30',
            'nip'      => 'nullable|string|max:30',
            'email'    => 'required|email|unique:users,email|max:100',
            'role'     => 'required|in:admin,master_data,supervisor,inspector_ee,inspector_automotive,inspector_wire_harness',
            'shift'    => 'required|in:1,2,3',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'         => $validated['name'],
            'nik'          => $validated['nik'],
            'nip'          => $validated['nip'] ?? $validated['nik'],
            'email'        => $validated['email'],
            'role'         => $validated['role'],
            'shift'        => $validated['shift'],
            'status_aktif' => true,
            'password'     => Hash::make($validated['password']),
        ]);

        AuditTrail::log(
            'USER_CREATED',
            "User: {$user->name} ({$user->nik})",
            null,
            "Role: {$user->role_label}, Shift: {$user->shift}"
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User account '{$user->name}' ({$user->role_label}) has been created successfully.");
    }

    /**
     * Update user details (UC-14)
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'nip'          => 'nullable|string|max:30',
            'email'        => 'required|email|unique:users,email,' . $user->id . '|max:100',
            'role'         => 'required|in:admin,master_data,supervisor,inspector_ee,inspector_automotive,inspector_wire_harness',
            'shift'        => 'required|in:1,2,3',
            'status_aktif' => 'required|boolean',
            'password'     => 'nullable|string|min:6',
        ]);

        $oldData = "Role: {$user->role}, Status: {$user->status_aktif}";

        $updateData = [
            'name'         => $validated['name'],
            'nip'          => $validated['nip'] ?? $user->nip,
            'email'        => $validated['email'],
            'role'         => $validated['role'],
            'shift'        => $validated['shift'],
            'status_aktif' => (bool) $validated['status_aktif'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditTrail::log(
            'USER_UPDATED',
            "User: {$user->name} ({$user->nik})",
            $oldData,
            "Role: {$user->role}, Status: {$user->status_aktif}"
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User account details for '{$user->name}' have been updated.");
    }

    /**
     * Toggle active/inactive status (UC-14)
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own active session account.');
        }

        $newStatus = !$user->status_aktif;
        $user->update(['status_aktif' => $newStatus]);

        AuditTrail::log(
            'USER_STATUS_TOGGLED',
            "User: {$user->name} ({$user->nik})",
            'Active: ' . ($user->status_aktif ? '1' : '0'),
            'Active: ' . ($newStatus ? '1' : '0')
        );

        $msg = $newStatus ? "User account '{$user->name}' has been reactivated." : "User account '{$user->name}' has been deactivated.";
        return redirect()->route('admin.users.index')->with('success', $msg);
    }
}
