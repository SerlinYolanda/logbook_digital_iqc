<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\SupervisorValidationController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\DefectController;

use App\Http\Controllers\ReportController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\IncomingMaterialController;

// Public routes
Route::get('/', [AuthController::class, 'rootRedirect'])->name('root');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');

    // Inspections
    Route::get('/inspections', [InspectionController::class, 'index'])->name('inspections.index');
    Route::get('/inspections/create', [InspectionController::class, 'create'])->name('inspections.create');
    Route::post('/inspections', [InspectionController::class, 'store'])->name('inspections.store');
    Route::get('/inspections/{id}', [InspectionController::class, 'show'])->name('inspections.show');

    // Reports & Output
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

    // Instruments / Status Alat Ukur (view: all roles)
    Route::get('/instruments', [InstrumentController::class, 'index'])->name('instruments.index');

    // Incoming material (GRN) queue management (all authenticated roles)
    Route::get('/incoming-materials/scan', [IncomingMaterialController::class, 'scanGrn'])->name('incoming-materials.scan');
    Route::post('/incoming-materials', [IncomingMaterialController::class, 'store'])->name('incoming-materials.store');
    Route::delete('/incoming-materials/{incomingMaterial}', [IncomingMaterialController::class, 'destroy'])->name('incoming-materials.destroy');

    // Defects logging
    Route::post('/inspections/{id}/defects', [DefectController::class, 'store'])->name('defects.store');
    Route::delete('/defects/{id}', [DefectController::class, 'destroy'])->name('defects.destroy');

    // Poka-Yoke error page simulation
    Route::get('/poka-yoke/denied', [AuthController::class, 'pokaYokeDenied'])->name('poka-yoke.denied');

    // Supervisor ONLY: Review and approve/disposition rejected materials
    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('/supervisor/validations', [SupervisorValidationController::class, 'index'])->name('supervisor.validation');
        Route::get('/supervisor/validations/{id}', [SupervisorValidationController::class, 'show'])->name('supervisor.review');
        Route::post('/supervisor/validations/{id}/decide', [SupervisorValidationController::class, 'decide'])->name('supervisor.decide');
    });

    // Master Data & User Shift Roster: Supervisor and Admin
    Route::middleware(['role:supervisor,admin'])->group(function () {
        Route::resource('master-data', MasterDataController::class)->except(['create', 'show', 'edit']);
        Route::get('/admin/users', [\App\Http\Controllers\UserController::class, 'index'])->name('admin.users.index');
        Route::post('/inspectors/{user}/set-shift', [\App\Http\Controllers\UserController::class, 'setInspectorShift'])->name('inspectors.set-shift');

        // Instrument registry management
        Route::post('/instruments', [InstrumentController::class, 'store'])->name('instruments.store');
        Route::put('/instruments/{instrument}', [InstrumentController::class, 'update'])->name('instruments.update');
        Route::post('/instruments/{instrument}/verify', [InstrumentController::class, 'verify'])->name('instruments.verify');
        Route::delete('/instruments/{instrument}', [InstrumentController::class, 'destroy'])->name('instruments.destroy');
    });

    // Admin ONLY: Full Account Management (Create, Edit, Delete Inspector & SPV Credentials)
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/admin/users', [\App\Http\Controllers\UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('admin.users.destroy');
    });
});

