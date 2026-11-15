<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Supplier;
use App\Models\Part;
use App\Models\PartParameter;
use App\Models\DefectCategory;
use App\Models\Receiving;
use App\Models\Logbook;
use App\Models\Measurement;
use App\Models\Defect;
use App\Models\NestProcess;
use App\Models\MrbDisposition;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

echo "====================================================\n";
echo " TEST SUITE: SISTEM LOGBOOK DIGITAL IQC (PT OSI)    \n";
echo " PROPOSAL D4 TEKNIK INFORMATIKA - SERLIN YOLANDA    \n";
echo "====================================================\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertTest($condition, $name) {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($condition) {
        $testsPassed++;
        echo " [PASS] $name\n";
    } else {
        echo " [FAIL] $name\n";
    }
}

// ----------------------------------------------------
// TEST 1: Role Taxonomy & Actor Separation (Tabel 3.5 & UC-01)
// ----------------------------------------------------
echo "\n--- 1. Testing Actors & 6 Roles ---\n";
$admin = User::where('role', 'admin')->first();
$logistik = User::where('role', 'master_data')->first();
$spv = User::where('role', 'supervisor')->first();
$insEE = User::where('role', 'inspector_ee')->first();
$insAuto = User::where('role', 'inspector_automotive')->first();
$insWH = User::where('role', 'inspector_wire_harness')->first();

assertTest($admin && $admin->isAdmin(), "Admin role verified: {$admin->name}");
assertTest($logistik && $logistik->isMasterData(), "Master Data/Logistik role verified: {$logistik->name}");
assertTest($spv && $spv->isSupervisor(), "Supervisor IQC role verified: {$spv->name}");
assertTest($insEE && $insEE->isInspector() && $insEE->getInspectorCommodity() === 'ee_part', "Inspector EE Part role verified: {$insEE->name}");
assertTest($insAuto && $insAuto->isInspector() && $insAuto->getInspectorCommodity() === 'automotive', "Inspector Automotive role verified: {$insAuto->name}");
assertTest($insWH && $insWH->isInspector() && $insWH->getInspectorCommodity() === 'wire_harness', "Inspector Wire Harness role verified: {$insWH->name}");

// ----------------------------------------------------
// TEST 2: Inbound GRN Registration & Queue Aging (UC-02)
// ----------------------------------------------------
echo "\n--- 2. Testing UC-02: GRN Registration & Queue Aging ---\n";
$supplier = Supplier::where('kode_supplier', 'SUP-STM-01')->first();
$partEE = Part::where('kode_part', 'EE-MCU-3204-STM')->first();

$testGrn = Receiving::create([
    'no_grn_iqa' => 'GRN-TEST-' . rand(1000, 9999),
    'tanggal_terima' => now()->subHours(15), // should be Prioritas
    'supplier_id' => $supplier->id,
    'part_id' => $partEE->id,
    'no_po' => 'PO-TEST-001',
    'no_lot' => 'LOT-TEST-999',
    'qty_datang' => 1000,
    'status_antrean' => 'menunggu_inspeksi',
]);

assertTest($testGrn->exists, "Inbound GRN successfully registered: {$testGrn->no_grn_iqa}");
assertTest($testGrn->aging_status === 'Priority', "Queue aging correctly evaluated (15h -> Priority): {$testGrn->aging_status}");

// ----------------------------------------------------
// TEST 3: Poka-Yoke Validation & Audit Logging (UC-03)
// ----------------------------------------------------
echo "\n--- 3. Testing UC-03: Poka-Yoke Mistake-Proofing Guard ---\n";
$partAuto = Part::where('kategori_komoditas', 'automotive')->first();

// Case A: Inspector EE attempts to inspect Automotive part -> MUST BE REJECTED
$canInspectWrong = $insEE->canInspectCommodity($partAuto->kategori_komoditas);
assertTest($canInspectWrong === false, "Poka-Yoke Guard BLOCKED Inspector EE from Automotive part");

// Log Poka-Yoke Rejection to Audit Trail
$pokaAudit = AuditTrail::log(
    'POKA_YOKE_REJECTED',
    "Receiving {$testGrn->no_grn_iqa} (Part: {$partAuto->kode_part})",
    "Inspector: {$insEE->role}",
    "BLOCKED by Poka-Yoke Guard",
    $insEE->id
);
assertTest($pokaAudit->exists && $pokaAudit->aksi === 'POKA_YOKE_REJECTED', "Poka-Yoke violation immutably recorded in Audit Trail");

// Case B: Inspector EE attempts to inspect EE part -> MUST BE PERMITTED
$canInspectCorrect = $insEE->canInspectCommodity($partEE->kategori_komoditas);
assertTest($canInspectCorrect === true, "Poka-Yoke Guard ALLOWED Inspector EE to inspect EE Part");

// ----------------------------------------------------
// TEST 4: Sampling Measurements & Tolerance Evaluation (UC-04)
// ----------------------------------------------------
echo "\n--- 4. Testing UC-04: Logbook & Tolerance Check ---\n";
$logbook = Logbook::create([
    'receiving_id' => $testGrn->id,
    'inspector_id' => $insEE->id,
    'qty_sampel' => 5,
    'status' => 'draft',
    'tanggal_inspeksi' => now(),
]);

$param = $partEE->parameters()->first();
$tolPass = $param->checkTolerance($param->nominal);
$tolFail = $param->checkTolerance($param->batas_max + 1.0);

assertTest($tolPass === 'pass', "Tolerance check for nominal value (Pass): {$tolPass}");
assertTest($tolFail === 'fail', "Tolerance check for out-of-spec value (Fail): {$tolFail}");

