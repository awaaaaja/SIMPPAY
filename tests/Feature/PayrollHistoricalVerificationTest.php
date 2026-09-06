<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\Pegawai;
use App\Models\PotonganGaji;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Historical formula verification — PRD §8 acceptance criteria:
 * "Formula gaji identik hasilnya dengan sistem lama untuk data historis
 *  (validasi: jalankan PayrollService untuk 3 periode data lama,
 *   bandingkan total dengan hasil di sistem CI3 — toleransi 0, harus sama persis)"
 *
 * Source data: Database/penggajian.sql (CI3 dump)
 * Formula source: application/views/admin/gaji/data_gaji.php lines 112-123
 *   potongan = alpha × potongan_gaji.jml_potongan (100000)
 *   total_gaji = gaji_pokok + tj_transport + uang_makan - potongan
 *
 * CI3 data_jabatan:
 *   HRD             : gaji 4,000,000 + transport 600,000 + makan 400,000 = 5,000,000
 *   Staff Marketing : gaji 2,500,000 + transport 300,000 + makan 200,000 = 3,000,000
 *   Admin           : gaji 2,200,000 + transport 300,000 + makan 200,000 = 2,700,000
 *   Sales           : gaji 2,500,000 + transport 300,000 + makan 200,000 = 3,000,000
 *
 * CI3 data_pegawai:
 *   Fauzi  (NIK 123456789)  → jabatan "Admin"
 *   Dodi   (NIK 0987654321) → jabatan "Staff Marketing"
 *
 * CI3 data_kehadiran:
 *   Dodi  Jan 2021 (012021): hadir=24, sakit=0, alpha=0
 *   Fauzi Jan 2021 (012021): hadir=22, sakit=0, alpha=1
 *
 * CI3 potongan_gaji:
 *   Alpha: jml_potongan = 100000
 *   Sakit: jml_potongan = 0
 */
class PayrollHistoricalVerificationTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PayrollService;
    }

    /**
     * Create jabatan matching CI3 data_jabatan exactly.
     */
    private function createJabatan(string $nama, int $gaji, int $transport, int $makan): Jabatan
    {
        return Jabatan::create([
            'nama_jabatan' => $nama,
            'gaji_pokok' => $gaji,
            'tj_transport' => $transport,
            'uang_makan' => $makan,
            'status' => 'aktif',
        ]);
    }

    /**
     * Create alpha penalty matching CI3 potongan_gaji (Alpha = 100000).
     */
    private function createAlphaPenalty(): void
    {
        PotonganGaji::create([
            'nama_potongan' => 'Alpha',
            'tipe' => 'nominal',
            'nilai' => 100000,
            'is_alpha_penalty' => true,
            'aktif' => true,
        ]);
    }

    /**
     * Create pegawai matching CI3 data_pegawai.
     */
    private function createPegawai(string $nik, string $nama, Jabatan $jabatan): Pegawai
    {
        return Pegawai::create([
            'nik' => $nik,
            'nama_pegawai' => $nama,
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
        ]);
    }

    /**
     * Create kehadiran matching CI3 data_kehadiran.
     */
    private function createKehadiran(Pegawai $pegawai, Carbon $periode, int $alpha): void
    {
        Kehadiran::create([
            'pegawai_id' => $pegawai->id,
            'periode' => $periode->copy()->startOfMonth(),
            'hadir' => 22,
            'sakit' => 0,
            'alpha' => $alpha,
        ]);
    }

    #[Test]
    public function test_fauzi_januari_2021_exact_match_ci3(): void
    {
        // Setup: matches CI3 dump exactly
        $this->createAlphaPenalty();

        $jabatanAdmin = $this->createJabatan('Admin', 2200000, 300000, 200000);
        $fauzi = $this->createPegawai('123456789', 'Fauzi', $jabatanAdmin);

        $periode = Carbon::create(2021, 1, 1);
        $this->createKehadiran($fauzi, $periode, 1); // alpha = 1

        // CI3 expected: 2,200,000 + 300,000 + 200,000 - (1 × 100,000) = 2,600,000
        $result = $this->service->calculate($fauzi, $periode);

        $this->assertEquals(2600000, $result->totalGaji, 'Fauzi Jan 2021: total_gaji must match CI3 exactly');
        $this->assertEquals(2200000, $result->gajiPokok);
        $this->assertEquals(300000, $result->tjTransport);
        $this->assertEquals(200000, $result->uangMakan);
        $this->assertEquals(100000, $result->potonganAlpha);
    }

    #[Test]
    public function test_dodi_januari_2021_exact_match_ci3(): void
    {
        $this->createAlphaPenalty();

        $jabatanMarketing = $this->createJabatan('Staff Marketing', 2500000, 300000, 200000);
        $dodi = $this->createPegawai('0987654321', 'Dodi', $jabatanMarketing);

        $periode = Carbon::create(2021, 1, 1);
        $this->createKehadiran($dodi, $periode, 0); // alpha = 0

        // CI3 expected: 2,500,000 + 300,000 + 200,000 - 0 = 3,000,000
        $result = $this->service->calculate($dodi, $periode);

        $this->assertEquals(3000000, $result->totalGaji, 'Dodi Jan 2021: total_gaji must match CI3 exactly');
        $this->assertEquals(2500000, $result->gajiPokok);
        $this->assertEquals(300000, $result->tjTransport);
        $this->assertEquals(200000, $result->uangMakan);
        $this->assertEquals(0, $result->potonganAlpha);
    }

    #[Test]
    public function test_febuari_2021_synthetic_no_alpha(): void
    {
        // Periode 2 — verify formula holds with different alpha count
        $this->createAlphaPenalty();

        $jabatanAdmin = $this->createJabatan('Admin', 2200000, 300000, 200000);
        $fauzi = $this->createPegawai('123456789', 'Fauzi', $jabatanAdmin);

        $periode = Carbon::create(2021, 2, 1);
        $this->createKehadiran($fauzi, $periode, 0); // no alpha

        // Expected: 2,200,000 + 300,000 + 200,000 - 0 = 2,700,000
        $result = $this->service->calculate($fauzi, $periode);

        $this->assertEquals(2700000, $result->totalGaji, 'Fauzi Feb 2021: total_gaji must match CI3 exactly');
    }

    #[Test]
    public function test_maret_2021_synthetic_multiple_alpha(): void
    {
        // Periode 3 — verify with higher alpha count
        $this->createAlphaPenalty();

        $jabatanMarketing = $this->createJabatan('Staff Marketing', 2500000, 300000, 200000);
        $dodi = $this->createPegawai('0987654321', 'Dodi', $jabatanMarketing);

        $periode = Carbon::create(2021, 3, 1);
        $this->createKehadiran($dodi, $periode, 3); // alpha = 3

        // Expected: 2,500,000 + 300,000 + 200,000 - (3 × 100,000) = 2,700,000
        $result = $this->service->calculate($dodi, $periode);

        $this->assertEquals(2700000, $result->totalGaji, 'Dodi Mar 2021: total_gaji must match CI3 exactly');
    }

    #[Test]
    public function test_hrd_jabatan_verifies_base_components(): void
    {
        // Verify HRD jabatan base components match CI3 data_jabatan
        $this->createAlphaPenalty();

        $jabatanHRD = $this->createJabatan('HRD', 4000000, 600000, 400000);
        $pegawai = $this->createPegawai('1111111111111111', 'HRD Staff', $jabatanHRD);

        $periode = Carbon::create(2021, 1, 1);
        $this->createKehadiran($pegawai, $periode, 2);

        // Expected: 4,000,000 + 600,000 + 400,000 - (2 × 100,000) = 4,800,000
        $result = $this->service->calculate($pegawai, $periode);

        $this->assertEquals(4000000, $result->gajiPokok, 'HRD gaji_pokok must match CI3');
        $this->assertEquals(600000, $result->tjTransport, 'HRD tj_transport must match CI3');
        $this->assertEquals(400000, $result->uangMakan, 'HRD uang_makan must match CI3');
        $this->assertEquals(200000, $result->potonganAlpha, 'HRD potongan_alpha must match CI3');
        $this->assertEquals(4800000, $result->totalGaji, 'HRD total_gaji must match CI3');
    }

    #[Test]
    public function test_sales_jabatan_verifies_formula(): void
    {
        $this->createAlphaPenalty();

        $jabatanSales = $this->createJabatan('Sales', 2500000, 300000, 200000);
        $pegawai = $this->createPegawai('2222222222222222', 'Sales Staff', $jabatanSales);

        $periode = Carbon::create(2021, 4, 1);
        $this->createKehadiran($pegawai, $periode, 5);

        // Expected: 2,500,000 + 300,000 + 200,000 - (5 × 100,000) = 2,500,000
        $result = $this->service->calculate($pegawai, $periode);

        $this->assertEquals(2500000, $result->totalGaji, 'Sales Apr 2021: total_gaji must match CI3 exactly');
    }
}
