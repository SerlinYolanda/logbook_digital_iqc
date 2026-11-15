<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\SupervisorMrbController;
use App\Http\Controllers\MaterialTraceabilityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\InstrumentController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// --------------------------------------------------------------------------
// Public Authentication Routes (UC-01)
// --------------------------------------------------------------------------
Route::get('/', [AuthController::class, 'rootRedirect'])->name('root');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Quick Demo Authentication Shortcuts for 6 Roles
Route::get('/login/admin', function () {
    $user = User::where('role', 'admin')->first() ?? User::where('nik', 'ADM-001')->first();
    if ($user) {
        Auth::login($user);
        session(['user_role' => $user->role]);
    }
    return redirect()->route('dashboard');
})->name('login.admin');

Route::get('/login/logistik', function () {
    $user = User::where('role', 'master_data')->first() ?? User::where('nik', 'LOG-001')->first();
    if ($user) {
        Auth::login($user);
        session(['user_role' => $user->role]);
    }
    return redirect()->route('dashboard');
})->name('login.logistik');

Route::get('/login/supervisor', function () {
    $user = User::where('role', 'supervisor')->first() ?? User::where('nik', 'SPV-001')->first();
    if ($user) {
        Auth::login($user);
        session(['user_role' => $user->role]);
    }
    return redirect()->route('dashboard');
})->name('login.supervisor');

Route::get('/login/inspector-ee', function () {
    $user = User::where('role', 'inspector_ee')->first() ?? User::where('nik', 'INS-EE-001')->first();
    if ($user) {
        Auth::login($user);
        session(['user_role' => $user->role]);
    }
    return redirect()->route('dashboard');
})->name('login.inspector.ee');

Route::get('/login/inspector-auto', function () {
    $user = User::where('role', 'inspector_automotive')->first() ?? User::where('nik', 'INS-AUTO-001')->first();
    if ($user) {
        Auth::login($user);
        session(['user_role' => $user->role]);
    }
    return redirect()->route('dashboard');
})->name('login.inspector.auto');

Route::get('/login/inspector-wh', function () {
    $user = User::where('role', 'inspector_wire_harness')->first() ?? User::where('nik', 'INS-WH-001')->first();
    if ($user) {
        Auth::login($user);
        session(['user_role' => $user->role]);
    }
    return redirect()->route('dashboard');
})->name('login.inspector.wh');

Route::get('/login/inspector', function () {
    return redirect()->route('login.inspector.ee');
})->name('login.inspector');

