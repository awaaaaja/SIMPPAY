<?php

namespace Tests\Feature;

use App\DTOs\PayrollUaCalculationResult;
use App\Models\GajiPokokScale;
use App\Models\GolonganRuang;
use App\Models\JabatanStrukturalPoin;
use App\Models\Kehadiran;
use App\Models\KlasifikasiJabatanStruktural;
use App\Models\LevelStruktur;
use App\Models\Pegawai;
use App\Models\PegawaiAnak;
use App\Models\TunjanganFungsionalDosenScale;
use App\Models\TunjanganJabatanKaryawanScale;
use App\Models\TunjanganTransportasiScale;
use App\Models\TunjanganVariabelScale;
use App\Services\Payroll\PayrollUaFormulaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollUaFormulaAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private PayrollUaFormulaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PayrollUaFormulaService;
        $this->seedReferenceData();
    }

    private function seedReferenceData(): void
    {
        // Level Struktur
        $levels = [
            'Strategis A' => 1, 'Strategis B' => 2, 'Strategis C' => 3,
            'Operasional A' => 4, 'Operasional B' => 5, 'Operasional C' => 6, 'Operasional D' => 7,
        ];
        foreach ($levels as $kode => $urutan) {
            LevelStruktur::firstOrCreate(['kode' => $kode], ['nama' => $kode, 'urutan' => $urutan]);
        }

        // Klasifikasi (§10b)
        $klasData = [
            ['klasifikasi' => 1, 'poin_min' => 200, 'poin_max' => 400, 'level_jabatan' => 'Sekprod/Kaprod', 'tunjangan_min' => 1050000, 'tunjangan_max' => 1500000],
            ['klasifikasi' => 2, 'poin_min' => 401, 'poin_max' => 500, 'level_jabatan' => 'Kabid/Kabag', 'tunjangan_min' => 1600000, 'tunjangan_max' => 1700000],
            ['klasifikasi' => 3, 'poin_min' => 501, 'poin_max' => 600, 'level_jabatan' => 'Sekretaris Lembaga', 'tunjangan_min' => 1750000, 'tunjangan_max' => 1800000],
            ['klasifikasi' => 4, 'poin_min' => 601, 'poin_max' => 700, 'level_jabatan' => 'Kepala UPT', 'tunjangan_min' => 1900000, 'tunjangan_max' => 2000000],
            ['klasifikasi' => 5, 'poin_min' => 701, 'poin_max' => 800, 'level_jabatan' => 'Kabiro/KaLembaga', 'tunjangan_min' => 2100000, 'tunjangan_max' => 2500000],
            ['klasifikasi' => 6, 'poin_min' => 801, 'poin_max' => 1000, 'level_jabatan' => 'Pimpinan', 'tunjangan_min' => 7500000, 'tunjangan_max' => 8250000],
        ];
        foreach ($klasData as $k) {
            KlasifikasiJabatanStruktural::firstOrCreate(['klasifikasi' => $k['klasifikasi']], $k);
        }

        // Golongan Ruang
        $golData = [
            ['kode' => 'I/a', 'nama' => 'Juru Muda', 'urutan' => 1],
            ['kode' => 'I/b', 'nama' => 'Juru Muda Tingkat I', 'urutan' => 2],
            ['kode' => 'I/c', 'nama' => 'Juru', 'urutan' => 3],
            ['kode' => 'I/d', 'nama' => 'Juru Tingkat I', 'urutan' => 4],
            ['kode' => 'II/a', 'nama' => 'Pengatur Muda', 'urutan' => 5],
            ['kode' => 'II/b', 'nama' => 'Pengatur Muda Tingkat I', 'urutan' => 6],
            ['kode' => 'II/c', 'nama' => 'Pengatur', 'urutan' => 7],
            ['kode' => 'II/d', 'nama' => 'Pengatur Tingkat I', 'urutan' => 8],
            ['kode' => 'III/a', 'nama' => 'Penata Muda', 'urutan' => 9],
            ['kode' => 'III/b', 'nama' => 'Penata Muda Tingkat I', 'urutan' => 10],
            ['kode' => 'III/c', 'nama' => 'Penata', 'urutan' => 11],
            ['kode' => 'III/d', 'nama' => 'Penata Tingkat I', 'urutan' => 12],
            ['kode' => 'IV/a', 'nama' => 'Pembina', 'urutan' => 13],
            ['kode' => 'IV/b', 'nama' => 'Pembina Tingkat I', 'urutan' => 14],
            ['kode' => 'IV/c', 'nama' => 'Pembina Utama Muda', 'urutan' => 15],
            ['kode' => 'IV/d', 'nama' => 'Pembina Utama Madya', 'urutan' => 16],
            ['kode' => 'IV/e', 'nama' => 'Pembina Utama', 'urutan' => 17],
        ];
        foreach ($golData as $g) {
            GolonganRuang::firstOrCreate(['kode' => $g['kode']], $g);
        }

        // Gaji Pokok Scale (§4) — only the rows we need for test cases
        $gajiRows = [
            // III/b MKG 0,2,4,...,14,16
            ['kode' => 'III/b', 'mkg' => 0, 'nominal' => 1500000],
            ['kode' => 'III/b', 'mkg' => 2, 'nominal' => 1560000],
            ['kode' => 'III/b', 'mkg' => 4, 'nominal' => 1620000],
            ['kode' => 'III/b', 'mkg' => 6, 'nominal' => 1680000],
            ['kode' => 'III/b', 'mkg' => 8, 'nominal' => 1740000],
            ['kode' => 'III/b', 'mkg' => 10, 'nominal' => 1800000],
            ['kode' => 'III/b', 'mkg' => 12, 'nominal' => 1860000],
            ['kode' => 'III/b', 'mkg' => 14, 'nominal' => 1920000],
            ['kode' => 'III/b', 'mkg' => 16, 'nominal' => 1980000],
        ];
        foreach ($gajiRows as $row) {
            $gol = GolonganRuang::where('kode', $row['kode'])->first();
            GajiPokokScale::firstOrCreate(
                ['golongan_ruang_id' => $gol->id, 'mkg' => $row['mkg']],
                ['nominal' => $row['nominal']]
            );
        }

        // Tunjangan Jabatan Karyawan (§8) — NOT seeded: XLSX Aug 2025 shows
        // "Tunj.jab/fungsional" = 0 for tendik, §8 not yet applied

        // Tunjangan Variabel (§9)
        $tjVariabel = [
            'I/a' => 50000, 'I/b' => 100000, 'I/c' => 150000, 'I/d' => 200000,
            'II/a' => 250000, 'II/b' => 300000, 'II/c' => 350000, 'II/d' => 400000,
            'III/a' => 450000, 'III/b' => 500000, 'III/c' => 550000, 'III/d' => 600000,
            'IV/a' => 800000, 'IV/b' => 950000, 'IV/c' => 1100000, 'IV/d' => 1250000, 'IV/e' => 1400000,
        ];
        foreach ($tjVariabel as $kode => $nominal) {
            $gol = GolonganRuang::where('kode', $kode)->first();
            TunjanganVariabelScale::firstOrCreate(
                ['golongan_ruang_id' => $gol->id],
                ['nominal_maksimum' => $nominal]
            );
        }

        // Tunjangan Fungsional Dosen (§7) — III/b Asisten Ahli
        TunjanganFungsionalDosenScale::firstOrCreate(
            ['jabatan_fungsional' => 'Asisten Ahli', 'angka_kredit' => 150],
            ['pangkat' => 'Penata Muda Tk. 1', 'golongan_ruang' => 'III/b', 'nominal' => 350000]
        );

        // Level Struktur refs
        $levelOpA = LevelStruktur::where('kode', 'Operasional A')->first();
        $levelOpD = LevelStruktur::where('kode', 'Operasional D')->first();
        $klas1 = KlasifikasiJabatanStruktural::where('klasifikasi', 1)->first();

        // Jabatan Struktural Poin — Ka. UPT (for Zulfikar) and Ketua Prodi Kecil (for Thoriq)
        JabatanStrukturalPoin::firstOrCreate(
            ['nama_jabatan' => 'Kepala UPT'],
            ['level_struktur_id' => $levelOpA->id, 'total_poin' => 486, 'klasifikasi_id' => null, 'tunjangan_baru' => 1900000]
        );
        JabatanStrukturalPoin::firstOrCreate(
            ['nama_jabatan' => 'Ketua Prodi Kecil'],
            ['level_struktur_id' => $levelOpA->id, 'total_poin' => 332, 'klasifikasi_id' => $klas1->id, 'tunjangan_baru' => 1400000]
        );
        // Dosen tetap tanpa struktural (for Desi)
        JabatanStrukturalPoin::firstOrCreate(
            ['nama_jabatan' => 'Dosen tetap dan karyawan (tanpa jabatan struktural)'],
            ['level_struktur_id' => $levelOpD->id, 'total_poin' => 0, 'klasifikasi_id' => null, 'tunjangan_baru' => 0]
        );

        // Tunjangan Transportasi (§11)
        // Operasional A (structural)
        TunjanganTransportasiScale::firstOrCreate(
            ['keterangan' => 'Operasional A'],
            ['level_struktur_id' => $levelOpA->id, 'golongan_range' => null, 'nominal' => 566800]
        );
        // Operasional D — per golongan range
        TunjanganTransportasiScale::firstOrCreate(
            ['keterangan' => 'Gol IIA-IIIB'],
            ['level_struktur_id' => $levelOpD->id, 'golongan_range' => 'IIA-IIIB', 'nominal' => 390000]
        );
        TunjanganTransportasiScale::firstOrCreate(
            ['keterangan' => 'Gol IIIC-IIID'],
            ['level_struktur_id' => $levelOpD->id, 'golongan_range' => 'IIIC-IIID', 'nominal' => 442000]
        );
        TunjanganTransportasiScale::firstOrCreate(
            ['keterangan' => 'Gol IVA-IVC'],
            ['level_struktur_id' => $levelOpD->id, 'golongan_range' => 'IVA-IVC', 'nominal' => 494000]
        );
        TunjanganTransportasiScale::firstOrCreate(
            ['keterangan' => 'Gol IVD-IVE'],
            ['level_struktur_id' => $levelOpD->id, 'golongan_range' => 'IVD-IVE', 'nominal' => 546000]
        );
    }

    private function createPegawai(array $attrs, int $anakCount = 0, ?string $anakKe = null): Pegawai
    {
        $pegawai = Pegawai::create(array_merge([
            'nik' => uniqid(),
            'nama_pegawai' => 'Test',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_kawin' => 'belum_kawin',
            'alamat' => 'Jakarta',
            'status_pegawai' => 'aktif',
            'status_dosen' => 'bukan_dosen',
        ], $attrs));

        for ($i = 1; $i <= $anakCount; $i++) {
            PegawaiAnak::create([
                'pegawai_id' => $pegawai->id,
                'nama_anak' => "Anak {$i}",
                'jenis_kelamin' => 'L',
                'anak_ke' => $i,
            ]);
        }

        return $pegawai;
    }

    // ─── CASE 1: Zulfikar, S.E — Ka. UPT Perpustakaan (Tendik) ────

    public function test_zulfikar_ka_upt_perpustakaan(): void
    {
        $golIIIb = GolonganRuang::where('kode', 'III/b')->first();
        $jabatanUpt = JabatanStrukturalPoin::where('nama_jabatan', 'Kepala UPT')->first();

        $pegawai = $this->createPegawai([
            'nama_pegawai' => 'Zulfikar, S.E',
            'golongan_ruang_id' => $golIIIb->id,
            'jabatan_struktural_poin_id' => $jabatanUpt->id,
            'status_kawin' => 'kawin',
            'status_dosen' => 'bukan_dosen',
            'tmt' => Carbon::now()->subYears(14)->startOfYear(),
        ], 2);

        Kehadiran::create([
            'pegawai_id' => $pegawai->id,
            'periode' => Carbon::create(2025, 8, 1),
            'hadir' => 26, 'sakit' => 0, 'alpha' => 0,
        ]);

        $result = $this->service->calculate($pegawai, Carbon::create(2025, 8, 1));

        $this->assertInstanceOf(PayrollUaCalculationResult::class, $result);
        $this->assertEquals($pegawai->id, $result->pegawaiId);

        // §4: Gol III.b, MKG 14 → 1,920,000
        $this->assertEquals(1920000, $result->gajiPokok, 'Gaji Pokok');
        // §8: XLSX shows 0 for tendik (not yet applied)
        $this->assertEquals(0, $result->tunjanganJabatan, 'Tunjangan Jabatan');
        // §7: not dosen → 0
        $this->assertEquals(0, $result->tunjanganFungsional, 'Tunjangan Fungsional');
        // §10c: Ka. UPT → 1,900,000
        $this->assertEquals(1900000, $result->tunjanganStruktural, 'Tunjangan Struktural');
        // §9: Gol III/b → 500,000
        $this->assertEquals(500000, $result->tunjanganVariabel, 'Tunjangan Variabel');
        // §5: 10% × 1,920,000 = 192,000
        $this->assertEquals(192000, $result->tunjanganIstri, 'Tunjangan Istri');
        // §5: 2 × (2% × 1,920,000) = 76,800
        $this->assertEquals(76800, $result->tunjanganAnak, 'Tunjangan Anak');
        // §11: Operasional A → 566,800
        $this->assertEquals(566800, $result->tunjanganTransportasi, 'Tunjangan Transportasi');

        // Jumlah (gross)
        $this->assertEquals(5155600, $result->jumlah, 'Jumlah (gross)');

        // Potongan = 0 (no deduction data in test)
        $this->assertEquals(0, $result->jumlahPotongan, 'Jumlah Potongan');

        // THP
        $this->assertEquals(5155600, $result->thp, 'THP');
    }

    // ─── CASE 2: Muhammad Thoriq, M.Kom — Ka. Prodi Informatika (Dosen) ────

    public function test_muhammad_thoriq_ka_prodi_informatika(): void
    {
        $golIIIb = GolonganRuang::where('kode', 'III/b')->first();
        $jabatanProdi = JabatanStrukturalPoin::where('nama_jabatan', 'Ketua Prodi Kecil')->first();

        $pegawai = $this->createPegawai([
            'nama_pegawai' => 'Muhammad Thoriq, M.Kom',
            'golongan_ruang_id' => $golIIIb->id,
            'jabatan_struktural_poin_id' => $jabatanProdi->id,
            'status_kawin' => 'kawin',
            'status_dosen' => 'dosen',
            'tmt' => Carbon::now()->subYears(3)->startOfYear(),
        ], 3);

        Kehadiran::create([
            'pegawai_id' => $pegawai->id,
            'periode' => Carbon::create(2025, 8, 1),
            'hadir' => 26, 'sakit' => 0, 'alpha' => 0,
        ]);

        $result = $this->service->calculate($pegawai, Carbon::create(2025, 8, 1));

        // §4: Gol III.b, MKG 2 → 1,560,000
        $this->assertEquals(1560000, $result->gajiPokok, 'Gaji Pokok');
        // §8: XLSX shows fungsional for dosen, 0 for jabatan karyawan
        $this->assertEquals(0, $result->tunjanganJabatan, 'Tunjangan Jabatan');
        // §7: dosen + Gol III/b → 350,000
        $this->assertEquals(350000, $result->tunjanganFungsional, 'Tunjangan Fungsional');
        // §10c: Ketua Prodi Kecil → 1,400,000
        $this->assertEquals(1400000, $result->tunjanganStruktural, 'Tunjangan Struktural');
        // §9: Gol III/b → 500,000  (note: XLSX shows 250,000 but full table value is 500,000)
        // The XLSX may have applied partial penilaian kinerja; we use full value per §9
        $this->assertEquals(500000, $result->tunjanganVariabel, 'Tunjangan Variabel');
        // §5: 10% × 1,560,000 = 156,000
        $this->assertEquals(156000, $result->tunjanganIstri, 'Tunjangan Istri');
        // §5: 3 × (2% × 1,560,000) = 93,600
        $this->assertEquals(93600, $result->tunjanganAnak, 'Tunjangan Anak');
        // §11: Operasional A → 566,800
        $this->assertEquals(566800, $result->tunjanganTransportasi, 'Tunjangan Transportasi');

        // Jumlah (gross) = 1,560,000 + 0 + 350,000 + 1,400,000 + 500,000 + 156,000 + 93,600 + 0 + 566,800 = 4,626,400
        $this->assertEquals(4626400, $result->jumlah, 'Jumlah (gross)');

        // THP (no deductions in test)
        $this->assertEquals(4626400, $result->thp, 'THP');
    }

    // ─── CASE 3: Desi Rosalina, SM, MM — Dosen Tetap (no struktural) ────

    public function test_desi_rosalina_dosen_tanpa_struktural(): void
    {
        $golIIIb = GolonganRuang::where('kode', 'III/b')->first();
        $jabatanDosen = JabatanStrukturalPoin::where('nama_jabatan', 'LIKE', '%Dosen tetap%')->first();

        $pegawai = $this->createPegawai([
            'nama_pegawai' => 'Desi Rosalina, SM, MM',
            'golongan_ruang_id' => $golIIIb->id,
            'jabatan_struktural_poin_id' => $jabatanDosen->id,
            'status_kawin' => 'belum_kawin',
            'status_dosen' => 'dosen',
            'tmt' => Carbon::now()->subYears(2)->startOfYear(),
        ], 0);

        Kehadiran::create([
            'pegawai_id' => $pegawai->id,
            'periode' => Carbon::create(2025, 8, 1),
            'hadir' => 26, 'sakit' => 0, 'alpha' => 0,
        ]);

        $result = $this->service->calculate($pegawai, Carbon::create(2025, 8, 1));

        // §4: Gol III.b, MKG 2 → 1,560,000
        $this->assertEquals(1560000, $result->gajiPokok, 'Gaji Pokok');
        // §8: XLSX shows 0 for this combined column
        $this->assertEquals(0, $result->tunjanganJabatan, 'Tunjangan Jabatan');
        // §7: dosen + Gol III/b → 350,000
        $this->assertEquals(350000, $result->tunjanganFungsional, 'Tunjangan Fungsional');
        // §10c: Dosen tanpa struktural → 0
        $this->assertEquals(0, $result->tunjanganStruktural, 'Tunjangan Struktural');
        // §9: Gol III/b → 500,000
        $this->assertEquals(500000, $result->tunjanganVariabel, 'Tunjangan Variabel');
        // §5: not married → 0
        $this->assertEquals(0, $result->tunjanganIstri, 'Tunjangan Istri');
        // §5: no children → 0
        $this->assertEquals(0, $result->tunjanganAnak, 'Tunjangan Anak');
        // §11: non-structural + Gol III/b (IIA-IIIB) → 390,000
        $this->assertEquals(390000, $result->tunjanganTransportasi, 'Tunjangan Transportasi');

        // Jumlah (gross) = 1,560,000 + 0 + 350,000 + 0 + 500,000 + 0 + 0 + 0 + 0 + 390,000 = 2,800,000
        $this->assertEquals(2800000, $result->jumlah, 'Jumlah (gross)');

        // THP
        $this->assertEquals(2800000, $result->thp, 'THP');
    }
}
