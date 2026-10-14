<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['admin', 'supervisor'])) {
            return response()->view('poka-yoke.denied', [
                'rule_violated' => 'Poka-Yoke Guard: Only Administrator and IQC Supervisor have the authority to manage user accounts and assign inspector shifts.',
                'user_role' => auth()->user()->role,
                'target_resource' => 'User Account & Shift Management',
            ], 403);
        }

        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(10);

        $nextNiks = [
            'inspector' => self::generateNextNik('inspector'),
            'supervisor' => self::generateNextNik('supervisor'),
            'admin' => self::generateNextNik('admin'),
        ];

        return view('admin.users.index', compact('users', 'nextNiks'));
    }

    public static function generateNextNik(string $role): string
    {
        $prefix = match ($role) {
            'inspector' => 'INS',
            'supervisor' => 'SPV',
            'admin' => 'ADM',
            default => 'USR',
        };

        $users = User::where('nik', 'like', "{$prefix}-%")->get();
        $maxNum = 0;
        foreach ($users as $u) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/i', $u->nik, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return sprintf('%s-%03d', $prefix, $maxNum + 1);
    }

    public function setInspectorShift(Request $request, User $user)
    {
        if (!in_array(auth()->user()->role, ['admin', 'supervisor'])) {
            return back()->with('error', 'Poka-Yoke Rule Enforced: Only IQC Supervisor and Administrator can assign inspector shifts.');
        }

        $validated = $request->validate([
            'shift' => 'required|in:1,2',
        ]);

        $user->shift = $validated['shift'];
        $user->save();

        $shiftName = $validated['shift'] === '1' ? 'Shift 1 (07:00-15:30)' : 'Shift 2 (15:30-23:00)';
        return back()->with('success', "Inspector [{$user->name}] operational shift successfully updated to [{$shiftName}] by " . strtoupper(auth()->user()->role) . ".");
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized access.');
        }

        if (empty($request->nik) || trim($request->nik) === '' || strtoupper(trim($request->nik)) === 'AUTO') {
            $request->merge(['nik' => self::generateNextNik($request->role ?? 'inspector')]);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'nik'      => 'required|string|max:50|unique:users,nik',
            'role'     => 'required|in:inspector,supervisor,admin',
            'shift'    => 'nullable|string',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $validated['shift'] = $validated['shift'] ?? '1';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "User account [{$validated['name']}] created successfully as " . strtoupper($validated['role']) . ".");
    }

    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'nik'      => 'required|string|max:50|unique:users,nik,' . $user->id,
            'role'     => 'required|in:inspector,supervisor,admin',
            'shift'    => 'nullable|string',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        if (empty($validated['shift'])) {
            unset($validated['shift']);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "User account [{$user->name}] updated successfully.");
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized access.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Poka-Yoke Guard: You cannot delete your own active administrator account!');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User account [{$name}] has been deleted.");
    }
}
