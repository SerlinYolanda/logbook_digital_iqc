<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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

        $user = User::where('nik', $credentials['nik'])->first();

        if ($user && password_verify($credentials['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang, {$user->name}!");
        }

        return back()->withErrors([
            'nik' => 'NIK atau kata sandi yang Anda masukkan tidak sesuai.',
        ])->onlyInput('nik');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah keluar dari sistem.');
    }

    public function rootRedirect()
    {
        return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
    }

    public function profile()
    {
        return view('profile.index');
    }

    public function pokaYokeDenied()
    {
        return view('poka-yoke.denied', [
            'rule_violated' => 'Sistem Poka-Yoke: Inspektur dilarang memvalidasi keputusannya sendiri atau mengubah parameter spesifikasi.',
            'user_role' => auth()->user()->role ?? 'guest',
            'target_resource' => 'Approval & Master Parameter',
        ]);
    }
}


