<?php

use App\Http\Controllers\Admin\BebanSksJabatanController;
use App\Http\Controllers\Admin\CompensationPolicyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FungsionalController;
use App\Http\Controllers\Admin\GajiPokokScaleController;
use App\Http\Controllers\Admin\GolonganRuangController;
use App\Http\Controllers\Admin\HonorSksScaleController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\Admin\JabatanStrukturalPoinController;
use App\Http\Controllers\Admin\KehadiranController;
use App\Http\Controllers\Admin\KlasifikasiJabatanStrukturalController;
use App\Http\Controllers\Admin\LevelStrukturController;
use App\Http\Controllers\Admin\PayrollRunController;
use App\Http\Controllers\Admin\PayrollSettingsController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\PotonganGajiController;
use App\Http\Controllers\Admin\ReferensiController;
use App\Http\Controllers\Admin\StrukturalController;
use App\Http\Controllers\Admin\TunjanganFungsionalDosenScaleController;
use App\Http\Controllers\Admin\TunjanganGajiController;
use App\Http\Controllers\Admin\TunjanganJabatanKaryawanScaleController;
use App\Http\Controllers\Admin\TunjanganTransportasiScaleController;
use App\Http\Controllers\Admin\TunjanganVariabelScaleController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\Portal\SlipGajiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Login page (public)
Route::get('/', fn () => redirect()->route('login'));

// Dashboard redirect — role-based, needed by Breeze auth controllers
Route::middleware('auth')->get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole('bpsdm')) {
        return redirect()->route('bpsdm.dashboard');
    }

    return redirect()->route('portal.dashboard');
})->name('dashboard');

