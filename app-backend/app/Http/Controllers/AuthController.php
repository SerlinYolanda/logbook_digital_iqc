<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AuditTrail;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nik'      => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('nik', $credentials['nik'])
            ->orWhere('nip', $credentials['nik'])
            ->orWhere('email', $credentials['nik'])
            ->first();

        if ($user && password_verify($credentials['password'], $user->password)) {
            if (isset($user->status_aktif) && !$user->status_aktif) {
                return back()->withErrors([
                    'nik' => 'Your account has been deactivated by the System Administrator.',
                ])->onlyInput('nik');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            AuditTrail::log(
                'LOGIN',
                'User Login: ' . $user->name . ' (' . $user->role_label . ')',
                null,
                'Success'
            );

            return redirect()->intended(route('dashboard'))
                ->with('success', "Welcome back, {$user->name} ({$user->role_label})!");
        }

        AuditTrail::log(
            'LOGIN_FAILED',
            'Attempted NIK: ' . $credentials['nik'],
            null,
            'Failed - Invalid credentials',
            $user ? $user->id : null
        );

        return back()->withErrors([
            'nik' => 'The NIK / Employee ID or password entered is incorrect.',
        ])->onlyInput('nik');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditTrail::log(
                'LOGOUT',
                'User Logout: ' . Auth::user()->name
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out of the system.');
    }

    public function rootRedirect()
    {
        return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
    }

    public function profile()
    {
        return view('profile.index');
    }

    public function pokaYokeDenied(Request $request)
    {
        $partNumber = $request->query('part_number');
        $partName = $request->query('part_name');
        $commodity = $request->query('commodity');
        $grn = $request->query('grn');

        $user = Auth::user();
        $userRole = $user ? $user->role_label : 'Inspector';

        $ruleViolated = $request->query('reason', "Poka-Yoke System: Inspector sub-role {$userRole} is not authorized to inspect material in the " . ($commodity ? strtoupper($commodity) : 'this') . " category. The physical lot must be routed to the respective division inspector per IQC SOP.");
        $ruleCode = "POKA-YOKE-COM-GUARD (Commodity Segregation Guard)";
        $targetResource = "Lot Inspection: " . ($grn ?? 'Inbound Material');
        $resolution = "This material belongs to commodity " . ($commodity ? strtoupper($commodity) : '') . ". Only personnel certified in that specific commodity sub-role may access the inspection form.";

        return view('poka-yoke.denied', [
            'rule_violated'   => $ruleViolated,
            'rule_code'       => $ruleCode,
            'user_role'       => $userRole,
            'target_resource' => $targetResource,
            'resolution'      => $resolution,
            'part_number'     => $partNumber,
            'part_name'       => $partName,
            'grn'             => $grn,
        ]);
    }
}
