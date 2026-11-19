<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\Receiving;
use App\Models\Part;
use App\Models\PartParameter;
use App\Models\Measurement;
use App\Models\Defect;
use App\Models\DefectCategory;
use App\Models\NestProcess;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LogbookController extends Controller
{
    /**
     * UC-07: Pantau Status dan Riwayat Logbook
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeFolder = $request->query('folder', 'all');

        $query = Logbook::with(['receiving.supplier', 'receiving.part', 'inspector', 'latestMrbDisposition.supervisor', 'defects.defectCategory', 'measurements']);

        // Folder Tabs Filter
        if ($activeFolder === 'ee_part') {
            $query->whereHas('receiving.part', fn($q) => $q->where('kategori_komoditas', 'ee_part'));
        } elseif ($activeFolder === 'automotive') {
            $query->whereHas('receiving.part', fn($q) => $q->where('kategori_komoditas', 'automotive'));
        } elseif ($activeFolder === 'wire_harness') {
            $query->whereHas('receiving.part', fn($q) => $q->where('kategori_komoditas', 'wire_harness'));
        } elseif ($activeFolder === 'mrb_hold') {
            $query->whereIn('status', ['menunggu_penetapan', 'perlu_revisi']);
            if (!$request->filled('status')) {
                $query->whereIn('status', ['menunggu_penetapan', 'perlu_revisi']);
            }
        }

        // Filter by status
        // Filter by status (explicit status selection takes precedence)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by commodity
        if ($request->filled('komoditas')) {
            $query->whereHas('receiving.part', function ($q) use ($request) {
                $q->where('kategori_komoditas', $request->komoditas);
            });
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->whereHas('receiving', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        // Filter by date (if exact single date selected)
        if ($request->filled('tanggal_dari') && !$request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_inspeksi', $request->tanggal_dari);
        } elseif ($request->filled('tanggal_dari') && $request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_inspeksi', '>=', $request->tanggal_dari)
                  ->whereDate('tanggal_inspeksi', '<=', $request->tanggal_sampai);
        }

        // Search keyword across all related attributes
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('receiving', function ($rq) use ($search) {
                    $rq->where('no_grn_iqa', 'like', $search)
                       ->orWhere('no_lot', 'like', $search)
                       ->orWhere('no_po', 'like', $search)
                       ->orWhereHas('part', function ($pq) use ($search) {
                           $pq->where('kode_part', 'like', $search)
                              ->orWhere('nama_part', 'like', $search);
                       })
                       ->orWhereHas('supplier', function ($sq) use ($search) {
                           $sq->where('nama_supplier', 'like', $search);
                       });
                })
                ->orWhereHas('inspector', function ($iq) use ($search) {
                    $iq->where('name', 'like', $search);
                })
                ->orWhere('catatan', 'like', $search)
                ->orWhere('visual_notes', 'like', $search)
                ->orWhere('date_code', 'like', $search);
            });
        }

        $logbooks = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Summary counts
        $totalLogbooks = Logbook::count();
        $draftCount = Logbook::where('status', 'draft')->count();
        $waitingMrbCount = Logbook::where('status', 'menunggu_penetapan')->count();
        $revisionCount = Logbook::where('status', 'perlu_revisi')->count();
        $acceptedCount = Logbook::where('status', 'accept')->count();
        $rejectedCount = Logbook::whereIn('status', ['return_to_vendor', 'scrap', 'rework'])->count();

        // Folder Archive Tab Counts
        $eeCount = Logbook::whereHas('receiving.part', fn($q) => $q->where('kategori_komoditas', 'ee_part'))->count();
        $autoCount = Logbook::whereHas('receiving.part', fn($q) => $q->where('kategori_komoditas', 'automotive'))->count();
        $whCount = Logbook::whereHas('receiving.part', fn($q) => $q->where('kategori_komoditas', 'wire_harness'))->count();
        $mrbHoldCount = Logbook::whereIn('status', ['menunggu_penetapan', 'perlu_revisi'])->count();

        $suppliers = Supplier::where('status_aktif', true)->orderBy('nama_supplier')->get();

        return view('logbooks.index', compact(
            'logbooks',
            'suppliers',
            'activeFolder',
            'totalLogbooks',
            'draftCount',
            'waitingMrbCount',
            'revisionCount',
            'acceptedCount',
            'rejectedCount',
            'eeCount',
            'autoCount',
            'whCount',
            'mrbHoldCount'
        ));
    }

    /**
     * UC-03: Pindai Lot dan Validasi Poka-Yoke
     */
    public function scan(Request $request)
    {
        $code = strtoupper(trim($request->input('code', '')));

        if (empty($code)) {
            return back()->with('error', 'Please enter or scan a GR Number or Lot Number.');
        }

        // Lookup receiving by GR or Lot (supports exact match and flexible prefix/suffix)
        $receiving = Receiving::with('part')
            ->where('no_grn_iqa', $code)
            ->orWhere('no_lot', $code)
            ->orWhere('no_grn_iqa', 'like', "%{$code}%")
            ->orWhere('no_lot', 'like', "%{$code}%")
            ->first();

        if (!$receiving) {
            return back()->with('error', "GR or Lot Number '{$code}' was not found in inbound material queue. Please verify the code or check with Logistics.");
        }

        $user = auth()->user();
        $part = $receiving->part;

        // Poka-Yoke Validation (Commodity Match)
        if (!$user->canInspectCommodity($part->kategori_komoditas)) {
            AuditTrail::log(
                'POKA_YOKE_REJECTED',
                "Receiving {$receiving->no_grn_iqa} (Part: {$part->kode_part}, Komoditas: {$part->komoditas_label})",
                "Inspector Role: {$user->role} ({$user->role_label})",
                "BLOCKED: Material commodity ({$part->komoditas_label}) does not match inspector authorization!"
            );

            return redirect()->route('poka-yoke.denied', [
                'part_number' => $part->kode_part,
                'part_name'   => $part->nama_part,
                'commodity'   => $part->komoditas_label,
                'grn'         => $receiving->no_grn_iqa,
            ]);
        }

        // Check if there is an existing logbook
        $existingLogbook = $receiving->latest_logbook;
        if ($existingLogbook) {
            if ($existingLogbook->status === 'perlu_revisi' || $existingLogbook->status === 'draft') {
                return redirect()->route('logbooks.edit', $existingLogbook->id)
                    ->with('info', "Continuing logbook with status: {$existingLogbook->status_label}");
            }
            return redirect()->route('logbooks.show', $existingLogbook->id);
        }

        return redirect()->route('logbooks.create', ['receiving_id' => $receiving->id]);
    }

    /**
     * UC-04: Form Input Logbook Inspeksi
     */
    public function create(Request $request)
    {
        $receivingId = $request->query('receiving_id');
        $user = auth()->user();

        $receiving = null;
        if ($receivingId) {
            $receiving = Receiving::with(['supplier', 'part.parameters'])->findOrFail($receivingId);

            // Poka-Yoke check
            if (!$user->canInspectCommodity($receiving->part->kategori_komoditas)) {
                AuditTrail::log(
                    'POKA_YOKE_REJECTED',
                    "Receiving {$receiving->no_grn_iqa} (Part: {$receiving->part->kode_part})",
                    "Inspector: {$user->role_label}",
                    "Akses ditolak karena perbedaan komoditas"
                );
                return redirect()->route('poka-yoke.denied', [
                    'part_number' => $receiving->part->kode_part,
                    'part_name'   => $receiving->part->nama_part,
                    'commodity'   => $receiving->part->komoditas_label,
                    'grn'         => $receiving->no_grn_iqa,
                ]);
            }
        }

        // Available pending receivings for user's commodity
        $receivingsQuery = Receiving::with(['supplier', 'part'])
            ->whereIn('status_antrean', ['menunggu_inspeksi', 'dalam_inspeksi']);

        if ($user->getInspectorCommodity()) {
            $receivingsQuery->whereHas('part', function ($q) use ($user) {
                $q->where('kategori_komoditas', $user->getInspectorCommodity());
            });
        }

        // Search filter: shows matching GR/Lot first before inspecting
        $search = trim($request->query('search', $request->query('code', '')));
        if (!empty($search)) {
            $receivingsQuery->where(function ($q) use ($search) {
                $q->where('no_grn_iqa', 'like', "%{$search}%")
                  ->orWhere('no_lot', 'like', "%{$search}%")
                  ->orWhere('no_po', 'like', "%{$search}%")
                  ->orWhereHas('part', function ($pq) use ($search) {
                      $pq->where('kode_part', 'like', "%{$search}%")
                         ->orWhere('nama_part', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('nama_supplier', 'like', "%{$search}%");
                  });
            });
        }

        $pendingReceivings = $receivingsQuery->orderBy('tanggal_terima')->get();
        $defectCategories = DefectCategory::orderBy('nama_defect')->get();

        return view('logbooks.create', compact('receiving', 'pendingReceivings', 'defectCategories', 'search'));
    }

    /**
     * Store new logbook & measurements (UC-04)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiving_id'        => 'required|exists:receivings,id',
            'qty_sampel'          => 'required|integer|min:1|max:50',
            'action_type'         => 'required|in:draft,submit',
            'visual_packaging'    => 'nullable|in:pass,fail',
            'visual_esd_moisture' => 'nullable|in:pass,fail,na',
            'visual_body'         => 'nullable|in:pass,fail',
            'visual_terminal'     => 'nullable|in:pass,fail',
            'visual_notes'        => 'nullable|string|max:1000',
            'date_code'           => 'nullable|string|max:50',
            'manufacturing_date'  => 'nullable|date',
            'expiration_date'     => 'nullable|date',
            'measurements'        => 'nullable|array',
        ]);

        $receiving = Receiving::with('part.parameters')->findOrFail($validated['receiving_id']);
        $user = auth()->user();

        // Poka-Yoke Check
        if (!$user->canInspectCommodity($receiving->part->kategori_komoditas)) {
            return redirect()->route('poka-yoke.denied', [
                'part_number' => $receiving->part->kode_part,
                'part_name'   => $receiving->part->nama_part,
                'commodity'   => $receiving->part->komoditas_label,
                'grn'         => $receiving->no_grn_iqa,
            ]);
        }

        // Evaluate Expiration & Shelf-Life Poka-Yoke
        $isExpired = false;
        $shelfLifeStatus = 'normal';
        if (!empty($validated['expiration_date'])) {
            $expDate = \Carbon\Carbon::parse($validated['expiration_date'])->startOfDay();
            $today = now()->startOfDay();
            if ($expDate->lt($today)) {
                $isExpired = true;
                $shelfLifeStatus = 'expired';
            } elseif ($expDate->lte($today->copy()->addDays(30))) {
                $shelfLifeStatus = 'near_expiry';
            }
        }

        // Evaluate Visual Non-Conformance
        $hasVisualFail = ($validated['visual_packaging'] ?? 'pass') === 'fail'
            || ($validated['visual_esd_moisture'] ?? 'pass') === 'fail'
            || ($validated['visual_body'] ?? 'pass') === 'fail'
            || ($validated['visual_terminal'] ?? 'pass') === 'fail';

        $status = $validated['action_type'] === 'draft' ? 'draft' : 'accept';

        $logbook = Logbook::create([
            'receiving_id'        => $receiving->id,
            'inspector_id'        => $user->id,
            'qty_sampel'          => $validated['qty_sampel'],
            'visual_packaging'    => $validated['visual_packaging'] ?? 'pass',
            'visual_esd_moisture' => $validated['visual_esd_moisture'] ?? 'pass',
            'visual_body'         => $validated['visual_body'] ?? 'pass',
            'visual_terminal'     => $validated['visual_terminal'] ?? 'pass',
            'visual_notes'        => $validated['visual_notes'] ?? null,
            'date_code'           => $validated['date_code'] ?? null,
            'manufacturing_date'  => $validated['manufacturing_date'] ?? null,
            'expiration_date'     => $validated['expiration_date'] ?? null,
            'is_expired'          => $isExpired,
            'shelf_life_status'   => $shelfLifeStatus,
            'status'              => $status,
            'tanggal_inspeksi'    => now(),
        ]);

        $receiving->update(['status_antrean' => 'dalam_inspeksi']);

        // Save sample measurements
        $hasOutOfSpec = false;
        if (!empty($validated['measurements'])) {
            foreach ($validated['measurements'] as $paramId => $samples) {
                $parameter = PartParameter::find($paramId);
                if (!$parameter) continue;

                foreach ($samples as $sampleNum => $val) {
                    if ($val === null || $val === '') continue;

                    $tolerance = $parameter->checkTolerance($val);
                    if ($tolerance === 'fail') {
                        $hasOutOfSpec = true;
                    }

                    Measurement::create([
                        'logbook_id'        => $logbook->id,
                        'part_parameter_id' => $paramId,
                        'sample_number'     => $sampleNum,
                        'nilai_ukur'        => (float) $val,
                        'status_toleransi'  => $tolerance,
                    ]);
                }
            }
        }

        if ($validated['action_type'] === 'submit') {
            if ($isExpired || $hasVisualFail || $hasOutOfSpec) {
                // Non-conformance detected in Shelf-Life, Visual, or Measurement -> Route to MRB Review
                $logbook->update(['status' => 'menunggu_penetapan']);
                
                $reasons = [];
                if ($isExpired) $reasons[] = 'Material has EXPIRED past its shelf-life';
                if ($hasVisualFail) $reasons[] = 'Visual / Packaging check failed';
                if ($hasOutOfSpec) $reasons[] = 'Measurements out-of-spec';

                $reasonText = implode('; ', $reasons);
                $msg = "Non-conformance detected ({$reasonText})! Logbook has been routed to Pending MRB Review.";
                
                AuditTrail::log(
                    'SUBMIT_INSPECTION_MRB_HOLD',
                    "Logbook #{$logbook->id} (GRN: {$receiving->no_grn_iqa})",
                    null,
                    "Routed to MRB: {$reasonText}"
                );
            } else {
                $receiving->update(['status_antrean' => 'selesai']);
                $msg = 'Quality inspection completed and passed (ACCEPT). Visual, shelf-life, and measurements verified!';
                
                AuditTrail::log(
                    'SUBMIT_INSPECTION_PASS',
                    "Logbook #{$logbook->id} (GRN: {$receiving->no_grn_iqa})",
                    null,
                    "Status: ACCEPT (All checks passed)"
                );
            }
        } else {
            $msg = 'Inspection logbook draft has been saved successfully.';
        }

        AuditTrail::log(
            $validated['action_type'] === 'draft' ? 'SAVE_DRAFT_LOGBOOK' : 'SUBMIT_LOGBOOK',
            "Logbook #{$logbook->id} (GRN: {$receiving->no_grn_iqa})",
            null,
            "Status: {$logbook->status}, Sampel: {$logbook->qty_sampel}"
        );

        return redirect()->route('logbooks.show', $logbook->id)->with('success', $msg);
    }

    /**
     * UC-04 & UC-07: Detail View Logbook
     */
    public function show(Logbook $logbook)
    {
        $logbook->load([
            'receiving.supplier',
            'receiving.part.parameters',
            'inspector',
            'measurements.partParameter',
            'defects.defectCategory',
            'nestProcess.inspector',
            'mrbDispositions.supervisor'
        ]);

        $defectCategories = DefectCategory::orderBy('nama_defect')->get();

        return view('logbooks.show', compact('logbook', 'defectCategories'));
    }

    /**
     * Edit draft or revision (UC-07)
     */
    public function edit(Logbook $logbook)
    {
        $logbook->load([
            'receiving.supplier',
            'receiving.part.parameters',
            'measurements',
            'defects.defectCategory',
            'latestMrbDisposition.supervisor'
        ]);

        $defectCategories = DefectCategory::orderBy('nama_defect')->get();

        return view('logbooks.edit', compact('logbook', 'defectCategories'));
    }

    /**
     * Update draft or revision (UC-07)
     */
    public function update(Request $request, Logbook $logbook)
    {
        $validated = $request->validate([
            'action_type'         => 'required|in:draft,submit',
            'visual_packaging'    => 'nullable|in:pass,fail',
            'visual_esd_moisture' => 'nullable|in:pass,fail,na',
            'visual_body'         => 'nullable|in:pass,fail',
            'visual_terminal'     => 'nullable|in:pass,fail',
            'visual_notes'        => 'nullable|string|max:1000',
            'date_code'           => 'nullable|string|max:50',
            'manufacturing_date'  => 'nullable|date',
            'expiration_date'     => 'nullable|date',
            'measurements'        => 'nullable|array',
        ]);

        // Evaluate Expiration & Shelf-Life Poka-Yoke
        $isExpired = false;
        $shelfLifeStatus = 'normal';
        if (!empty($validated['expiration_date'])) {
            $expDate = \Carbon\Carbon::parse($validated['expiration_date'])->startOfDay();
            $today = now()->startOfDay();
            if ($expDate->lt($today)) {
                $isExpired = true;
                $shelfLifeStatus = 'expired';
            } elseif ($expDate->lte($today->copy()->addDays(30))) {
                $shelfLifeStatus = 'near_expiry';
            }
        }

        $hasVisualFail = ($validated['visual_packaging'] ?? $logbook->visual_packaging) === 'fail'
            || ($validated['visual_esd_moisture'] ?? $logbook->visual_esd_moisture) === 'fail'
            || ($validated['visual_body'] ?? $logbook->visual_body) === 'fail'
            || ($validated['visual_terminal'] ?? $logbook->visual_terminal) === 'fail';

        $logbook->update([
            'visual_packaging'    => $validated['visual_packaging'] ?? $logbook->visual_packaging,
            'visual_esd_moisture' => $validated['visual_esd_moisture'] ?? $logbook->visual_esd_moisture,
            'visual_body'         => $validated['visual_body'] ?? $logbook->visual_body,
            'visual_terminal'     => $validated['visual_terminal'] ?? $logbook->visual_terminal,
            'visual_notes'        => $validated['visual_notes'] ?? $logbook->visual_notes,
            'date_code'           => $validated['date_code'] ?? $logbook->date_code,
            'manufacturing_date'  => $validated['manufacturing_date'] ?? $logbook->manufacturing_date,
            'expiration_date'     => $validated['expiration_date'] ?? $logbook->expiration_date,
            'is_expired'          => $isExpired,
            'shelf_life_status'   => $shelfLifeStatus,
        ]);

        $hasOutOfSpec = false;
        if (!empty($validated['measurements'])) {
            foreach ($validated['measurements'] as $paramId => $samples) {
                $parameter = PartParameter::find($paramId);
                if (!$parameter) continue;

                foreach ($samples as $sampleNum => $val) {
                    if ($val === null || $val === '') continue;

                    $tolerance = $parameter->checkTolerance($val);
                    if ($tolerance === 'fail') {
                        $hasOutOfSpec = true;
                    }

                    Measurement::updateOrCreate(
                        [
                            'logbook_id'        => $logbook->id,
                            'part_parameter_id' => $paramId,
                            'sample_number'     => $sampleNum,
                        ],
                        [
                            'nilai_ukur'       => (float) $val,
                            'status_toleransi' => $tolerance,
                        ]
                    );
                }
            }
        }

        if ($validated['action_type'] === 'submit') {
            $hasIssue = $hasOutOfSpec || $logbook->defects()->exists() || $hasVisualFail || $isExpired;
            $newStatus = $hasIssue ? 'menunggu_penetapan' : 'accept';
            $logbook->update([
                'status' => $newStatus,
                'tanggal_inspeksi' => now(),
            ]);

            if ($newStatus === 'accept') {
                $logbook->receiving->update(['status_antrean' => 'selesai']);
            }

            $msg = 'Revised logbook has been re-submitted to the Supervisor!';
        } else {
            $logbook->update(['status' => 'draft']);
            $msg = 'Draft changes have been saved successfully.';
        }

        AuditTrail::log(
            'UPDATE_LOGBOOK',
            "Logbook #{$logbook->id} (GRN: {$logbook->receiving->no_grn_iqa})",
            null,
            "Updated Status: {$logbook->status}"
        );

        return redirect()->route('logbooks.show', $logbook->id)->with('success', $msg);
    }

    /**
     * UC-05: Catat Defect dan Bukti Foto
     */
    public function storeDefect(Request $request, Logbook $logbook)
    {
        $validated = $request->validate([
            'defect_category_id' => 'required|exists:defect_categories,id',
            'qty_defect'         => 'required|integer|min:1',
            'tingkat_keparahan'  => 'required|in:minor,major,critical',
            'deskripsi'          => 'required|string|max:500',
            'foto_bukti'         => 'nullable|image|max:5120', // max 5MB
        ]);

        $photoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $photoPath = $request->file('foto_bukti')->store('defects', 'public');
        }

        $defect = Defect::create([
            'logbook_id'         => $logbook->id,
            'defect_category_id' => $validated['defect_category_id'],
            'qty_defect'         => $validated['qty_defect'],
            'tingkat_keparahan'  => $validated['tingkat_keparahan'],
            'deskripsi'          => $validated['deskripsi'],
            'foto_bukti'         => $photoPath,
        ]);

        // If defects are recorded, logbook needs MRB review
        if ($logbook->status === 'accept') {
            $logbook->update(['status' => 'menunggu_penetapan']);
        }

        AuditTrail::log(
            'RECORD_DEFECT',
            "Defect on Logbook #{$logbook->id}",
            null,
            "Kategori: {$defect->defectCategory->nama_defect}, Qty: {$defect->qty_defect}, Severity: {$defect->tingkat_keparahan}"
        );

        return back()->with('success', 'Defect finding and evidence photo successfully recorded!');
    }

    /**
     * Delete a recorded defect
     */
    public function destroyDefect(Defect $defect)
    {
        $logbookId = $defect->logbook_id;
        $defect->delete();

        AuditTrail::log(
            'DELETE_DEFECT',
            "Deleted Defect #{$defect->id} from Logbook #{$logbookId}"
        );

        return back()->with('info', 'Defect record has been removed.');
    }

    /**
     * UC-06: Jalankan Proses NEST (Not Evaluated Special Track)
     */
    public function storeNest(Request $request, Logbook $logbook)
    {
        $validated = $request->validate([
            'kronologi_masalah' => 'required|string|min:15',
            'bukti_pendukung'   => 'nullable|file|max:10240', // max 10MB
        ]);

        $docPath = null;
        if ($request->hasFile('bukti_pendukung')) {
            $docPath = $request->file('bukti_pendukung')->store('nest_evidence', 'public');
        }

        $nest = NestProcess::updateOrCreate(
            ['logbook_id' => $logbook->id],
            [
                'inspector_id'      => auth()->id(),
                'kronologi_masalah' => $validated['kronologi_masalah'],
                'bukti_pendukung'   => $docPath ?? ($logbook->nestProcess->bukti_pendukung ?? null),
                'diajukan_pada'     => now(),
            ]
        );

        // Sets status to menunggu_penetapan (pending Supervisor MRB)
        $logbook->update(['status' => 'menunggu_penetapan']);

        AuditTrail::log(
            'NEST_INITIATED',
            "NEST for Logbook #{$logbook->id} (GRN: {$logbook->receiving->no_grn_iqa})",
            'Status: ' . $logbook->getOriginal('status'),
            'Status: menunggu_penetapan (NEST Chronology submitted to Supervisor)'
        );

        return back()->with('success', 'NEST (Not Evaluated Special Track) process has been successfully submitted to IQC Supervisor for MRB disposition!');
    }
}

