<?php

namespace App\Services\Payroll;

use App\DTOs\PayrollUaCalculationResult;
use App\Models\BebanSksJabatan;
use App\Models\GajiPokokScale;
use App\Models\GolonganRuang;
use App\Models\HonorSksScale;
use App\Models\Kehadiran;
use App\Models\PayrollSetting;
use App\Models\Pegawai;
use App\Models\TunjanganFungsionalDosenScale;
use App\Models\TunjanganJabatanKaryawanScale;
use App\Models\TunjanganTransportasiScale;
use App\Models\TunjanganVariabelScale;
use Carbon\Carbon;

final class PayrollUaFormulaService
{
    public function calculate(Pegawai $pegawai, Carbon $periode, float $actualSksTaught = 0.0): PayrollUaCalculationResult
    {
        $settings = PayrollSetting::instance();
        $golongan = $pegawai->golonganRuang;
        $mkg = $this->hitungMkg($pegawai);
        $kehadiran = $this->getKehadiran($pegawai, $periode);
        $anakCount = $this->hitungAnak($pegawai);

        $gajiPokok = $this->getGajiPokok($golongan, $mkg);
        $tunjanganJabatan = $this->getTunjanganJabatan($golongan);
        $tunjanganFungsional = $this->getTunjanganFungsional($pegawai, $golongan);
        $tunjanganStruktural = $this->getTunjanganStruktural($pegawai);
        $tunjanganVariabel = $this->getTunjanganVariabel($golongan, $settings);
        $tunjanganIstri = $this->getTunjanganIstri($pegawai, $gajiPokok);
        $tunjanganAnak = $this->getTunjanganAnak($pegawai, $anakCount);
        $tunjanganMakan = $this->getTunjanganMakan($settings, $kehadiran);
        $bpjsTkIncome = 0.0;
        $tunjanganTransportasi = $this->getTunjanganTransportasi($pegawai);
        $penyesuaian = 0.0;
        $lembur = 0.0;
        $honorKelebihanSks = $this->getHonorKelebihanSks($pegawai, $actualSksTaught);
        $rapel = 0.0;

        $jumlah = $gajiPokok + $tunjanganJabatan + $tunjanganFungsional
            + $tunjanganStruktural + $tunjanganVariabel + $tunjanganIstri
            + $tunjanganAnak + $tunjanganMakan + $bpjsTkIncome
            + $tunjanganTransportasi + $penyesuaian + $lembur
            + $honorKelebihanSks + $rapel;

        // ─── Potongan (§6) ─────────────────────────────────────
        // All potongan calculated as percentage from payroll_settings
        $potonganMakan = 0.0; // Deducted from tunjangan makan, not a separate potongan

        // BPJS Kesehatan: X% from gaji pokok
        $potonganBpjs = $gajiPokok * ((float) $settings->potongan_bpjs_kes_pct / 100);

        // BPJS TK: combined % from total jumlah (income before deductions)
        $bpjsTkTotalPct = (float) $settings->potongan_bpjs_tk_jkk_pct
            + (float) $settings->potongan_bpjs_tk_jkm_pct
            + (float) $settings->potongan_bpjs_tk_jht_pct
            + (float) $settings->potongan_bpjs_tk_jp_pct;
        $potonganBpjsTk = $jumlah * ($bpjsTkTotalPct / 100);

        // Iuran Pendidikan Anak: X% from gaji pokok
        $potonganPendidikanAnak = $gajiPokok * ((float) $settings->potongan_pendidikan_anak_pct / 100);

        // Iuran Sosial: X% from gaji pokok
        $potonganSosial = $gajiPokok * ((float) $settings->potongan_sosial_pct / 100);

        // Other potongan: 0 (not yet configured in settings)
        $potonganUjks = 0.0;
        $potonganKkb = 0.0;
        $potonganBtnBns = 0.0;
        $potonganLainLain = 0.0;

        $jumlahPotongan = $potonganMakan + $potonganBpjs + $potonganBpjsTk
            + $potonganPendidikanAnak + $potonganSosial + $potonganUjks
            + $potonganKkb + $potonganBtnBns + $potonganLainLain;

        // ponytail: $rapel dan $lembur sudah termasuk di $jumlah, jangan ditambah lagi
        $thp = $jumlah - $jumlahPotongan;

        return new PayrollUaCalculationResult(
            pegawaiId: $pegawai->id,
            periode: $periode->format('Y-m-d'),
            gajiPokok: $gajiPokok,
            tunjanganJabatan: $tunjanganJabatan,
            tunjanganFungsional: $tunjanganFungsional,
            tunjanganStruktural: $tunjanganStruktural,
            tunjanganVariabel: $tunjanganVariabel,
            tunjanganIstri: $tunjanganIstri,
            tunjanganAnak: $tunjanganAnak,
            tunjanganMakan: $tunjanganMakan,
            bpjsTkIncome: $bpjsTkIncome,
            tunjanganTransportasi: $tunjanganTransportasi,
            penyesuaian: $penyesuaian,
            lembur: $lembur,
            honorKelebihanSks: $honorKelebihanSks,
            rapel: $rapel,
            jumlah: $jumlah,
            potonganMakan: $potonganMakan,
            potonganBpjs: $potonganBpjs,
            potonganBpjsTk: $potonganBpjsTk,
            potonganPendidikanAnak: $potonganPendidikanAnak,
            potonganSosial: $potonganSosial,
            potonganUjks: $potonganUjks,
            potonganKkb: $potonganKkb,
            potonganBtnBns: $potonganBtnBns,
            potonganLainLain: $potonganLainLain,
            jumlahPotongan: $jumlahPotongan,
            thp: $thp,
            breakdown: [
                'mkg' => $mkg,
                'kehadiran' => $kehadiran,
                'anak_count' => $anakCount,
                'golongan.kode' => $golongan?->kode,
            ],
        );
    }

