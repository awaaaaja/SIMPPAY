<?php

namespace Tests\Feature;

use App\DTOs\PayrollCalculationResult;
use App\Events\PayrollFinalized;
use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use App\Models\PotonganGaji;
use App\Models\TunjanganGaji;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Jabatan $jabatan;

    private Pegawai $pegawai;

    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->jabatan = Jabatan::create([
            'nama_jabatan' => 'Dosen Tetap',
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'sks_maksimal' => 12,
            'honor_per_kategori' => 150000,
            'status' => 'aktif',
        ]);

        $this->pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi Santoso',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-15',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'alamat' => 'Jl. Merdeka No. 1',
            'no_telp' => '08123456789',
            'email' => 'budi@example.com',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'tmt' => '2020-01-01',
            'masa_kerja_tahun' => 6,
            'masa_kerja_bulan' => 0,
            'pangkat' => 'III/c',
            'golongan' => 'III',
        ]);

        $this->service = new PayrollService;
    }

    public function test_calculate_basic_no_alpha_no_tunjangan(): void
    {
        $periode = Carbon::create(2026, 1, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        $this->assertInstanceOf(PayrollCalculationResult::class, $result);
        $this->assertEquals($this->pegawai->id, $result->pegawaiId);
        $this->assertEquals(8000000, $result->gajiPokok);
        $this->assertEquals(1500000, $result->tjTransport);
        $this->assertEquals(1000000, $result->uangMakan);
        $this->assertEquals(0, $result->potonganAlpha);
        $this->assertEquals(0, $result->totalTunjanganTambahan);
        $this->assertEquals(0, $result->totalPotonganTambahan);
        $this->assertEquals(10500000, $result->totalGaji);
    }

    public function test_calculate_with_alpha(): void
    {
        PotonganGaji::create([
            'nama_potongan' => 'Alpha',
            'tipe' => 'nominal',
            'nilai' => 100000,
            'is_alpha_penalty' => true,
            'aktif' => true,
        ]);

        Kehadiran::create([
            'pegawai_id' => $this->pegawai->id,
            'periode' => Carbon::create(2026, 2, 1),
            'alpha' => 3,
        ]);

        $periode = Carbon::create(2026, 2, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        // Gaji Pokok + Tj.Transport + Uang.Makan = 10,500,000
        // Alpha: 3 × 100,000 = 300,000
        // Total Dasar: 10,500,000 - 300,000 = 10,200,000
        $this->assertEquals(300000, $result->potonganAlpha);
        $this->assertEquals(10200000, $result->totalGaji);
    }

    public function test_calculate_with_tunjangan_jabatan(): void
    {
        TunjanganGaji::create([
            'nama_tunjangan' => 'Tj. Fungsional',
            'target_tipe' => 'jabatan',
            'jabatan_id' => $this->jabatan->id,
            'nominal' => 500000,
            'aktif' => true,
        ]);

        $periode = Carbon::create(2026, 3, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        // Total Dasar: 10,500,000
        // Tunjangan: 500,000
        // Total: 11,000,000
        $this->assertEquals(500000, $result->totalTunjanganTambahan);
        $this->assertEquals(11000000, $result->totalGaji);
    }

    public function test_calculate_with_tunjangan_semua(): void
    {
        TunjanganGaji::create([
            'nama_tunjangan' => 'Tj. Kehadiran',
            'target_tipe' => 'semua',
            'jabatan_id' => null,
            'nominal' => 300000,
            'aktif' => true,
        ]);

        $periode = Carbon::create(2026, 4, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        $this->assertEquals(300000, $result->totalTunjanganTambahan);
        $this->assertEquals(10800000, $result->totalGaji);
    }

    public function test_calculate_with_potongan_tambahan(): void
    {
        PotonganGaji::create([
            'nama_potongan' => 'Iuran Pensiun',
            'tipe' => 'persentase',
            'nilai' => 200000,
            'is_alpha_penalty' => false,
            'aktif' => true,
        ]);

        PotonganGaji::create([
            'nama_potongan' => 'Iuran JKK',
            'tipe' => 'persentase',
            'nilai' => 100000,
            'is_alpha_penalty' => false,
            'aktif' => true,
        ]);

        $periode = Carbon::create(2026, 5, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        // Total Dasar: 10,500,000
        // Potongan Tambahan: 200,000 + 100,000 = 300,000
        // Total: 10,200,000
        $this->assertEquals(300000, $result->totalPotonganTambahan);
        $this->assertEquals(10200000, $result->totalGaji);
    }

    public function test_calculate_full_scenario(): void
    {
        // Alpha penalty
        PotonganGaji::create([
            'nama_potongan' => 'Alpha',
            'tipe' => 'nominal',
            'nilai' => 100000,
            'is_alpha_penalty' => true,
            'aktif' => true,
        ]);

        // Kehadiran: 2 alpha
        Kehadiran::create([
            'pegawai_id' => $this->pegawai->id,
            'periode' => Carbon::create(2026, 6, 1),
            'alpha' => 2,
        ]);

        // Tunjangan
        TunjanganGaji::create([
            'nama_tunjangan' => 'Tj. Fungsional',
            'target_tipe' => 'jabatan',
            'jabatan_id' => $this->jabatan->id,
            'nominal' => 500000,
            'aktif' => true,
        ]);

        TunjanganGaji::create([
            'nama_tunjangan' => 'Tj. Kehadiran',
            'target_tipe' => 'semua',
            'jabatan_id' => null,
            'nominal' => 300000,
            'aktif' => true,
        ]);

        // Potongan
        PotonganGaji::create([
            'nama_potongan' => 'Iuran Pensiun',
            'tipe' => 'persentase',
            'nilai' => 200000,
            'is_alpha_penalty' => false,
            'aktif' => true,
        ]);

        $periode = Carbon::create(2026, 6, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        // Gaji Pokok: 8,000,000
        // Tj.Transport: 1,500,000
        // Uang Makan: 1,000,000
        // Potongan Alpha: 2 × 100,000 = 200,000
        // Total Dasar: 10,500,000 - 200,000 = 10,300,000
        // Tunjangan: 500,000 + 300,000 = 800,000
        // Potongan Tambahan: 200,000
        // Total: 10,300,000 + 800,000 - 200,000 = 10,900,000

        $this->assertEquals(200000, $result->potonganAlpha);
        $this->assertEquals(800000, $result->totalTunjanganTambahan);
        $this->assertEquals(200000, $result->totalPotonganTambahan);
        $this->assertEquals(10900000, $result->totalGaji);
    }

    public function test_calculate_no_kehadiran_record(): void
    {
        $periode = Carbon::create(2026, 7, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        // No kehadiran → alpha = 0
        $this->assertEquals(0, $result->potonganAlpha);
        $this->assertEquals(10500000, $result->totalGaji);
    }

    public function test_calculate_inactive_tunjangan_excluded(): void
    {
        TunjanganGaji::create([
            'nama_tunjangan' => 'Tj. Aktif',
            'target_tipe' => 'semua',
            'jabatan_id' => null,
            'nominal' => 300000,
            'aktif' => true,
        ]);

        TunjanganGaji::create([
            'nama_tunjangan' => 'Tj. Nonaktif',
            'target_tipe' => 'semua',
            'jabatan_id' => null,
            'nominal' => 500000,
            'aktif' => false,
        ]);

        $periode = Carbon::create(2026, 8, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        $this->assertEquals(300000, $result->totalTunjanganTambahan);
        $this->assertEquals(10800000, $result->totalGaji);
    }

    public function test_calculate_inactive_potongan_excluded(): void
    {
        PotonganGaji::create([
            'nama_potongan' => 'Aktif',
            'tipe' => 'nominal',
            'nilai' => 100000,
            'is_alpha_penalty' => false,
            'aktif' => true,
        ]);

        PotonganGaji::create([
            'nama_potongan' => 'Nonaktif',
            'tipe' => 'nominal',
            'nilai' => 200000,
            'is_alpha_penalty' => false,
            'aktif' => false,
        ]);

        $periode = Carbon::create(2026, 9, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        $this->assertEquals(100000, $result->totalPotonganTambahan);
        $this->assertEquals(10400000, $result->totalGaji);
    }

    public function test_calculate_negative_total_clamped_to_zero(): void
    {
        // Massive potongan that exceeds gaji
        PotonganGaji::create([
            'nama_potongan' => 'Alpha',
            'tipe' => 'nominal',
            'nilai' => 10000000, // 10M × 1 alpha = 10M
            'is_alpha_penalty' => true,
            'aktif' => true,
        ]);

        Kehadiran::create([
            'pegawai_id' => $this->pegawai->id,
            'periode' => Carbon::create(2026, 10, 1),
            'alpha' => 10, // 10 × 10M = 100M >> gaji
        ]);

        $periode = Carbon::create(2026, 10, 1);

        $result = $this->service->calculate($this->pegawai, $periode);

        // Total would be negative → clamped to 0
        $this->assertEquals(0, $result->totalGaji);
    }

    // ─── calculateBatch Tests ──────────────────────────────────

    public function test_calculate_batch_creates_run_and_details(): void
    {
        $periode = Carbon::create(2026, 1, 1);

        $run = $this->service->calculateBatch($periode, $this->admin);

        $this->assertInstanceOf(PayrollRun::class, $run);
        $this->assertEquals('calculated', $run->status);
        $this->assertEquals($this->admin->id, $run->calculated_by);
        $this->assertNotNull($run->calculated_at);
        $this->assertEquals($periode->format('Y-m-d'), $run->periode->format('Y-m-d'));

        $this->assertDatabaseHas('payroll_details', [
            'payroll_run_id' => $run->id,
            'pegawai_id' => $this->pegawai->id,
            'total_gaji' => 10500000,
        ]);
    }

    public function test_calculate_batch_idempotent_replaces_existing(): void
    {
        $periode = Carbon::create(2026, 3, 1);

        $first = $this->service->calculateBatch($periode, $this->admin);
        $this->assertEquals('calculated', $first->status);
        $firstId = $first->id;

        $second = $this->service->calculateBatch($periode, $this->admin);

        $this->assertEquals($firstId, $second->id); // same run, replaced
        $this->assertEquals('calculated', $second->status);
        $this->assertEquals(1, PayrollDetail::where('payroll_run_id', $second->id)->count());
    }

    public function test_calculate_batch_does_not_touch_finalized(): void
    {
        $periode = Carbon::create(2026, 4, 1);

        $finalized = $this->service->calculateBatch($periode, $this->admin);
        $this->service->finalize($finalized, $this->admin);

        $this->expectException(\RuntimeException::class);
        $this->service->calculateBatch($periode, $this->admin);
    }

    // ─── finalize Tests ────────────────────────────────────────

    public function test_finalize_sets_status_and_emits_event(): void
    {
        Event::fake([PayrollFinalized::class]);

        $run = $this->service->calculateBatch(Carbon::create(2026, 6, 1), $this->admin);

        $finalized = $this->service->finalize($run, $this->admin);

        $this->assertEquals('finalized', $finalized->status);
        $this->assertEquals($this->admin->id, $finalized->finalized_by);
        $this->assertNotNull($finalized->finalized_at);

        Event::assertDispatched(PayrollFinalized::class, function ($event) use ($finalized) {
            return $event->payrollRun->id === $finalized->id;
        });
    }

    public function test_finalize_only_calculated_status(): void
    {
        $run = $this->service->calculateBatch(Carbon::create(2026, 7, 1), $this->admin);
        $this->service->finalize($run, $this->admin);

        $this->expectException(\RuntimeException::class);
        $this->service->finalize($run->fresh(), $this->admin);
    }

    // ─── void Tests ────────────────────────────────────────────

    public function test_void_sets_status_with_reason(): void
    {
        $run = $this->service->calculateBatch(Carbon::create(2026, 8, 1), $this->admin);
        $this->service->finalize($run, $this->admin);

        $voided = $this->service->void($run->fresh(), 'Error perhitungan', $this->admin);

        $this->assertEquals('void', $voided->status);
        $this->assertEquals('Error perhitungan', $voided->void_reason);
    }

    public function test_void_requires_reason(): void
    {
        $run = $this->service->calculateBatch(Carbon::create(2026, 9, 1), $this->admin);
        $this->service->finalize($run, $this->admin);

        $this->expectException(\RuntimeException::class);
        $this->service->void($run->fresh(), '', $this->admin);
    }

    public function test_void_only_finalized_status(): void
    {
        $run = $this->service->calculateBatch(Carbon::create(2026, 10, 1), $this->admin);

        $this->expectException(\RuntimeException::class);
        $this->service->void($run, 'test', $this->admin);
    }
}
