<?php

use App\Plugins\Kurikulum\Controllers\KurikulumController;
use App\Plugins\Kurikulum\Controllers\StrukturKurikulumController;
use App\Plugins\Kurikulum\Controllers\KomponenKompetensiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'plugin:kurikulum'])
    ->prefix('kurikulum')
    ->name('kurikulum.')
    ->group(function () {

        // --- Master Kurikulum ---
        Route::get('/',                         [KurikulumController::class, 'index'])->name('index');
        Route::get('/create',                   [KurikulumController::class, 'create'])->name('create');
        Route::post('/',                        [KurikulumController::class, 'store'])->name('store');
        Route::get('/{kurikulum}/edit',         [KurikulumController::class, 'edit'])->name('edit');
        Route::put('/{kurikulum}',              [KurikulumController::class, 'update'])->name('update');
        Route::delete('/{kurikulum}',           [KurikulumController::class, 'destroy'])->name('destroy');

        // --- Struktur Kurikulum ---
        Route::prefix('struktur')->name('struktur.')->group(function () {
            Route::get('/',                     [StrukturKurikulumController::class, 'index'])->name('index');
            Route::get('/create',               [StrukturKurikulumController::class, 'create'])->name('create');
            Route::post('/',                    [StrukturKurikulumController::class, 'store'])->name('store');
            Route::get('/{struktur}/edit',      [StrukturKurikulumController::class, 'edit'])->name('edit');
            Route::put('/{struktur}',           [StrukturKurikulumController::class, 'update'])->name('update');
            Route::delete('/{struktur}',        [StrukturKurikulumController::class, 'destroy'])->name('destroy');
        });

        // --- Komponen Kompetensi ---
        Route::prefix('komponen')->name('komponen.')->group(function () {
            Route::get('/',                     [KomponenKompetensiController::class, 'index'])->name('index');
            Route::get('/create',               [KomponenKompetensiController::class, 'create'])->name('create');
            Route::post('/',                    [KomponenKompetensiController::class, 'store'])->name('store');
            Route::get('/{komponen}/edit',      [KomponenKompetensiController::class, 'edit'])->name('edit');
            Route::put('/{komponen}',           [KomponenKompetensiController::class, 'update'])->name('update');
            Route::delete('/{komponen}',        [KomponenKompetensiController::class, 'destroy'])->name('destroy');
        });

        // --- API Endpoints (CRUDLFIX cascade/search) ---
        Route::get('/api',                      [KurikulumController::class, 'api'])->name('api');
        Route::get('/struktur/api',             [StrukturKurikulumController::class, 'api'])->name('struktur.api');
        Route::get('/komponen/api',             [KomponenKompetensiController::class, 'api'])->name('komponen.api');
    });
