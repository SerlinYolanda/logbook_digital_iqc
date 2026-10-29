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

    public function pokaYokeDenied(Request $request)
    {
        $partNumber = $request->query('part_number');
        $partName = $request->query('part_name');
        $grn = $request->query('grn');

        if ($partNumber || $partName) {
            $pUpper = strtoupper($partNumber . ' ' . $partName);
            if (str_contains($pUpper, 'WH-') || str_contains($pUpper, 'HARNESS') || str_contains($pUpper, 'WIRING')) {
                $ruleViolated = "Sistem Poka-Yoke: Inspektur EE Part otomatis ditolak memeriksa komponen {$partNumber} ({$partName}) karena merupakan kategori Wire Harness (Bukan Komponen EE).";
                $ruleCode = "POKA-YOKE-RULE-08 (Wire Harness Isolation Guard)";
                $targetResource = "Wire Harness Testing Standard (USCAR-21 / JIS C 5402)";
                $resolution = "Material ini adalah komponen Wire Harness dan memiliki standar uji kontinuitas multi-pin, terminal crimping & pull-force terpisah. Dokumen dan lot fisik harus dialihkan ke Tim IQC Wire Harness per SOP WH-SOP-004.";
            } else {
                $ruleViolated = "Sistem Poka-Yoke: Inspektur EE Part otomatis ditolak memeriksa komponen {$partNumber} ({$partName}) karena merupakan kategori Automotive Part (Bukan Komponen EE).";
                $ruleCode = "POKA-YOKE-RULE-09 (Automotive Stamping Isolation Guard)";
                $targetResource = "Automotive Mechanical Standard (ISO/TS 16949 / JIS D 0201)";
                $resolution = "Material ini adalah komponen Automotive Part / Stamping dan memerlukan uji ketebalan plat, coating thickness & tensile test tersendiri. Dokumen dan lot fisik harus dialihkan ke Tim IQC Automotive per SOP AUTO-SOP-012.";
            }
        } else {
            $ruleViolated = $request->query('reason', 'Sistem Poka-Yoke: Inspektur dilarang memvalidasi keputusannya sendiri atau mengubah parameter spesifikasi.');
            $ruleCode = "POKA-YOKE-RULE-04 (Dual Control & Segregation)";
            $targetResource = 'Approval & Master Parameter';
            $resolution = 'Aksi ini memerlukan wewenang Quality Supervisor (SPV-001) atau otoritas yang berwenang.';
        }

        return view('poka-yoke.denied', [
            'rule_violated'   => $ruleViolated,
            'rule_code'       => $ruleCode,
            'user_role'       => auth()->user()->role ?? 'inspector',
            'target_resource' => $targetResource,
            'resolution'      => $resolution,
            'part_number'     => $partNumber,
            'part_name'       => $partName,
            'grn'             => $grn,
        ]);
    }
}


