<?php

use App\Modules\Academic\Controllers\SiswaController;
use App\Modules\Academic\Controllers\GuruController;
use App\Modules\Academic\Controllers\KelasController;
use App\Modules\Academic\Controllers\MapelController;
use App\Modules\Academic\Controllers\MapelJenisController;
use App\Modules\Academic\Controllers\TahunAjaranController;
use App\Modules\Academic\Controllers\SemesterController;
use App\Modules\Academic\Controllers\OrangTuaController;
use App\Modules\Academic\Controllers\KelasSiswaController;
use App\Modules\Academic\Controllers\JadwalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('academic')->name('academic.')->group(function () {
    Route::resource('siswa', SiswaController::class);
    Route::resource('guru', GuruController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('mapel', MapelController::class);
    Route::resource('mapel-jenis', MapelJenisController::class);
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    Route::resource('semester', SemesterController::class);
    Route::resource('orang-tua', OrangTuaController::class);
    Route::resource('kelas-siswa', KelasSiswaController::class);
    Route::resource('jadwal', JadwalController::class);

    // Livewire test route for Kelas (pilot migration)
    Route::get('kelas-livewire', function () {
        $gurus = \App\Modules\Academic\Models\Guru::where('aktif', true)->orderBy('nama')->get();
        $branches = \App\Modules\Tenancy\Models\Branch::orderBy('nama')->get();

        return view('academic.kelas.index-livewire', compact('gurus', 'branches'));
    })->name('kelas.livewire');

    // CRUDLFIX API endpoints
    Route::get('api/jadwal', [JadwalController::class, 'api'])->name('jadwal.api');
});