// ─── Admin ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Data Master
    Route::resource('jabatan', JabatanController::class)->except(['show']);
    Route::resource('struktural', StrukturalController::class)->except(['show']);
    Route::resource('fungsional', FungsionalController::class)->except(['show']);

    // Reference Tables (Rumusan Penggajian UA)
    Route::resource('golongan-ruang', GolonganRuangController::class)->except(['show']);
    Route::resource('gaji-pokok-scale', GajiPokokScaleController::class)->except(['show']);
    Route::resource('level-struktur', LevelStrukturController::class)->except(['show']);
    Route::resource('tunjangan-transportasi-scale', TunjanganTransportasiScaleController::class)->except(['show']);
    Route::resource('klasifikasi-jabatan-struktural', KlasifikasiJabatanStrukturalController::class)->except(['show']);
    Route::resource('jabatan-struktural-poin', JabatanStrukturalPoinController::class)->except(['show']);
    Route::resource('tunjangan-fungsional-dosen-scale', TunjanganFungsionalDosenScaleController::class)->except(['show']);
    Route::resource('tunjangan-jabatan-karyawan-scale', TunjanganJabatanKaryawanScaleController::class)->except(['show']);
    Route::resource('tunjangan-variabel-scale', TunjanganVariabelScaleController::class)->except(['show']);
    Route::resource('honor-sks-scale', HonorSksScaleController::class)->except(['show']);
    Route::resource('beban-sks-jabatan', BebanSksJabatanController::class)->except(['show']);

    // Consolidated Reference Pages
    Route::get('referensi/golongan-gaji-pokok', [ReferensiController::class, 'golonganGajiPokok'])->name('referensi.golongan-gaji-pokok');
    Route::get('referensi/jabatan-struktural', [ReferensiController::class, 'jabatanStruktural'])->name('referensi.jabatan-struktural');
    Route::get('referensi/tunjangan-komponen', [ReferensiController::class, 'tunjanganKomponen'])->name('referensi.tunjangan-komponen');
    Route::get('referensi/dosen-sks', [ReferensiController::class, 'dosenSks'])->name('referensi.dosen-sks');

    Route::get('payroll-settings', [PayrollSettingsController::class, 'index'])->name('payroll-settings.index');
    Route::put('payroll-settings', [PayrollSettingsController::class, 'update'])->name('payroll-settings.update');

    // Kebijakan Kompensasi (admin + bpsdm write access)
    Route::get('kebijakan-kompensasi', [CompensationPolicyController::class, 'index'])->name('kebijakan-kompensasi.index');
    Route::put('kebijakan-kompensasi/settings', [CompensationPolicyController::class, 'updateSettings'])->name('kebijakan-kompensasi.update-settings');
    Route::put('kebijakan-kompensasi/scales-jabatan', [CompensationPolicyController::class, 'updateScalesJabatan'])->name('kebijakan-kompensasi.update-scales-jabatan');
    Route::put('kebijakan-kompensasi/scales-variabel', [CompensationPolicyController::class, 'updateScalesVariabel'])->name('kebijakan-kompensasi.update-scales-variabel');
    Route::resource('pegawai', PegawaiController::class);
    Route::post('pegawai/{pegawai}/create-account', [PegawaiController::class, 'createAccount'])->name('pegawai.createAccount');
    Route::get('pegawai-export/export', [PegawaiController::class, 'export'])->name('pegawai.export');
    Route::post('pegawai-export/import', [PegawaiController::class, 'import'])->name('pegawai.import');

    // Kehadiran
    Route::get('kehadiran', [KehadiranController::class, 'index'])->name('kehadiran.index');
    Route::post('kehadiran', [KehadiranController::class, 'store'])->name('kehadiran.store');
    Route::get('kehadiran/{kehadiran}/edit', [KehadiranController::class, 'edit'])->name('kehadiran.edit');
    Route::put('kehadiran/{kehadiran}', [KehadiranController::class, 'update'])->name('kehadiran.update');
    Route::post('kehadiran/import', [KehadiranController::class, 'import'])->name('kehadiran.import');
    Route::delete('kehadiran/{kehadiran}', [KehadiranController::class, 'destroy'])->name('kehadiran.destroy');

    // Potongan Gaji
    Route::get('potongan-gaji', [PotonganGajiController::class, 'index'])->name('potongan-gaji.index');
    Route::post('potongan-gaji', [PotonganGajiController::class, 'store'])->name('potongan-gaji.store');
    Route::patch('potongan-gaji/{potongan_gaji}', [PotonganGajiController::class, 'update'])->name('potongan-gaji.update');
    Route::patch('potongan-gaji/{potongan_gaji}/toggle', [PotonganGajiController::class, 'toggle'])->name('potongan-gaji.toggle');
    Route::delete('potongan-gaji/{potongan_gaji}', [PotonganGajiController::class, 'destroy'])->name('potongan-gaji.destroy');

    // Tunjangan Gaji
    Route::get('tunjangan-gaji', [TunjanganGajiController::class, 'index'])->name('tunjangan-gaji.index');
    Route::post('tunjangan-gaji', [TunjanganGajiController::class, 'store'])->name('tunjangan-gaji.store');
    Route::patch('tunjangan-gaji/{tunjangan_gaji}', [TunjanganGajiController::class, 'update'])->name('tunjangan-gaji.update');
    Route::patch('tunjangan-gaji/{tunjangan_gaji}/toggle', [TunjanganGajiController::class, 'toggle'])->name('tunjangan-gaji.toggle');
    Route::delete('tunjangan-gaji/{tunjangan_gaji}', [TunjanganGajiController::class, 'destroy'])->name('tunjangan-gaji.destroy');

    // Payroll Run
    Route::get('payroll-run', [PayrollRunController::class, 'index'])->name('payroll-run.index');
    Route::post('payroll-run/calculate', [PayrollRunController::class, 'calculate'])->name('payroll-run.calculate');
    Route::get('payroll-run/{payroll_run}', [PayrollRunController::class, 'show'])->name('payroll-run.show');
    Route::post('payroll-run/{payroll_run}/finalize', [PayrollRunController::class, 'finalize'])->name('payroll-run.finalize');
    Route::post('payroll-run/{payroll_run}/void', [PayrollRunController::class, 'void'])->name('payroll-run.void');

    // PDF & Export
    Route::get('slip-gaji/{detail}/pdf', [PdfController::class, 'slipGaji'])->name('slip-gaji.pdf');
    Route::get('laporan-gaji/pdf', [PdfController::class, 'laporanGaji'])->name('laporan-gaji.pdf');
    Route::get('laporan-absensi/pdf', [PdfController::class, 'laporanAbsensi'])->name('laporan-absensi.pdf');
    Route::get('laporan-gaji/export', [PdfController::class, 'exportLaporanGaji'])->name('laporan-gaji.export');
    Route::get('laporan-absensi/export', [PdfController::class, 'exportLaporanAbsensi'])->name('laporan-absensi.export');
});

