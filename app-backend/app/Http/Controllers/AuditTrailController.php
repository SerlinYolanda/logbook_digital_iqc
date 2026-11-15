<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * UC-15: Pantau Audit Trail (Actor: Admin Sistem)
     */
    public function index(Request $request)
    {
        $query = AuditTrail::with('user');

        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('objek_data', 'like', $s)
                  ->orWhere('nilai_sebelum', 'like', $s)
                  ->orWhere('nilai_sesudah', 'like', $s)
                  ->orWhere('alamat_ip', 'like', $s)
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', $s)
                         ->orWhere('nik', 'like', $s);
                  });
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('waktu', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('waktu', '<=', $request->tanggal_sampai);
        }

        $logs = $query->orderBy('waktu', 'desc')->paginate(20)->withQueryString();

        $actionTypes = AuditTrail::select('aksi')->distinct()->pluck('aksi');
        $users = User::orderBy('name')->get();

        $pokaYokeViolationsCount = AuditTrail::where('aksi', 'POKA_YOKE_REJECTED')->count();
        $totalLogsCount = AuditTrail::count();

        return view('admin.audit-trails.index', compact('logs', 'actionTypes', 'users', 'pokaYokeViolationsCount', 'totalLogsCount'));
    }
}

