<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Check if user has one of the allowed roles
        if (!in_array($user->role, $roles)) {
            // Poka-Yoke error page: dynamic role-specific authorization guard
            if (in_array('admin', $roles) && !in_array('supervisor', $roles)) {
                $roleLabel = 'System Administrator';
                $violation = "Akses Ditolak (Sistem Poka-Yoke): Modul ini (GRN Tracking / Admin Controls) hanya dapat diakses oleh {$roleLabel}. Akun Anda ({$user->role}) tidak memiliki hak akses.";
            } elseif (in_array('supervisor', $roles) && !in_array('admin', $roles)) {
                $roleLabel = 'IQC Supervisor';
                $violation = "Akses Ditolak (Sistem Poka-Yoke): Hanya {$roleLabel} yang memiliki wewenang untuk mengevaluasi & memberikan disposisi approval material bermasalah/reject. Inspektur dan Administrator dilarang memvalidasi hasil inspeksi.";
            } else {
                $roleLabel = implode(', ', $roles);
                $violation = "Akses Ditolak (Sistem Poka-Yoke): Modul ini membutuhkan hak akses {$roleLabel}. Akun Anda ({$user->role}) tidak memiliki hak akses.";
            }

            return response()->view('poka-yoke.denied', [
                'rule_violated'   => $violation,
                'user_role'       => $user->role,
                'target_resource' => $request->path(),
            ], 403);
        }

        return $next($request);
    }

}
