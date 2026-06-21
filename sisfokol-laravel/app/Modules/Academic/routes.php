<?php

use App\Modules\Academic\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('academic')->name('academic.')->group(function () {
    Route::resource('siswa', SiswaController::class);
});