// --------------------------------------------------------------------------
// Authenticated Group (All Active Users)
// --------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    // UC-11: Adaptive Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');

    // UC-08: Material Traceability Portal (Inspector, Supervisor, Logistik)
    Route::get('/traceability', [MaterialTraceabilityController::class, 'index'])->name('traceability.index');
    Route::get('/traceability/{id}', [MaterialTraceabilityController::class, 'show'])->name('traceability.show');

    // Instrument Status (All authenticated roles)
    Route::get('/instruments', [InstrumentController::class, 'index'])->name('instruments.index');
    Route::post('/instruments', [InstrumentController::class, 'store'])->name('instruments.store');
    Route::put('/instruments/{instrument}', [InstrumentController::class, 'update'])->name('instruments.update');
    Route::post('/instruments/{instrument}/verify', [InstrumentController::class, 'verify'])->name('instruments.verify');
    Route::delete('/instruments/{instrument}', [InstrumentController::class, 'destroy'])->name('instruments.destroy');

    // Poka-Yoke error page simulation & SOP explanation
    Route::get('/poka-yoke/denied', [AuthController::class, 'pokaYokeDenied'])->name('poka-yoke.denied');

    // ----------------------------------------------------------------------
    // Inspector IQC Routes (UC-03 to UC-07)
    // ----------------------------------------------------------------------
    Route::middleware(['role:inspector,supervisor'])->group(function () {
        // UC-07: Logbook History
        Route::get('/logbooks', [LogbookController::class, 'index'])->name('logbooks.index');
        Route::get('/inspections', [LogbookController::class, 'index'])->name('inspections.index'); // backward compat alias

        // UC-03: Scan Lot & Poka-Yoke Validation
        Route::get('/logbooks/scan', [LogbookController::class, 'scan'])->name('logbooks.scan');
        Route::get('/incoming-materials/scan', [LogbookController::class, 'scan'])->name('incoming-materials.scan'); // alias

        // UC-04: Input Logbook Inspeksi
        Route::get('/logbooks/create', [LogbookController::class, 'create'])->name('logbooks.create');
        Route::get('/inspections/create', [LogbookController::class, 'create'])->name('inspections.create'); // alias
        Route::post('/logbooks', [LogbookController::class, 'store'])->name('logbooks.store');
        Route::get('/logbooks/{logbook}', [LogbookController::class, 'show'])->name('logbooks.show');
        Route::get('/inspections/{id}', [LogbookController::class, 'show'])->name('inspections.show'); // alias

        // UC-07: Revision of Logbook
        Route::get('/logbooks/{logbook}/edit', [LogbookController::class, 'edit'])->name('logbooks.edit');
        Route::put('/logbooks/{logbook}', [LogbookController::class, 'update'])->name('logbooks.update');
        Route::post('/logbooks/{logbook}', [LogbookController::class, 'update'])->name('logbooks.update.post');

        // UC-05: Defect Recording & Evidence Photo
        Route::post('/logbooks/{logbook}/defects', [LogbookController::class, 'storeDefect'])->name('logbooks.defects.store');
        Route::delete('/defects/{defect}', [LogbookController::class, 'destroyDefect'])->name('defects.destroy');

        // UC-06: NEST Process Trigger
        Route::post('/logbooks/{logbook}/nest', [LogbookController::class, 'storeNest'])->name('logbooks.nest.store');
    });

    // ----------------------------------------------------------------------
    // Supervisor IQC Routes (UC-09, UC-10, UC-12)
    // ----------------------------------------------------------------------
    Route::middleware(['role:supervisor'])->group(function () {
        // UC-09 & UC-10: Verification & MRB Disposition
        Route::get('/supervisor/mrb', [SupervisorMrbController::class, 'index'])->name('supervisor.mrb.index');
        Route::get('/supervisor/mrb/{logbook}', [SupervisorMrbController::class, 'show'])->name('supervisor.mrb.show');
        Route::post('/supervisor/mrb/{logbook}/decide', [SupervisorMrbController::class, 'decide'])->name('supervisor.mrb.decide');

        // Aliases for backward compatibility
        Route::get('/supervisor/validation', [SupervisorMrbController::class, 'index'])->name('supervisor.validation');
        Route::get('/supervisor/validations', [SupervisorMrbController::class, 'index']);
        Route::get('/supervisor/validations/{id}', [SupervisorMrbController::class, 'show'])->name('supervisor.review');
        Route::post('/supervisor/validations/{id}/decide', [SupervisorMrbController::class, 'decide'])->name('supervisor.decide');

        // UC-12: Recapitulation Reports (PDF & Excel)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'exportExcel'])->name('reports.export');
    });

    // ----------------------------------------------------------------------
    // Master Data / Logistik Routes (UC-02, UC-13)
    // ----------------------------------------------------------------------
    Route::middleware(['role:master_data,admin'])->group(function () {
        // UC-02: Receiving / Material Arrival & Queue
        Route::get('/receivings', [ReceivingController::class, 'index'])->name('receivings.index');
        Route::post('/receivings', [ReceivingController::class, 'store'])->name('receivings.store');
        Route::delete('/receivings/{receiving}', [ReceivingController::class, 'destroy'])->name('receivings.destroy');

        // UC-13: Master Data CRUD
        Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
        Route::post('/master-data/suppliers', [MasterDataController::class, 'storeSupplier'])->name('master-data.suppliers.store');
        Route::put('/master-data/suppliers/{supplier}', [MasterDataController::class, 'updateSupplier'])->name('master-data.suppliers.update');
        Route::post('/master-data/parts', [MasterDataController::class, 'storePart'])->name('master-data.parts.store');
        Route::put('/master-data/parts/{part}', [MasterDataController::class, 'updatePart'])->name('master-data.parts.update');
        Route::post('/master-data/parts/{part}/parameters', [MasterDataController::class, 'storeParameter'])->name('master-data.parameters.store');
        Route::delete('/master-data/parameters/{parameter}', [MasterDataController::class, 'destroyParameter'])->name('master-data.parameters.destroy');
        Route::post('/master-data/defect-categories', [MasterDataController::class, 'storeDefectCategory'])->name('master-data.defects.store');
        Route::put('/master-data/defect-categories/{category}', [MasterDataController::class, 'updateDefectCategory'])->name('master-data.defects.update');
    });

    // ----------------------------------------------------------------------
    // Admin Sistem Routes (UC-14, UC-15)
    // ----------------------------------------------------------------------
    Route::middleware(['role:admin'])->group(function () {
        // UC-14: User Accounts Management
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::post('/admin/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');

        // UC-15: Immutable Audit Trail Viewer
        Route::get('/admin/audit-trails', [AuditTrailController::class, 'index'])->name('admin.audit-trails.index');
    });
});
