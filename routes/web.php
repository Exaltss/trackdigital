<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebAuthController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/get-locations', [DashboardController::class, 'getLocations'])->name('dashboard.data');

    // Laporan & Fitur Export PDF
    Route::get('/laporan-digital', [DashboardController::class, 'laporan'])->name('laporan');
    Route::get('/laporan-digital/export-pdf', [DashboardController::class, 'exportPdf'])->name('laporan.pdf');
    
    Route::get('/check-new-reports', [DashboardController::class, 'checkNotification'])->name('laporan.check');
    Route::get('/checkpoint-log', [DashboardController::class, 'checkpoint'])->name('checkpoint');
    Route::delete('/dashboard/laporan/{id}', [DashboardController::class, 'destroyLaporan'])->name('laporan.destroy');
    Route::get('/dashboard/laporan/update/{id}/{status}', [DashboardController::class, 'updateStatusLaporan'])->name('laporan.status');

    // Jadwal
    Route::get('/jadwal-personel', [DashboardController::class, 'jadwal'])->name('jadwal');
    Route::post('/jadwal-personel', [DashboardController::class, 'storeJadwal'])->name('jadwal.store');
    Route::delete('/dashboard/jadwal/{id}', [DashboardController::class, 'destroyJadwal'])->name('jadwal.destroy');

    // Instruksi
    Route::get('/instruksi', [DashboardController::class, 'instruksi'])->name('instruksi');
    Route::post('/instruksi', [DashboardController::class, 'storeInstruksi'])->name('instruksi.store');
    Route::delete('/dashboard/instruksi/{id}', [DashboardController::class, 'destroyInstruksi'])->name('instruksi.destroy');
    Route::get('/get-latest-instruction', [DashboardController::class, 'getLatestInstruction'])->name('instruksi.latest');
});

/*
|--------------------------------------------------------------------------
| API ROUTES (Flutter)
|--------------------------------------------------------------------------
*/
Route::post('/api/login', [ApiAuthController::class, 'login']);
Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::post('/tracking', [TrackingController::class, 'updateLocation']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
});