    // ─── MKG ──────────────────────────────────────────────

    private function getKehadiran(Pegawai $pegawai, Carbon $periode): int
    {
        $kehadiran = Kehadiran::where('pegawai_id', $pegawai->id)
            ->where('periode', $periode->copy()->startOfMonth())
            ->first();

        return $kehadiran?->hadir ?? 0;
    }

    private function hitungMkg(Pegawai $pegawai): int
    {
        $tmt = $pegawai->tmt;
        if (! $tmt) {
            return 0;
        }

        $years = (int) $tmt->diffInYears(now());
        // Round down to nearest even (MKG steps every 2 years)
        return (int) floor($years / 2) * 2;
    }

    // ─── Gaji Pokok (§4) ──────────────────────────────────

    private function getGajiPokok(?GolonganRuang $golongan, int $mkg): float
    {
        if (! $golongan) {
            return 0.0;
        }

        $scale = GajiPokokScale::where('golongan_ruang_id', $golongan->id)
            ->where('mkg', $mkg)
            ->first();

        return $scale ? (float) $scale->nominal : 0.0;
    }

    // ─── Tunjangan Jabatan Karyawan/Tendik (§8) ──────────

    private function getTunjanganJabatan(?GolonganRuang $golongan): float
    {
        if (! $golongan) {
            return 0.0;
        }

        $scale = TunjanganJabatanKaryawanScale::where('golongan_ruang_id', $golongan->id)->first();

        return $scale ? (float) $scale->nominal : 0.0;
    }

    // ─── Tunjangan Fungsional Dosen (§7) ─────────────────

    private function getTunjanganFungsional(Pegawai $pegawai, ?GolonganRuang $golongan): float
    {
        if ($pegawai->status_dosen !== 'dosen' || ! $golongan) {
            return 0.0;
        }

        $scale = TunjanganFungsionalDosenScale::where('golongan_ruang', $golongan->kode)
            ->first();

        return $scale ? (float) $scale->nominal : 0.0;
    }

