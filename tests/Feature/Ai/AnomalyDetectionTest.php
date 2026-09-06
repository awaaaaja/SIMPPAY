<?php

namespace Tests\Feature\Ai;

use App\Models\Jabatan;
use App\Models\PayrollAnomaly;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use App\Models\User;
use App\Services\AnomalyDetectionService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnomalyDetectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    #[Test]
    public function test_anomaly_detected_when_deviation_exceeds_threshold(): void
    {
        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Dosen',
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'status' => 'aktif',
        ]);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
        ]);

        // Historical: 3 months at 10,500,000
        foreach ([10, 11, 12] as $month) {
            $run = PayrollRun::create([
                'periode' => Carbon::create(2025, $month, 1),
                'status' => 'finalized',
                'calculated_by' => $this->admin->id,
            ]);

            PayrollDetail::create([
                'payroll_run_id' => $run->id,
                'pegawai_id' => $pegawai->id,
                'gaji_pokok' => 8000000,
                'tj_transport' => 1500000,
                'uang_makan' => 1000000,
                'potongan_alpha' => 0,
                'total_tunjangan_tambahan' => 0,
                'total_potongan_tambahan' => 0,
                'honor_kelebihan_sks' => 0,
                'total_gaji' => 10500000,
            ]);
        }

        // Current month: 50% deviation (15,750,000 vs 10,500,000)
        $currentRun = PayrollRun::create([
            'periode' => Carbon::create(2026, 1, 1),
            'status' => 'finalized',
            'calculated_by' => $this->admin->id,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $currentRun->id,
            'pegawai_id' => $pegawai->id,
            'gaji_pokok' => 12000000,
            'tj_transport' => 2250000,
            'uang_makan' => 1500000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 15750000,
        ]);

        $service = new AnomalyDetectionService;
        $anomalies = $service->detect($currentRun);

        $this->assertCount(1, $anomalies);
        $this->assertEquals('gaji_deviasi', $anomalies[0]->tipe);
        $this->assertEquals(10500000, $anomalies[0]->nilai_sebelumnya);
        $this->assertEquals(15750000, $anomalies[0]->nilai_sekarang);
        $this->assertEqualsWithDelta(50.0, $anomalies[0]->persentase_deviasi, 0.01);
        $this->assertEquals('pending', $anomalies[0]->status_review);
    }

    #[Test]
    public function test_no_anomaly_when_deviation_below_threshold(): void
    {
        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Staff',
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'status' => 'aktif',
        ]);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
        ]);

        // Historical: 3 months at 6,500,000
        foreach ([10, 11, 12] as $month) {
            $run = PayrollRun::create([
                'periode' => Carbon::create(2025, $month, 1),
                'status' => 'finalized',
                'calculated_by' => $this->admin->id,
            ]);

            PayrollDetail::create([
                'payroll_run_id' => $run->id,
                'pegawai_id' => $pegawai->id,
                'gaji_pokok' => 5000000,
                'tj_transport' => 1000000,
                'uang_makan' => 500000,
                'potongan_alpha' => 0,
                'total_tunjangan_tambahan' => 0,
                'total_potongan_tambahan' => 0,
                'honor_kelebihan_sks' => 0,
                'total_gaji' => 6500000,
            ]);
        }

        // Current month: 10% deviation (7,150,000 vs 6,500,000)
        $currentRun = PayrollRun::create([
            'periode' => Carbon::create(2026, 1, 1),
            'status' => 'finalized',
            'calculated_by' => $this->admin->id,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $currentRun->id,
            'pegawai_id' => $pegawai->id,
            'gaji_pokok' => 5500000,
            'tj_transport' => 1100000,
            'uang_makan' => 550000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 7150000,
        ]);

        $service = new AnomalyDetectionService;
        $anomalies = $service->detect($currentRun);

        $this->assertCount(0, $anomalies);
    }

    #[Test]
    public function test_anomalies_endpoint_returns_pending(): void
    {
        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Dosen',
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'status' => 'aktif',
        ]);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
        ]);

        $run = PayrollRun::create([
            'periode' => Carbon::create(2026, 1, 1),
            'status' => 'finalized',
            'calculated_by' => $this->admin->id,
        ]);

        $detail = PayrollDetail::create([
            'payroll_run_id' => $run->id,
            'pegawai_id' => $pegawai->id,
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 10500000,
        ]);

        PayrollAnomaly::create([
            'payroll_detail_id' => $detail->id,
            'tipe' => 'gaji_deviasi',
            'nilai_sebelumnya' => 5000000,
            'nilai_sekarang' => 10500000,
            'persentase_deviasi' => 110.00,
            'catatan_ai' => null,
            'status_review' => 'pending',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/ai/anomalies');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    #[Test]
    public function test_anomalies_endpoint_requires_admin_role(): void
    {
        $pegawaibUser = User::factory()->create();
        $pegawaibUser->assignRole('pegawai');

        Sanctum::actingAs($pegawaibUser);

        $response = $this->getJson('/api/v1/ai/anomalies');

        $response->assertForbidden();
    }

    #[Test]
    public function test_configurable_threshold(): void
    {
        config(['simppay.anomaly_threshold' => 10]);

        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Staff',
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'status' => 'aktif',
        ]);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
        ]);

        // Historical: 6,500,000
        $histRun = PayrollRun::create([
            'periode' => Carbon::create(2025, 12, 1),
            'status' => 'finalized',
            'calculated_by' => $this->admin->id,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $histRun->id,
            'pegawai_id' => $pegawai->id,
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 6500000,
        ]);

        // Current: 7,200,000 (~10.77% deviation — exceeds threshold=10)
        $currentRun = PayrollRun::create([
            'periode' => Carbon::create(2026, 1, 1),
            'status' => 'finalized',
            'calculated_by' => $this->admin->id,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $currentRun->id,
            'pegawai_id' => $pegawai->id,
            'gaji_pokok' => 5600000,
            'tj_transport' => 1100000,
            'uang_makan' => 500000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 7200000,
        ]);

        $service = new AnomalyDetectionService;
        $anomalies = $service->detect($currentRun);

        $this->assertCount(1, $anomalies);
    }
}
