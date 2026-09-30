<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaintenanceReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/reports');

/* ---------- Staff login ---------- */
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/* ---------- Staff only (must be logged in) ---------- */
Route::middleware('auth')->group(function () {
    // Keep summary above the public {report} route so "summary" is not read as an id
    Route::get('reports/summary', [MaintenanceReportController::class, 'summary'])->name('reports.summary');
    Route::patch('reports/{report}/status', [MaintenanceReportController::class, 'updateStatus'])->name('reports.status');
    Route::resource('reports', MaintenanceReportController::class)->only(['edit', 'update', 'destroy']);
});

/* ---------- Everyone (students, teachers, parents) ---------- */
Route::resource('reports', MaintenanceReportController::class)->only(['index', 'create', 'store', 'show']);
