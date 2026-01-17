<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\DashboardController;

Route::post('/login', [AuthController::class, 'login']);
// Route yang butuh Token (Harus Login)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/update-location', [TrackingController::class, 'updateLocation']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/history', [ReportController::class, 'history']); // Buat nanti
});
// Route untuk Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/get-locations', [DashboardController::class, 'getLocations'])->name('dashboard.data');

// Dashboard Utama (Peta)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/get-locations', [DashboardController::class, 'getLocations'])->name('dashboard.data');

// Menu-menu Baru
Route::get('/laporan-digital', [DashboardController::class, 'laporan'])->name('laporan');
Route::get('/checkpoint-log', [DashboardController::class, 'checkpoint'])->name('checkpoint');

// Jadwal (Tampil & Simpan)
Route::get('/jadwal-personel', [DashboardController::class, 'jadwal'])->name('jadwal');
Route::post('/jadwal-personel', [DashboardController::class, 'storeJadwal'])->name('jadwal.store');

// Instruksi (Tampil & Simpan)
Route::get('/instruksi', [DashboardController::class, 'instruksi'])->name('instruksi');
Route::post('/instruksi', [DashboardController::class, 'storeInstruksi'])->name('instruksi.store');

// TAMBAHKAN INI: Route untuk membatalkan (menghapus) instruksi
Route::delete('/instruksi/{id}', [DashboardController::class, 'destroyInstruksi'])->name('instruksi.destroy');