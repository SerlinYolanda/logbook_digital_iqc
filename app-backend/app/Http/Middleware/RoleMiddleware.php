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
            // Poka-Yoke error page: only strictly authorized role (SPV) can approve / decide
            $roleLabel = in_array('supervisor', $roles) ? 'IQC Supervisor' : 'pihak yang berwenang';
            return response()->view('poka-yoke.denied', [
                'rule_violated' => "Akses Ditolak (Sistem Poka-Yoke): Hanya {$roleLabel} yang memiliki wewenang untuk mengevaluasi & memberikan disposisi approval material bermasalah/reject. Inspektur dan Administrator dilarang memvalidasi hasil inspeksi.",
                'user_role' => $user->role,
                'target_resource' => $request->path(),
            ], 403);
        }

        return $next($request);
    }

}
