<?php

use App\Modules\Presence\Controllers\AbsensiController;
use App\Modules\Presence\Controllers\IzinController;
use App\Modules\Presence\Controllers\LaporanPresensiController;
use App\Modules\Presence\Controllers\PresensiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('presence')
    ->name('presence.')
    ->group(function () {
        // QR Scanner
        Route::get('/scan', [PresensiController::class, 'scan'])->name('scan');
        Route::post('/scan', [PresensiController::class, 'storeScan'])->name('scan.store');

        // Rekap Kehadiran
        Route::get('/rekap', [PresensiController::class, 'index'])
            ->middleware('throttle:60,1')
            ->name('rekap');

        // Absensi (Alpa)
        Route::prefix('absensi')->name('absensi.')->group(function () {
            Route::get('/', [AbsensiController::class, 'index'])->name('index');
            Route::get('/create', [AbsensiController::class, 'create'])->name('create');
            Route::post('/', [AbsensiController::class, 'store'])->name('store');
            Route::delete('/{absence}', [AbsensiController::class, 'destroy'])->name('destroy');
        });

        // Izin (Sakit / Keperluan)
        Route::prefix('izin')->name('izin.')->group(function () {
            Route::get('/', [IzinController::class, 'index'])->name('index');
            Route::get('/create', [IzinController::class, 'create'])->name('create');
            Route::post('/', [IzinController::class, 'store'])->name('store');
            Route::get('/{permit}', [IzinController::class, 'show'])->name('show');
            Route::post('/{permit}/approve', [IzinController::class, 'approve'])->name('approve');
            Route::post('/{permit}/reject', [IzinController::class, 'reject'])->name('reject');
            Route::delete('/{permit}', [IzinController::class, 'destroy'])->name('destroy');
        });

        // Laporan Bulanan
        Route::get('/laporan', [LaporanPresensiController::class, 'index'])->name('laporan');
    });
