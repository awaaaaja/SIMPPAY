<?php

namespace Tests\Feature;

use App\Models\GajiPokokScale;
use App\Models\GolonganRuang;
use App\Models\PayrollSetting;
use App\Models\PayrollSettingLog;
use App\Models\Pegawai;
use App\Models\TunjanganVariabelScale;
use App\Models\User;
use App\Services\Payroll\PayrollUaFormulaService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RoleSeeder)->run();
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function createBpsdm(): User
    {
        $user = User::factory()->create();
        $user->assignRole('bpsdm');

        return $user;
    }

    private function seedGolonganAndScale(): void
    {
        $gol = GolonganRuang::firstOrCreate(
            ['kode' => 'III/b'],
            ['nama' => 'Penata Muda Tingkat I', 'urutan' => 10]
        );

        GajiPokokScale::firstOrCreate(
            ['golongan_ruang_id' => $gol->id, 'mkg' => 14],
            ['nominal' => 1920000]
        );

        TunjanganVariabelScale::firstOrCreate(
            ['golongan_ruang_id' => $gol->id],
            ['nominal_maksimum' => 500000]
        );
    }

    // ─── TEST 1: Changing 1 field produces 1 log ──────────

    public function test_changing_one_field_creates_one_log(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin);

        // Ensure singleton exists
        $settings = PayrollSetting::instance();
        $this->assertNull($settings->tunjangan_makan_per_hari, 'Default should be NULL');

        $response = $this->putJson(route('admin.payroll-settings.update'), [
            'tunjangan_makan_per_hari' => 25000,
            'lembur_basis_gaji_dasar' => 'gaji_pokok_saja',
            'lembur_jam_kerja_sebulan' => 120,
            'tunjangan_variabel_default_percentage' => 100,
            'bpjs_kesehatan_aktif' => true,
            'bpjs_tk_aktif' => false,
        ]);

        $response->assertRedirect();

        $settings->refresh();
        $this->assertEquals(25000, (float) $settings->tunjangan_makan_per_hari);
        $this->assertTrue($settings->is_confirmed_tunjangan_makan);

        // Should have exactly 1 log entry for this field
        $log = PayrollSettingLog::where('field_name', 'tunjangan_makan_per_hari')->first();
        $this->assertNotNull($log);
        $this->assertNull($log->nilai_lama);
        $this->assertEquals(25000, (float) $log->nilai_baru);
        $this->assertEquals($admin->id, $log->updated_by);
    }

    // ─── TEST 2: NULL/default shows "belum dikonfirmasi" ──

    public function test_null_default_fields_are_not_confirmed(): void
    {
        $settings = PayrollSetting::instance();

        // tunjangan_makan_per_hari is NULL → not confirmed
        $this->assertNull($settings->tunjangan_makan_per_hari);
        $this->assertFalse($settings->is_confirmed_tunjangan_makan);

        // bpjs_tk_aktif is false → not confirmed
        $this->assertFalse($settings->bpjs_tk_aktif);
        $this->assertFalse($settings->is_confirmed_bpjs_tk);

        // tunjangan_variabel_default_percentage is 100 (default) → not confirmed
        $this->assertEquals(100, (float) $settings->tunjangan_variabel_default_percentage);
        $this->assertFalse($settings->is_confirmed_tunjangan_variabel);

        // lembur_basis_gaji_dasar is default → not confirmed
        $this->assertEquals('gaji_pokok_saja', $settings->lembur_basis_gaji_dasar);
        $this->assertFalse($settings->is_confirmed_lembur_basis);

        // lembur_jam_kerja_sebulan is 120 → IS confirmed (it's a known value from docs)
        $this->assertEquals(120, $settings->lembur_jam_kerja_sebulan);
        $this->assertTrue($settings->is_confirmed_lembur_jam);

        // bpjs_kesehatan_aktif is true → IS confirmed
        $this->assertTrue($settings->bpjs_kesehatan_aktif);
        $this->assertTrue($settings->is_confirmed_bpjs_kes);
    }

    // ─── TEST 3: Authorization — non-admin denied ─────────

    public function test_non_admin_cannot_access_settings(): void
    {
        $bpsdm = $this->createBpsdm();

        $this->actingAs($bpsdm);

        $response = $this->get(route('admin.payroll-settings.index'));
        $response->assertForbidden();

        $response = $this->putJson(route('admin.payroll-settings.update'), [
            'tunjangan_makan_per_hari' => 10000,
            'lembur_basis_gaji_dasar' => 'gaji_pokok_saja',
            'lembur_jam_kerja_sebulan' => 120,
            'tunjangan_variabel_default_percentage' => 100,
            'bpjs_kesehatan_aktif' => true,
            'bpjs_tk_aktif' => false,
        ]);
        $response->assertForbidden();
    }

    // ─── TEST 4: Service reads from settings, not cache ──

    public function test_service_reads_from_settings_after_update(): void
    {
        $this->seedGolonganAndScale();

        $gol = GolonganRuang::where('kode', 'III/b')->first();
        $peg = Pegawai::create([
            'nik' => uniqid(),
            'nama_pegawai' => 'Test',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_kawin' => 'belum_kawin',
            'alamat' => 'Jakarta',
            'status_pegawai' => 'aktif',
            'status_dosen' => 'bukan_dosen',
            'golongan_ruang_id' => $gol->id,
            'tmt' => Carbon::now()->subYears(14)->startOfYear(),
        ]);

        $periode = Carbon::create(2025, 8, 1);
        $service = new PayrollUaFormulaService;

        // Default: tunjangan_makan_per_hari = NULL → 0
        $result1 = $service->calculate($peg, $periode);
        $this->assertEquals(0, $result1->tunjanganMakan, 'Default NULL → 0');

        // Update settings
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $this->putJson(route('admin.payroll-settings.update'), [
            'tunjangan_makan_per_hari' => 30000,
            'lembur_basis_gaji_dasar' => 'gaji_pokok_saja',
            'lembur_jam_kerja_sebulan' => 120,
            'tunjangan_variabel_default_percentage' => 100,
            'bpjs_kesehatan_aktif' => true,
            'bpjs_tk_aktif' => false,
        ]);

        // Service should read new value
        PayrollSetting::clearCache();
        $result2 = $service->calculate($peg, $periode);
        // 30000 × 0 kehadiran (no kehadiran record) = 0
        // But if we had kehadiran, it would be 30000 × kehadiran
        // Let's add kehadiran to make it meaningful
        \App\Models\Kehadiran::create([
            'pegawai_id' => $peg->id,
            'periode' => $periode,
            'hadir' => 26,
            'sakit' => 0,
            'alpha' => 0,
        ]);

        PayrollSetting::clearCache();
        $result3 = $service->calculate($peg, $periode);
        $this->assertEquals(780000, $result3->tunjanganMakan, '30000 × 26 = 780,000');

        // Now change tunjangan_variabel to 50%
        $this->putJson(route('admin.payroll-settings.update'), [
            'tunjangan_makan_per_hari' => 30000,
            'lembur_basis_gaji_dasar' => 'gaji_pokok_saja',
            'lembur_jam_kerja_sebulan' => 120,
            'tunjangan_variabel_default_percentage' => 50,
            'bpjs_kesehatan_aktif' => true,
            'bpjs_tk_aktif' => false,
        ]);

        PayrollSetting::clearCache();
        $result4 = $service->calculate($peg, $periode);
        // Tunjangan variabel for III/b = 500,000 × 50% = 250,000
        $this->assertEquals(250000, $result4->tunjanganVariabel, '500,000 × 50% = 250,000');
    }
}