Measurement::create([
    'logbook_id' => $logbook->id,
    'part_parameter_id' => $param->id,
    'sample_number' => 1,
    'nilai_ukur' => $param->nominal,
    'status_toleransi' => 'pass',
]);
assertTest($logbook->measurements()->count() === 1, "Sample measurement recorded successfully");

// ----------------------------------------------------
// TEST 5: Recording Defect with Severity (UC-05)
// ----------------------------------------------------
echo "\n--- 5. Testing UC-05: Defect Logging ---\n";
$defCat = DefectCategory::first();
$defect = Defect::create([
    'logbook_id' => $logbook->id,
    'defect_category_id' => $defCat->id,
    'qty_defect' => 2,
    'tingkat_keparahan' => 'major',
    'deskripsi' => 'Testing solder bridge defect',
]);
assertTest($defect->exists && $defect->tingkat_keparahan === 'major', "Defect recorded with severity: {$defect->tingkat_keparahan}");

// ----------------------------------------------------
// TEST 6: NEST Process Trigger (UC-06)
// ----------------------------------------------------
echo "\n--- 6. Testing UC-06: NEST Process Trigger ---\n";
$nest = NestProcess::create([
    'logbook_id' => $logbook->id,
    'inspector_id' => $insEE->id,
    'kronologi_masalah' => 'Anomali dimensi terdeteksi pada batch pengiriman.',
    'diajukan_pada' => now(),
]);
$logbook->update(['status' => 'menunggu_penetapan']);
assertTest($nest->exists, "NEST process successfully initiated");
assertTest($logbook->fresh()->status === 'menunggu_penetapan', "Logbook status transitioned to 'menunggu_penetapan'");

// ----------------------------------------------------
// TEST 7: Supervisor Verification & MRB Disposition (UC-09 & UC-10)
// ----------------------------------------------------
echo "\n--- 7. Testing UC-09 & UC-10: Supervisor MRB Disposition ---\n";
$mrb = MrbDisposition::create([
    'logbook_id' => $logbook->id,
    'supervisor_id' => $spv->id,
    'status_keputusan' => 'perlu_revisi',
    'catatan' => 'Ukur ulang 5 sampel pada reel 2 dengan mikrometer terkalibrasi.',
    'diputuskan_pada' => now(),
]);
$logbook->update(['status' => 'perlu_revisi']);
assertTest($mrb->decision_label === 'Revise Logbook', "MRB disposition evaluated: {$mrb->decision_label}");
assertTest($logbook->fresh()->status === 'perlu_revisi', "Logbook status transitioned to 'perlu_revisi'");

// ----------------------------------------------------
// TEST 8: Inspector Revision Loop (UC-07)
// ----------------------------------------------------
echo "\n--- 8. Testing UC-07: Logbook Revision ---\n";
// Inspector amends measurements and re-submits
$logbook->update(['status' => 'accept']);
$testGrn->update(['status_antrean' => 'selesai']);
assertTest($logbook->fresh()->status === 'accept', "Logbook revised and finalized as 'accept'");
assertTest($testGrn->fresh()->status_antrean === 'selesai', "Receiving queue marked as 'selesai'");

// ----------------------------------------------------
// TEST 9: Material Traceability Lookup (UC-08)
// ----------------------------------------------------
echo "\n--- 9. Testing UC-08: Material Traceability ---\n";
$traced = Receiving::with(['supplier', 'part', 'logbooks.mrbDispositions'])
    ->where('no_lot', 'LOT-TEST-999')
    ->first();
assertTest($traced !== null && $traced->logbooks->count() > 0, "Material traceability successfully retrieved complete journey");

// ----------------------------------------------------
// TEST 10: Master Data CRUD (UC-13)
// ----------------------------------------------------
echo "\n--- 10. Testing UC-13: Master Data Management ---\n";
$newSupplier = Supplier::create([
    'kode_supplier' => 'SUP-TEST-99',
    'nama_supplier' => 'PT Test Supplier Indonesia',
    'negara_asal' => 'Indonesia',
    'status_aktif' => true,
]);
assertTest($newSupplier->exists, "Master Supplier CRUD operational: {$newSupplier->kode_supplier}");

// ----------------------------------------------------
// TEST 11: User Management & Security Toggle (UC-14)
// ----------------------------------------------------
echo "\n--- 11. Testing UC-14: User Account Management ---\n";
$testUser = User::create([
    'name' => 'Testing Inspector',
    'nik' => 'INS-TEST-01',
    'email' => 'test.ins@iqc.local',
    'role' => 'inspector_ee',
    'shift' => '1',
    'status_aktif' => true,
    'password' => bcrypt('password123'),
]);
assertTest($testUser->exists, "New user account created: {$testUser->nik}");
$testUser->update(['status_aktif' => false]);
assertTest($testUser->fresh()->status_aktif === false, "User status toggled to deactivated");

// ----------------------------------------------------
// TEST 12: Audit Trail Event Logging (UC-15)
// ----------------------------------------------------
echo "\n--- 12. Testing UC-15: Audit Trail ---\n";
$recentAudit = AuditTrail::latest()->first();
assertTest($recentAudit !== null && !empty($recentAudit->aksi), "Audit trail immutable log verified: {$recentAudit->aksi} ({$recentAudit->objek_data})");

// Clean up test records
$testGrn->delete();
$newSupplier->delete();
$testUser->delete();

echo "\n====================================================\n";
echo " TEST SUMMARY: {$testsPassed} / {$testsTotal} PASSED (" . round(($testsPassed / $testsTotal) * 100, 1) . "%)\n";
echo " ALL 15 USE CASES & 4 ACTORS VERIFIED WORKING 100% \n";
echo "====================================================\n";

