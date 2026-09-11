<?php

namespace Tests\Feature;

use App\Models\GolonganRuang;
use App\Models\PayrollSetting;
use App\Models\TunjanganJabatanKaryawanScale;
use App\Models\TunjanganVariabelScale;
use App\Models\User;
use App\Services\Payroll\PayrollUaFormulaService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompensationPolicyTest extends TestCase
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

    private function seedCompensationData(): array
    {
        $gol = GolonganRuang::firstOrCreate(
            ['kode' => 'III/b'],
            ['nama' => 'Penata Muda Tingkat I', 'urutan' => 10]
        );

        $tjKaryawan = TunjanganJabatanKaryawanScale::firstOrCreate(
            ['golongan_ruang_id' => $gol->id],
            ['nominal' => 1750000]
        );

        $tjVariabel = TunjanganVariabelScale::firstOrCreate(
            ['golongan_ruang_id' => $gol->id],
            ['nominal_maksimum' => 500000]
        );

        return compact('gol', 'tjKaryawan', 'tjVariabel');
    }

    // ─── TEST 1: BPSDM CAN update compensation tables ─────

    public function test_bpsdm_can_update_payroll_settings(): void
    {
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        PayrollSetting::instance(); // seed singleton

        $response = $this->putJson(route('bpsdm.kebijakan-kompensasi.update-settings'), [
            'tunjangan_makan_per_hari' => 35000,
            'lembur_basis_gaji_dasar' => 'gaji_pokok_saja',
            'lembur_jam_kerja_sebulan' => 120,
            'tunjangan_variabel_default_percentage' => 80,
            'bpjs_kesehatan_aktif' => true,
            'bpjs_tk_aktif' => false,
        ]);

        $response->assertRedirect();

        $settings = PayrollSetting::instance();
        PayrollSetting::clearCache();
        $fresh = PayrollSetting::instance();
        $this->assertEquals(35000, (float) $fresh->tunjangan_makan_per_hari);
        $this->assertEquals(80, (float) $fresh->tunjangan_variabel_default_percentage);
    }

    public function test_bpsdm_can_update_scales_jabatan(): void
    {
        $data = $this->seedCompensationData();
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        $response = $this->putJson(route('bpsdm.kebijakan-kompensasi.update-scales-jabatan'), [
            'scales' => [['id' => $data['tjKaryawan']->id, 'nominal' => 2000000]],
        ]);

        $response->assertRedirect();
        $this->assertEquals(2000000, (float) $data['tjKaryawan']->fresh()->nominal);
    }

    public function test_bpsdm_can_update_scales_variabel(): void
    {
        $data = $this->seedCompensationData();
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        $response = $this->putJson(route('bpsdm.kebijakan-kompensasi.update-scales-variabel'), [
            'scales' => [['id' => $data['tjVariabel']->id, 'nominal_maksimum' => 600000]],
        ]);

        $response->assertRedirect();
        $this->assertEquals(600000, (float) $data['tjVariabel']->fresh()->nominal_maksimum);
    }

    // ─── TEST 2: BPSDM STILL DENIED at all other modules ──

    public function test_bpsdm_cannot_update_pegawai(): void
    {
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        $pegawai = \App\Models\Pegawai::firstOrCreate(
            ['nik' => '99999'],
            [
                'nama_pegawai' => 'Test',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'status_kawin' => 'belum_kawin',
                'alamat' => 'Jakarta',
                'status_pegawai' => 'aktif',
                'status_dosen' => 'bukan_dosen',
            ]
        );

        // BPSDM has no PUT route for pegawai — hitting admin route should 403
        $response = $this->putJson(route('admin.pegawai.update', $pegawai->id), [
            'nik' => '99999',
            'nama_pegawai' => 'Changed',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_kawin' => 'belum_kawin',
            'alamat' => 'Jakarta',
            'status_pegawai' => 'aktif',
            'status_dosen' => 'bukan_dosen',
        ]);

        $response->assertForbidden();
    }

    public function test_bpsdm_cannot_store_jabatan(): void
    {
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        // BPSDM has no POST route for jabatan — hitting admin route should 403
        $response = $this->postJson(route('admin.jabatan.store'), [
            'nama_jabatan' => 'Test Jabatan',
            'gaji_pokok' => 5000000,
            'tj_transport' => 500000,
            'uang_makan' => 300000,
        ]);

        $response->assertForbidden();
    }

    public function test_bpsdm_cannot_update_potongan_gaji(): void
    {
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        $potongan = \App\Models\PotonganGaji::firstOrCreate(
            ['nama_potongan' => 'Test Potongan'],
            ['tipe' => 'nominal', 'nilai' => 100000, 'aktif' => true]
        );

        // BPSDM has no PATCH route for potongan-gaji — hitting admin route should 403
        $response = $this->patchJson(route('admin.potongan-gaji.update', $potongan->id), [
            'nama_potongan' => 'Test Potongan',
            'tipe' => 'nominal',
            'nilai' => 200000,
            'aktif' => true,
        ]);

        $response->assertForbidden();
    }

    public function test_bpsdm_cannot_create_payroll_run(): void
    {
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        // BPSDM has no POST route for payroll-run.calculate — hitting admin route should 403
        $response = $this->postJson(route('admin.payroll-run.calculate'), [
            'periode' => '2025-09',
        ]);

        $response->assertForbidden();
    }

    // ─── TEST 3: Audit log records role ────────────────────

    public function test_audit_log_records_role_on_settings_change(): void
    {
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        PayrollSetting::instance();

        $this->putJson(route('bpsdm.kebijakan-kompensasi.update-settings'), [
            'tunjangan_makan_per_hari' => 40000,
            'lembur_basis_gaji_dasar' => 'gaji_pokok_saja',
            'lembur_jam_kerja_sebulan' => 120,
            'tunjangan_variabel_default_percentage' => 100,
            'bpjs_kesehatan_aktif' => true,
            'bpjs_tk_aktif' => false,
        ]);

        $log = \App\Models\PayrollSettingLog::where('field_name', 'tunjangan_makan_per_hari')->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('bpsdm', $log->role);
        $this->assertEquals($bpsdm->id, $log->updated_by);
    }

    public function test_audit_log_records_role_on_scale_change(): void
    {
        $data = $this->seedCompensationData();
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->putJson(route('admin.kebijakan-kompensasi.update-scales-jabatan'), [
            'scales' => [['id' => $data['tjKaryawan']->id, 'nominal' => 2500000]],
        ]);

        $log = \App\Models\TunjanganJabatanKaryawanScaleLog::latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('admin', $log->role);
        $this->assertEquals(1750000, (float) $log->nominal_lama);
        $this->assertEquals(2500000, (float) $log->nominal_baru);
    }

    public function test_audit_log_records_bpsdm_role_on_scale_change(): void
    {
        $data = $this->seedCompensationData();
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        $this->putJson(route('bpsdm.kebijakan-kompensasi.update-scales-variabel'), [
            'scales' => [['id' => $data['tjVariabel']->id, 'nominal_maksimum' => 700000]],
        ]);

        $log = \App\Models\TunjanganVariabelScaleLog::latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('bpsdm', $log->role);
        $this->assertEquals(500000, (float) $log->nominal_lama);
        $this->assertEquals(700000, (float) $log->nominal_baru);
    }

    // ─── TEST 4: Service reads fresh values after BPSDM update ──

    public function test_service_reads_fresh_values_after_bpsdm_update(): void
    {
        $data = $this->seedCompensationData();
        $bpsdm = $this->createBpsdm();
        $this->actingAs($bpsdm);

        // Seed gaji_pokok_scale for the golongan
        \App\Models\GajiPokokScale::firstOrCreate(
            ['golongan_ruang_id' => $data['gol']->id, 'mkg' => 14],
            ['nominal' => 1920000]
        );

        $peg = \App\Models\Pegawai::create([
            'nik' => uniqid(),
            'nama_pegawai' => 'Test',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_kawin' => 'belum_kawin',
            'alamat' => 'Jakarta',
            'status_pegawai' => 'aktif',
            'status_dosen' => 'bukan_dosen',
            'golongan_ruang_id' => $data['gol']->id,
            'tmt' => Carbon::now()->subYears(14)->startOfYear(),
        ]);

        $periode = Carbon::create(2025, 8, 1);
        $service = new PayrollUaFormulaService;

        // Before BPSDM update: default values
        PayrollSetting::clearCache();
        $result1 = $service->calculate($peg, $periode);
        // tj_karyawan for III/b = 1,750,000
        $this->assertEquals(1750000, $result1->tunjanganJabatan);
        // tj_variabel for III/b = 500,000
        $this->assertEquals(500000, $result1->tunjanganVariabel);

        // BPSDM changes values
        $this->putJson(route('bpsdm.kebijakan-kompensasi.update-scales-jabatan'), [
            'scales' => [['id' => $data['tjKaryawan']->id, 'nominal' => 2200000]],
        ]);
        $this->putJson(route('bpsdm.kebijakan-kompensasi.update-scales-variabel'), [
            'scales' => [['id' => $data['tjVariabel']->id, 'nominal_maksimum' => 800000]],
        ]);

        // After BPSDM update: service reads new values
        PayrollSetting::clearCache();
        $result2 = $service->calculate($peg, $periode);
        $this->assertEquals(2200000, $result2->tunjanganJabatan, 'Service should read BPSDM-updated tj_karyawan value');
        $this->assertEquals(800000, $result2->tunjanganVariabel, 'Service should read BPSDM-updated tj_variabel value');
    }
}
