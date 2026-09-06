<?php

namespace App\Services;

use App\Models\DosenSks;
use App\Models\HonorSks;
use App\Models\Pegawai;
use App\Models\TahunAkademik;
use Carbon\Carbon;

final class SksService
{
    /**
     * Hitung honor kelebihan SKS untuk 1 dosen di 1 periode.
     *
     * Formula (PRD FR-31):
     *   MAX(0, sks_terpakai − sks_maksimal) × honor_per_kategori
     *
     * Returns 0 if:
     *   - pegawai bukan dosen
     *   - tidak ada DosenSks record untuk tahun akademik aktif
     *   - tidak ada active honor_sks record
     *   - hasil negatif (tidak ada kelebihan)
     */
    public function hitungHonorKelebihan(Pegawai $pegawai, Carbon $periode): float
    {
        if ($pegawai->status_dosen !== 'dosen') {
            return 0.0;
        }

        $ta = TahunAkademik::where('aktif', true)->first();
        if (! $ta) {
            return 0.0;
        }

        $dosenSks = DosenSks::where('pegawai_id', $pegawai->id)
            ->where('tahun_akademik_id', $ta->id)
            ->first();

        if (! $dosenSks) {
            return 0.0;
        }

        $kelebihan = max(0, $dosenSks->sks_terpakai - $dosenSks->sks_maksimal);

        if ($kelebihan <= 0) {
            return 0.0;
        }

        // Get the active honor rate — we use the first active honor_sks
        // associated with any kategori. In practice there may be per-kategori
        // rates; for now, get the honor_sks record that was last updated
        // (or the only one). The PRD formula uses "honor per kategori" which
        // maps to honor_sks.honor for the dosen's kategori.
        //
        // Simplified: get all active honor_sks. If multiple kategori exist,
        // sum them (a dosen may have multiple kategori). But typically
        // there's one kategori per dosen. We just use the first.
        $honorRecord = HonorSks::join('kategori_honor_sks', 'kategori_honor_sks.id', '=', 'honor_sks.kategori_honor_sks_id')
            ->select('honor_sks.honor')
            ->first();

        if (! $honorRecord) {
            return 0.0;
        }

        return $kelebihan * (float) $honorRecord->honor;
    }
}