    // ─── Tunjangan Struktural (§10c) ─────────────────────

    private function getTunjanganStruktural(Pegawai $pegawai): float
    {
        $poin = $pegawai->jabatanStrukturalPoin;
        if (! $poin) {
            return 0.0;
        }

        return (float) $poin->tunjangan_baru;
    }

    // ─── Tunjangan Variabel (§9) ─────────────────────────

    private function getTunjanganVariabel(?GolonganRuang $golongan, PayrollSetting $settings): float
    {
        if (! $golongan) {
            return 0.0;
        }

        $scale = TunjanganVariabelScale::where('golongan_ruang_id', $golongan->id)->first();

        if (! $scale) {
            return 0.0;
        }

        // Apply percentage from payroll_settings (§9: "ditunaikan sesuai persentase penilaian kinerja")
        $pct = (float) $settings->tunjangan_variabel_default_percentage / 100;

        return (float) $scale->nominal_maksimum * $pct;
    }

    // ─── Tunjangan Makan (§6) ────────────────────────────

    private function getTunjanganMakan(PayrollSetting $settings, int $kehadiran): float
    {
        // NULL = belum diisi Admin, treat as 0 for calculation
        $perHari = $settings->tunjangan_makan_per_hari;

        return ($perHari !== null ? (float) $perHari : 0.0) * $kehadiran;
    }

    // ─── Tunjangan Keluarga (§5) ─────────────────────────

    private function getTunjanganIstri(Pegawai $pegawai, float $gajiPokok): float
    {
        if ($pegawai->status_kawin !== 'kawin') {
            return 0.0;
        }

        // Spouse dedup: if both spouses work at UA, only one gets tunjangan istri
        // Check if another pegawai has the same spouse name and is also married
        if ($pegawai->nama_sm) {
            $spouseAlsoWorks = Pegawai::where('nama_pegawai', $pegawai->nama_sm)
                ->where('status_kawin', 'kawin')
                ->where('id', '!=', $pegawai->id)
                ->exists();

            if ($spouseAlsoWorks) {
                // Both work at UA — only the one with lower gaji pokok gets it
                // (or neither if same gaji pokok — use nik as tiebreaker)
                $spouseGajiPokok = $this->getGajiPokok(
                    Pegawai::where('nama_pegawai', $pegawai->nama_sm)->first()->golonganRuang,
                    $this->hitungMkg(Pegawai::where('nama_pegawai', $pegawai->nama_sm)->first())
                );

                if ($spouseGajiPokok > $gajiPokok) {
                    return 0.0; // Spouse has higher gaji, they get it
                }
                if ($spouseGajiPokok === $gajiPokok && $pegawai->nik > Pegawai::where('nama_pegawai', $pegawai->nama_sm)->first()->nik) {
                    return 0.0; // Same gaji, higher nik doesn't get it
                }
            }
        }

        return $gajiPokok * 0.10;
    }

    private function getTunjanganAnak(Pegawai $pegawai, int $anakCount): float
    {
        $golongan = $pegawai->golonganRuang;
        if (! $golongan || $anakCount <= 0) {
            return 0.0;
        }

        $gajiPokok = $this->getGajiPokok($golongan, $this->hitungMkg($pegawai));

        return $gajiPokok * 0.02 * $anakCount;
    }

    private function hitungAnak(Pegawai $pegawai): int
    {
        // PRD §5: Hanya anak usia < 21 tahun yang dihitung (max 3)
        $anakList = $pegawai->anak()->get();

        $countUnder21 = 0;
        foreach ($anakList as $anak) {
            if (! $anak->tanggal_lahir) {
                // No birth date — count anyway (legacy data)
                $countUnder21++;
                continue;
            }

            $usia = $anak->tanggal_lahir->diffInYears(now());
            if ($usia < 21) {
                $countUnder21++;
            }
        }

        return min($countUnder21, 3);
    }

    // ─── Tunjangan Transportasi (§11) ────────────────────

