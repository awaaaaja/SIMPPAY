<?php

namespace Tests\Feature;

use App\Models\DosenSks;
use App\Models\HonorSks;
use App\Models\HonorSksLog;
use App\Models\Jabatan;
use App\Models\KategoriHonorSks;
use App\Models\Pegawai;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\PayrollService;
use App\Services\SksService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SksDosenTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Jabatan $jabatan;

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
            'status' => 'aktif',
        ]);
    }

    private function createDosen(string $name = 'Budi'): Pegawai
    {
        return Pegawai::create([
            'nik' => fake()->unique()->numerify('################'),
            'nama_pegawai' => $name,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-15',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'alamat' => 'Jl. Merdeka No. 1',
            'no_telp' => '08123456789',
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'status_dosen' => 'dosen',
            'tmt' => '2020-01-01',
            'masa_kerja_tahun' => 6,
            'masa_kerja_bulan' => 0,
            'pangkat' => 'III/c',
            'golongan' => 'III',
        ]);
    }

    // ─── TahunAkademik Tests ───────────────────────────────

    public function test_tahun_akademik_aktifkan_only_one_active(): void
    {
        $ta1 = TahunAkademik::create([
            'kode_tahun' => '2025/2026',
            'tahun' => '2025/2026',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2025-09-01',
            'tanggal_berakhir' => '2026-01-31',
            'aktif' => false,
        ]);

        $ta2 = TahunAkademik::create([
            'kode_tahun' => '2025/2027',
            'tahun' => '2025/2027',
            'semester' => 'genap',
            'tanggal_mulai' => '2026-02-01',
            'tanggal_berakhir' => '2026-06-30',
            'aktif' => false,
        ]);

        $ta1->aktifkan();
        $this->assertTrue($ta1->fresh()->aktif);

        $ta2->aktifkan();

        $this->assertFalse($ta1->fresh()->aktif);
        $this->assertTrue($ta2->fresh()->aktif);
    }

    public function test_tahun_akademik_aktifkan_seeds_dosen_sks(): void
    {
        $dosen = $this->createDosen('Andi Dosen');

        $ta = TahunAkademik::create([
            'kode_tahun' => '2026/2027',
            'tahun' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => false,
        ]);

        $ta->aktifkan();

        $this->assertDatabaseHas('dosen_sks', [
            'pegawai_id' => $dosen->id,
            'tahun_akademik_id' => $ta->id,
            'sks_maksimal' => 12,
            'sks_terpakai' => 0,
            'sks_beban' => 0,
        ]);
    }

    public function test_tahun_akademik_aktifkan_does_not_seed_non_dosen(): void
    {
        Pegawai::create([
            'nik' => '9999999999999999',
            'nama_pegawai' => 'Karyawan Biasa',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-15',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'alamat' => 'Jl. Merdeka',
            'no_telp' => '08123456788',
            'email' => 'karyawan@example.com',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'status_dosen' => 'bukan_dosen',
            'tmt' => '2020-01-01',
            'masa_kerja_tahun' => 6,
            'masa_kerja_bulan' => 0,
            'pangkat' => 'III/c',
            'golongan' => 'III',
        ]);

        $ta = TahunAkademik::create([
            'kode_tahun' => '2026/2028',
            'tahun' => '2026/2028',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => false,
        ]);

        $ta->aktifkan();

        $this->assertEquals(0, DosenSks::where('tahun_akademik_id', $ta->id)->count());
    }

    public function test_tahun_akademik_aktifkan_inherits_previous_sks(): void
    {
        $dosen = $this->createDosen('Citra Dosen');

        $ta1 = TahunAkademik::create([
            'kode_tahun' => '2025/2029',
            'tahun' => '2025/2029',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2025-09-01',
            'tanggal_berakhir' => '2026-01-31',
            'aktif' => false,
        ]);
        $ta1->aktifkan();

        DosenSks::where('pegawai_id', $dosen->id)
            ->where('tahun_akademik_id', $ta1->id)
            ->update(['sks_maksimal' => 16]);

        $ta2 = TahunAkademik::create([
            'kode_tahun' => '2025/2030',
            'tahun' => '2025/2030',
            'semester' => 'genap',
            'tanggal_mulai' => '2026-02-01',
            'tanggal_berakhir' => '2026-06-30',
            'aktif' => false,
        ]);
        $ta2->aktifkan();

        $this->assertDatabaseHas('dosen_sks', [
            'pegawai_id' => $dosen->id,
            'tahun_akademik_id' => $ta2->id,
            'sks_maksimal' => 16,
        ]);
    }

    public function test_tahun_akademik_aktifkan_is_idempotent(): void
    {
        $dosen = $this->createDosen('Dian Dosen');

        $ta = TahunAkademik::create([
            'kode_tahun' => '2026/2031',
            'tahun' => '2026/2031',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => false,
        ]);

        $ta->aktifkan();
        $ta->aktifkan();

        $this->assertEquals(1, DosenSks::where('pegawai_id', $dosen->id)
            ->where('tahun_akademik_id', $ta->id)
            ->count());
    }

    // ─── HonorSks + HonorSksLog Audit Trail Tests ──────────

    public function test_honor_sks_update_creates_log_entry(): void
    {
        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen Tetap']);
        $honor = HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 150000,
            'last_updated_by' => $this->admin->id,
        ]);

        $honor->updateHonor(200000, $this->admin->id);

        $this->assertDatabaseHas('honor_sks', [
            'id' => $honor->id,
            'honor' => 200000,
            'last_updated_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('honor_sks_log', [
            'honor_sks_id' => $honor->id,
            'honor_lama' => 150000,
            'honor_baru' => 200000,
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_honor_sks_multiple_updates_all_logged(): void
    {
        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen LB']);
        $honor = HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 100000,
            'last_updated_by' => $this->admin->id,
        ]);

        $honor->updateHonor(150000, $this->admin->id);
        $honor->updateHonor(200000, $this->admin->id);
        $honor->updateHonor(250000, $this->admin->id);

        $logs = HonorSksLog::where('honor_sks_id', $honor->id)->get();

        $this->assertCount(3, $logs);
        $this->assertEquals(100000, (float) $logs[0]->honor_lama);
        $this->assertEquals(150000, (float) $logs[0]->honor_baru);
        $this->assertEquals(150000, (float) $logs[1]->honor_lama);
        $this->assertEquals(200000, (float) $logs[1]->honor_baru);
        $this->assertEquals(200000, (float) $logs[2]->honor_lama);
        $this->assertEquals(250000, (float) $logs[2]->honor_baru);
    }

    public function test_honor_sks_same_value_no_log(): void
    {
        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen PG']);
        $honor = HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 150000,
            'last_updated_by' => $this->admin->id,
        ]);

        $honor->updateHonor(150000, $this->admin->id);

        $this->assertEquals(0, HonorSksLog::where('honor_sks_id', $honor->id)->count());
    }

    public function test_honor_sks_log_contains_correct_user(): void
    {
        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen PG']);
        $honor = HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 100000,
            'last_updated_by' => $this->admin->id,
        ]);

        $honor->updateHonor(180000, $this->admin->id);

        $log = HonorSksLog::where('honor_sks_id', $honor->id)->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->admin->id, $log->updated_by);
        $this->assertNotNull($log->updated_at);
    }

    // ─── SksService Tests ───────────────────────────────────

    public function test_sks_service_non_dosen_returns_zero(): void
    {
        $pegawai = Pegawai::create([
            'nik' => '8888888888888888',
            'nama_pegawai' => 'Non Dosen',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-15',
            'jenis_kelamin' => 'P',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'alamat' => 'Jl. Merdeka',
            'no_telp' => '08123456787',
            'email' => 'nondosen@example.com',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'status_dosen' => 'bukan_dosen',
            'tmt' => '2020-01-01',
            'masa_kerja_tahun' => 6,
            'masa_kerja_bulan' => 0,
            'pangkat' => 'III/c',
            'golongan' => 'III',
        ]);

        $service = new SksService;
        $result = $service->hitungHonorKelebihan($pegawai, Carbon::now());

        $this->assertEquals(0.0, $result);
    }

    public function test_sks_service_no_active_tahun_akademik_returns_zero(): void
    {
        $dosen = $this->createDosen('Rina Dosen');

        $service = new SksService;
        $result = $service->hitungHonorKelebihan($dosen, Carbon::now());

        $this->assertEquals(0.0, $result);
    }

    public function test_sks_service_no_dosen_sks_record_returns_zero(): void
    {
        $dosen = $this->createDosen('Sari Dosen');

        TahunAkademik::create([
            'kode_tahun' => '2026/2040',
            'tahun' => '2026/2040',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => true,
        ]);

        $service = new SksService;
        $result = $service->hitungHonorKelebihan($dosen, Carbon::now());

        $this->assertEquals(0.0, $result);
    }

    public function test_sks_service_no_kelebihan_returns_zero(): void
    {
        $dosen = $this->createDosen('Eka Dosen');

        $ta = TahunAkademik::create([
            'kode_tahun' => '2026/2041',
            'tahun' => '2026/2041',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => true,
        ]);

        DosenSks::create([
            'pegawai_id' => $dosen->id,
            'tahun_akademik_id' => $ta->id,
            'sks_maksimal' => 12,
            'sks_terpakai' => 10,
            'sks_beban' => 10,
        ]);

        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen Tetap']);
        HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 150000,
            'last_updated_by' => $this->admin->id,
        ]);

        $service = new SksService;
        $result = $service->hitungHonorKelebihan($dosen, Carbon::now());

        $this->assertEquals(0.0, $result);
    }

    public function test_sks_service_calculates_kelebihan_correctly(): void
    {
        $dosen = $this->createDosen('Fajar Dosen');

        $ta = TahunAkademik::create([
            'kode_tahun' => '2026/2042',
            'tahun' => '2026/2042',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => true,
        ]);

        DosenSks::create([
            'pegawai_id' => $dosen->id,
            'tahun_akademik_id' => $ta->id,
            'sks_maksimal' => 12,
            'sks_terpakai' => 15,
            'sks_beban' => 15,
        ]);

        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen Tetap']);
        HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 150000,
            'last_updated_by' => $this->admin->id,
        ]);

        $service = new SksService;
        $result = $service->hitungHonorKelebihan($dosen, Carbon::now());

        // (15 - 12) * 150,000 = 450,000
        $this->assertEquals(450000.0, $result);
    }

    // ─── PayrollService Integration Tests ───────────────────

    public function test_payroll_calculate_includes_honor_sks(): void
    {
        $dosen = $this->createDosen('Gilang Dosen');

        $ta = TahunAkademik::create([
            'kode_tahun' => '2026/2043',
            'tahun' => '2026/2043',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => true,
        ]);

        DosenSks::create([
            'pegawai_id' => $dosen->id,
            'tahun_akademik_id' => $ta->id,
            'sks_maksimal' => 12,
            'sks_terpakai' => 16,
            'sks_beban' => 16,
        ]);

        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen Tetap']);
        HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 150000,
            'last_updated_by' => $this->admin->id,
        ]);

        $periode = Carbon::create(2026, 10, 1);

        $service = new PayrollService;
        $result = $service->calculate($dosen, $periode);

        // Base: 8M + 1.5M + 1M = 10.5M
        // Honor SKS: (16 - 12) * 150,000 = 600,000
        // Total: 11,100,000
        $this->assertEquals(600000.0, $result->honorKelebihanSks);
        $this->assertEquals(11100000.0, $result->totalGaji);
    }

    public function test_payroll_calculate_non_dosen_honor_sks_zero(): void
    {
        $pegawai = Pegawai::create([
            'nik' => '7777777777777777',
            'nama_pegawai' => 'Karyawan Non Dosen',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-15',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'alamat' => 'Jl. Merdeka',
            'no_telp' => '08123456786',
            'email' => 'karyawannon@example.com',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'status_dosen' => 'bukan_dosen',
            'tmt' => '2020-01-01',
            'masa_kerja_tahun' => 6,
            'masa_kerja_bulan' => 0,
            'pangkat' => 'III/c',
            'golongan' => 'III',
        ]);

        TahunAkademik::create([
            'kode_tahun' => '2026/2044',
            'tahun' => '2026/2044',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => true,
        ]);

        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen Tetap']);
        HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 150000,
            'last_updated_by' => $this->admin->id,
        ]);

        $periode = Carbon::create(2026, 11, 1);

        $service = new PayrollService;
        $result = $service->calculate($pegawai, $periode);

        $this->assertEquals(0.0, $result->honorKelebihanSks);
        $this->assertEquals(10500000.0, $result->totalGaji);
    }

    public function test_payroll_calculate_exact_at_max_sks_returns_zero(): void
    {
        $dosen = $this->createDosen('Hani Dosen');

        $ta = TahunAkademik::create([
            'kode_tahun' => '2026/2045',
            'tahun' => '2026/2045',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => true,
        ]);

        DosenSks::create([
            'pegawai_id' => $dosen->id,
            'tahun_akademik_id' => $ta->id,
            'sks_maksimal' => 12,
            'sks_terpakai' => 12,
            'sks_beban' => 12,
        ]);

        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen Tetap']);
        HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 150000,
            'last_updated_by' => $this->admin->id,
        ]);

        $periode = Carbon::create(2026, 12, 1);

        $service = new PayrollService;
        $result = $service->calculate($dosen, $periode);

        $this->assertEquals(0.0, $result->honorKelebihanSks);
        $this->assertEquals(10500000.0, $result->totalGaji);
    }

    public function test_payroll_calculate_breakdown_includes_sks_detail(): void
    {
        $dosen = $this->createDosen('Indra Dosen');

        $ta = TahunAkademik::create([
            'kode_tahun' => '2026/2046',
            'tahun' => '2026/2046',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2027-01-31',
            'aktif' => true,
        ]);

        DosenSks::create([
            'pegawai_id' => $dosen->id,
            'tahun_akademik_id' => $ta->id,
            'sks_maksimal' => 12,
            'sks_terpakai' => 18,
            'sks_beban' => 18,
        ]);

        $kategori = KategoriHonorSks::create(['nama_kategori' => 'Dosen Tetap']);
        HonorSks::create([
            'kategori_honor_sks_id' => $kategori->id,
            'honor' => 200000,
            'last_updated_by' => $this->admin->id,
        ]);

        $periode = Carbon::create(2027, 1, 1);

        $service = new PayrollService;
        $result = $service->calculate($dosen, $periode);

        // (18 - 12) * 200,000 = 1,200,000
        $this->assertEquals(1200000.0, $result->honorKelebihanSks);

        $breakdown = $result->toBreakdownJson();
        $this->assertArrayHasKey('honor_sks', $breakdown);
        $this->assertEquals(18, $breakdown['honor_sks']['sks_terpakai']);
        $this->assertEquals(12, $breakdown['honor_sks']['sks_maksimal']);
        $this->assertEquals(6, $breakdown['honor_sks']['kelebihan']);
        $this->assertEquals(200000.0, $breakdown['honor_sks']['honor_per_sks']);
        $this->assertEquals(1200000.0, $breakdown['honor_sks']['total']);
    }
}
