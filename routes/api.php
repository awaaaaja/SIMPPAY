<?php

use App\Http\Controllers\Api\V1\AbsensiController;
use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\PegawaiController;
use App\Http\Controllers\Api\V1\PenggajianController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // ─── Pegawai (read) ───────────────────────────────────────
    Route::get('/pegawai', [PegawaiController::class, 'index']);
    Route::get('/pegawai/{pegawai}', [PegawaiController::class, 'show']);

    // ─── Pegawai (write) ──────────────────────────────────────
    Route::post('/pegawai', [PegawaiController::class, 'store'])
        ->middleware('abilities:pegawai:write');
    Route::put('/pegawai/{pegawai}', [PegawaiController::class, 'update'])
        ->middleware('abilities:pegawai:write');

    // ─── Absensi (read) ───────────────────────────────────────
    Route::get('/absensi', [AbsensiController::class, 'index']);

    // ─── Absensi (write) ──────────────────────────────────────
    Route::post('/absensi', [AbsensiController::class, 'store'])
        ->middleware('abilities:absensi:write');

    // ─── Penggajian (read) ────────────────────────────────────
    Route::get('/penggajian', [PenggajianController::class, 'index']);

    // ─── Penggajian (process) ─────────────────────────────────
    Route::post('/penggajian/process', [PenggajianController::class, 'process'])
        ->middleware('abilities:payroll:process');
    Route::get('/penggajian/jobs/{jobId}', [PenggajianController::class, 'jobStatus']);

    // ─── AI (all authenticated users) ─────────────────────────
    Route::post('/ai/payroll-query', [AiController::class, 'payrollQuery']);
    Route::post('/ai/chat', [AiController::class, 'chat']);

    // ─── AI (admin only) ──────────────────────────────────────
    Route::get('/ai/anomalies', [AiController::class, 'anomalies'])
        ->middleware('role:admin');
    Route::post('/ai/report', [AiController::class, 'report'])
        ->middleware('role:admin');
});
