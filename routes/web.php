<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrediksiController;
use App\Http\Controllers\DashboardController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.do');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/prediksi/form',   [PrediksiController::class, 'form'])->name('prediksi.form');
    Route::post('/prediksi',       [PrediksiController::class, 'predictAndSave'])->name('prediksi.store'); // <— INI YANG KURANG
    Route::get('/prediksi/riwayat', [PrediksiController::class, 'riwayat'])->name('prediksi.riwayat');
    Route::delete('/prediksi/{id}', [PrediksiController::class, 'destroy'])->name('prediksi.destroy');
    Route::delete('/prediksi', [PrediksiController::class, 'bulkDestroy'])->name('prediksi.bulkDestroy');

    Route::get('/prediksi/import', [PrediksiController::class, 'importForm'])->name('prediksi.import.form');
    Route::post('/prediksi/import', [PrediksiController::class, 'importProcess'])->name('prediksi.import.process');
    Route::get('/prediksi/export', [PrediksiController::class, 'exportCsv'])->name('prediksi.export');
});