    private function getTunjanganTransportasi(Pegawai $pegawai): float
    {
        $poin = $pegawai->jabatanStrukturalPoin;

        if ($poin && $poin->levelStruktur) {
            // Structural: look up by level_struktur with null golongan_range
            $scale = TunjanganTransportasiScale::where('level_struktur_id', $poin->levelStruktur->id)
                ->whereNull('golongan_range')
                ->first();

            if ($scale) {
                return (float) $scale->nominal;
            }
        }

        // Non-structural: use Operasional D + golongan range
        return $this->getTransportNonStruktural($pegawai);
    }

    private function getTransportNonStruktural(Pegawai $pegawai): float
    {
        $golongan = $pegawai->golonganRuang;
        if (! $golongan) {
            return 0.0;
        }

        $kode = strtoupper($golongan->kode);
        $range = $this->golonganToRange($kode);

        $opD = GolonganRuang::where('kode', 'like', '%Operasional D%')->first();
        // ponytail: cari level_struktur "Operasional D" langsung
        $levelOpD = \App\Models\LevelStruktur::where('kode', 'Operasional D')->first();

        if (! $levelOpD) {
            return 0.0;
        }

        $scale = TunjanganTransportasiScale::where('level_struktur_id', $levelOpD->id)
            ->where('golongan_range', $range)
            ->first();

        return $scale ? (float) $scale->nominal : 0.0;
    }

    private function golonganToRange(string $kode): string
    {
        $map = [
            'II/A' => 'IIA-IIIB', 'II/B' => 'IIA-IIIB',
            'II/C' => 'IIIC-IIID', 'II/D' => 'IIIC-IIID',
            'III/A' => 'IIA-IIIB', 'III/B' => 'IIA-IIIB',
            'III/C' => 'IIIC-IIID', 'III/D' => 'IIIC-IIID',
            'IV/A' => 'IVA-IVC', 'IV/B' => 'IVA-IVC', 'IV/C' => 'IVA-IVC',
            'IV/D' => 'IVD-IVE', 'IV/E' => 'IVD-IVE',
            'I/A' => 'IIA-IIIB', 'I/B' => 'IIA-IIIB',
            'I/C' => 'IIIC-IIID', 'I/D' => 'IIIC-IIID',
        ];

        return $map[$kode] ?? 'IIA-IIIB';
    }

    // ─── Honor Kelebihan SKS (§13) ────────────────────────

    private function getHonorKelebihanSks(Pegawai $pegawai, float $actualSksTaught): float
    {
        // Only dosen get honor SKS
        if ($pegawai->status_dosen !== 'dosen') {
            return 0.0;
        }

        // Determine standard beban SKS from jabatan struktural
        $bebanSks = null;
        if ($pegawai->jabatanStrukturalPoin) {
            $bebanSks = BebanSksJabatan::where('jabatan_struktural_poin_id', $pegawai->jabatanStrukturalPoin->id)->first();
        }
        // Default to 12 if no match (dosen without jabatan struktural)
        $standardSks = $bebanSks ? (float) $bebanSks->total : 12.0;

        // Look up nilai_sks from honor_sks_scale
        $program = $pegawai->program_mengajar;
        $statusDosen = 'tetap'; // dosen tetap by default; tidak_tetap = honoraire
        $strata = $this->mapStrata($pegawai->strata_pendidikan);

        if (! $program || ! $strata) {
            return 0.0;
        }

        $scale = HonorSksScale::where('program', $program)
            ->where('status_dosen', $statusDosen)
            ->where('strata', $strata)
            ->first();

        if (! $scale) {
            return 0.0;
        }

        $kelebihanSks = max(0.0, $actualSksTaught - $standardSks);

        return $kelebihanSks * (float) $scale->nilai_sks;
    }

    private function mapStrata(?string $strata): ?string
    {
        return match ($strata) {
            'profesor' => 'Profesor',
            's3' => 'S.3 / Dr',
            's2' => 'S.2 / Magister',
            default => null,
        };
    }
}
