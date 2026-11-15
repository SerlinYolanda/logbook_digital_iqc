<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditTrail;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Check if account is active
        if (isset($user->status_aktif) && !$user->status_aktif) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan oleh Administrator Sistem.');
        }

        // Support role matching:
        // 'inspector' matches 'inspector', 'inspector_ee', 'inspector_automotive', 'inspector_wire_harness'
        $isAllowed = false;
        foreach ($roles as $allowedRole) {
            if ($user->role === $allowedRole) {
                $isAllowed = true;
                break;
            }
            if ($allowedRole === 'inspector' && str_starts_with($user->role, 'inspector')) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            AuditTrail::log(
                'ROLE_ACCESS_DENIED',
                'Route: ' . $request->path(),
                'User Role: ' . $user->role,
                'Required Roles: ' . implode(', ', $roles)
            );

            return response()->view('poka-yoke.denied', [
                'rule_violated' => "Pemisahan Kewenangan (Separation of Duties): Role Anda ({$user->role_label}) tidak memiliki wewenang untuk mengakses halaman ini. Akses dibatasi untuk: " . implode(', ', $roles),
                'user_role' => $user->role_label,
                'target_resource' => $request->path(),
            ], 403);
        }

        return $next($request);
    }
}