// ─── BPSDM ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:bpsdm'])->prefix('bpsdm')->name('bpsdm.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Data Master — read-only (same controllers, policies restrict write)
    Route::get('jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
    Route::get('struktural', [StrukturalController::class, 'index'])->name('struktural.index');
    Route::get('fungsional', [FungsionalController::class, 'index'])->name('fungsional.index');
    Route::get('pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
    Route::get('pegawai/{pegawai}', [PegawaiController::class, 'show'])->name('pegawai.show');

    // Reference Tables — read-only
    Route::get('golongan-ruang', [GolonganRuangController::class, 'index'])->name('golongan-ruang.index');
    Route::get('gaji-pokok-scale', [GajiPokokScaleController::class, 'index'])->name('gaji-pokok-scale.index');
    Route::get('level-struktur', [LevelStrukturController::class, 'index'])->name('level-struktur.index');
    Route::get('tunjangan-transportasi-scale', [TunjanganTransportasiScaleController::class, 'index'])->name('tunjangan-transportasi-scale.index');
    Route::get('klasifikasi-jabatan-struktural', [KlasifikasiJabatanStrukturalController::class, 'index'])->name('klasifikasi-jabatan-struktural.index');
    Route::get('jabatan-struktural-poin', [JabatanStrukturalPoinController::class, 'index'])->name('jabatan-struktural-poin.index');
    Route::get('tunjangan-fungsional-dosen-scale', [TunjanganFungsionalDosenScaleController::class, 'index'])->name('tunjangan-fungsional-dosen-scale.index');
    Route::get('tunjangan-jabatan-karyawan-scale', [TunjanganJabatanKaryawanScaleController::class, 'index'])->name('tunjangan-jabatan-karyawan-scale.index');
    Route::get('tunjangan-variabel-scale', [TunjanganVariabelScaleController::class, 'index'])->name('tunjangan-variabel-scale.index');
    Route::get('honor-sks-scale', [HonorSksScaleController::class, 'index'])->name('honor-sks-scale.index');
    Route::get('beban-sks-jabatan', [BebanSksJabatanController::class, 'index'])->name('beban-sks-jabatan.index');

    // Consolidated Reference Pages
    Route::get('referensi/golongan-gaji-pokok', [ReferensiController::class, 'golonganGajiPokok'])->name('referensi.golongan-gaji-pokok');
    Route::get('referensi/jabatan-struktural', [ReferensiController::class, 'jabatanStruktural'])->name('referensi.jabatan-struktural');
    Route::get('referensi/tunjangan-komponen', [ReferensiController::class, 'tunjanganKomponen'])->name('referensi.tunjangan-komponen');
    Route::get('referensi/dosen-sks', [ReferensiController::class, 'dosenSks'])->name('referensi.dosen-sks');

    // Kehadiran — read-only
    Route::get('kehadiran', [KehadiranController::class, 'index'])->name('kehadiran.index');

    // Potongan Gaji — read-only
    Route::get('potongan-gaji', [PotonganGajiController::class, 'index'])->name('potongan-gaji.index');

    // Tunjangan Gaji — read-only
    Route::get('tunjangan-gaji', [TunjanganGajiController::class, 'index'])->name('tunjangan-gaji.index');

    // Payroll Run — read-only
    Route::get('payroll-run', [PayrollRunController::class, 'index'])->name('payroll-run.index');
    Route::get('payroll-run/{payroll_run}', [PayrollRunController::class, 'show'])->name('payroll-run.show');

    // Kebijakan Kompensasi — PENGECEOALIAN: BPSDM boleh tulis di area ini saja
    Route::get('kebijakan-kompensasi', [CompensationPolicyController::class, 'index'])->name('kebijakan-kompensasi.index');
    Route::put('kebijakan-kompensasi/settings', [CompensationPolicyController::class, 'updateSettings'])->name('kebijakan-kompensasi.update-settings');
    Route::put('kebijakan-kompensasi/scales-jabatan', [CompensationPolicyController::class, 'updateScalesJabatan'])->name('kebijakan-kompensasi.update-scales-jabatan');
    Route::put('kebijakan-kompensasi/scales-variabel', [CompensationPolicyController::class, 'updateScalesVariabel'])->name('kebijakan-kompensasi.update-scales-variabel');

    // PDF & Export
    Route::get('slip-gaji/{detail}/pdf', [PdfController::class, 'slipGaji'])->name('slip-gaji.pdf');
    Route::get('laporan-gaji/pdf', [PdfController::class, 'laporanGaji'])->name('laporan-gaji.pdf');
    Route::get('laporan-absensi/pdf', [PdfController::class, 'laporanAbsensi'])->name('laporan-absensi.pdf');
    Route::get('laporan-gaji/export', [PdfController::class, 'exportLaporanGaji'])->name('laporan-gaji.export');
    Route::get('laporan-absensi/export', [PdfController::class, 'exportLaporanAbsensi'])->name('laporan-absensi.export');
});

// ─── Portal (Pegawai/Tendik) ──────────────────────────────────────────
Route::middleware(['auth', 'role:pegawai|tendik'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/slip-gaji', [SlipGajiController::class, 'index'])->name('slip.gaji');
    Route::get('/riwayat-absensi', [PortalDashboardController::class, 'riwayatAbsensi'])->name('riwayat.absensi');
    Route::get('/slip-gaji/{detail}/pdf', [PdfController::class, 'slipGaji'])->name('slip-gaji.pdf');
    Route::get('/profile', [PortalProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [PortalProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [PortalProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// ─── Profile (all authenticated users) ────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
