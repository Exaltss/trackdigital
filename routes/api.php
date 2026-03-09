<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\DashboardController;

// --- Rute Publik ---
Route::post('/login', [AuthController::class, 'login']);

// --- Rute Terproteksi (Harus Login) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Ambil Data User Profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Ambil Jadwal Khusus Mobile (Pastikan nama ini sama dengan di Flutter)
    Route::get('/jadwal-mobile', [DashboardController::class, 'getJadwalMobile']);

    // Ambil Instruksi Terbaru (Polling)
    Route::get('/latest-instruction', [DashboardController::class, 'getLatestInstruction']);

    // Ambil Ringkasan Laporan (Counter Checkpoint)
    Route::get('/ringkasan-laporan', [DashboardController::class, 'getRingkasanLaporan']);

    // Update Lokasi Realtime
    Route::post('/tracking', [TrackingController::class, 'updateLocation']);
    
    // Rute Laporan & Checkpoint
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});