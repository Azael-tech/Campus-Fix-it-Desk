<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaintenanceReportController;
use App\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/reports');

/* ---------- Log in and sign up ---------- */
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    // Forgot password
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/* ---------- Maintenance staff only ---------- */
Route::middleware(['auth', EnsureStaff::class])->group(function () {
    // Keep summary above the public {report} route so "summary" is not read as an id
    Route::get('reports/summary', [MaintenanceReportController::class, 'summary'])->name('reports.summary');
    Route::patch('reports/{report}/status', [MaintenanceReportController::class, 'updateStatus'])->name('reports.status');
    Route::resource('reports', MaintenanceReportController::class)->only(['edit', 'update', 'destroy']);
});

/* ---------- Everyone (students, teachers, parents) ---------- */
Route::resource('reports', MaintenanceReportController::class)->only(['index', 'create', 'store', 'show']);
