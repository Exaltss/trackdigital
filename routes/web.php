<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\DashboardController;

// --- API ROUTES (Mobile) ---
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/update-location', [TrackingController::class, 'updateLocation']);
    Route::post('/reports', [ReportController::class, 'store']);
});

// --- DASHBOARD ADMIN ROUTES ---
// Dashboard Utama
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/get-locations', [DashboardController::class, 'getLocations'])->name('dashboard.data');

// Menu Lainnya
Route::get('/laporan-digital', [DashboardController::class, 'laporan'])->name('laporan');
Route::get('/checkpoint-log', [DashboardController::class, 'checkpoint'])->name('checkpoint');

// Jadwal
Route::get('/jadwal-personel', [DashboardController::class, 'jadwal'])->name('jadwal');
Route::post('/jadwal-personel', [DashboardController::class, 'storeJadwal'])->name('jadwal.store');

// Instruksi
Route::get('/instruksi', [DashboardController::class, 'instruksi'])->name('instruksi');
Route::post('/instruksi', [DashboardController::class, 'storeInstruksi'])->name('instruksi.store');
Route::delete('/instruksi/{id}', [DashboardController::class, 'destroyInstruksi'])->name('instruksi.destroy');

// [BARU] API Notifikasi Realtime (Simulasi)
Route::get('/get-latest-instruction', [DashboardController::class, 'getLatestInstruction'])->name('instruksi.latest');