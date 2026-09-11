<?php

namespace Database\Seeders;

use App\Models\JabatanStrukturalPoin;
use App\Models\KlasifikasiJabatanStruktural;
use App\Models\LevelStruktur;
use Illuminate\Database\Seeder;

class JabatanStrukturalPoinSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §10c — ~50 Jabatan Struktural dengan Poin & Tunjangan
        $levelA = LevelStruktur::where('kode', 'Strategis A')->first();
        $levelB = LevelStruktur::where('kode', 'Strategis B')->first();
        $levelC = LevelStruktur::where('kode', 'Strategis C')->first();
        $levelOpA = LevelStruktur::where('kode', 'Operasional A')->first();
        $levelOpB = LevelStruktur::where('kode', 'Operasional B')->first();
        $levelOpC = LevelStruktur::where('kode', 'Operasional C')->first();
        $levelOpD = LevelStruktur::where('kode', 'Operasional D')->first();

        $klas6 = KlasifikasiJabatanStruktural::where('klasifikasi', 6)->first();
        $klas5 = KlasifikasiJabatanStruktural::where('klasifikasi', 5)->first();
        $klas4 = KlasifikasiJabatanStruktural::where('klasifikasi', 4)->first();
        $klas3 = KlasifikasiJabatanStruktural::where('klasifikasi', 3)->first();
        $klas2 = KlasifikasiJabatanStruktural::where('klasifikasi', 2)->first();
        $klas1 = KlasifikasiJabatanStruktural::where('klasifikasi', 1)->first();

        $data = [
            // Strategis A (klasifikasi 6)
            ['level_struktur_id' => $levelA?->id, 'nama_jabatan' => 'Rektor', 'total_poin' => 962, 'klasifikasi_id' => $klas6?->id, 'tunjangan_baru' => 8250000],
            ['level_struktur_id' => $levelA?->id, 'nama_jabatan' => 'Wakil Rektor 1', 'total_poin' => 946, 'klasifikasi_id' => $klas6?->id, 'tunjangan_baru' => 7500000],
            ['level_struktur_id' => $levelA?->id, 'nama_jabatan' => 'Wakil Rektor 2', 'total_poin' => 946, 'klasifikasi_id' => $klas6?->id, 'tunjangan_baru' => 7500000],

            // Strategis B (klasifikasi 5)
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Kepala BKPU', 'total_poin' => 789, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2500000],
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Ketua LPPPM', 'total_poin' => 759, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2500000],
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Kepala BPSDM', 'total_poin' => 741, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2400000],
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Ketua LPPM', 'total_poin' => 733, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2400000],
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Kepala BAAK', 'total_poin' => 729, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2400000],
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Dekan', 'total_poin' => 717, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2400000],
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Kepala BKHM', 'total_poin' => 717, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2400000],
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Ketua LPKPM', 'total_poin' => 711, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2400000],
            ['level_struktur_id' => $levelB?->id, 'nama_jabatan' => 'Kepala LKPKA', 'total_poin' => 702, 'klasifikasi_id' => $klas5?->id, 'tunjangan_baru' => 2200000],

            // Strategis C (klasifikasi 4)
            ['level_struktur_id' => $levelC?->id, 'nama_jabatan' => 'Kepala Pustikom', 'total_poin' => 628, 'klasifikasi_id' => $klas4?->id, 'tunjangan_baru' => 1900000],
            ['level_struktur_id' => $levelC?->id, 'nama_jabatan' => 'Kepala Perpustakaan', 'total_poin' => 625, 'klasifikasi_id' => $klas4?->id, 'tunjangan_baru' => 1900000],
            ['level_struktur_id' => $levelC?->id, 'nama_jabatan' => 'Wakil Dekan', 'total_poin' => 619, 'klasifikasi_id' => $klas4?->id, 'tunjangan_baru' => 2000000],
            ['level_struktur_id' => $levelC?->id, 'nama_jabatan' => 'Kabag. Keuangan', 'total_poin' => 582, 'klasifikasi_id' => $klas3?->id, 'tunjangan_baru' => 1800000],

            // Operasional A (klasifikasi 3)
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Sekretaris LPPPM', 'total_poin' => 576, 'klasifikasi_id' => $klas3?->id, 'tunjangan_baru' => 1750000],
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Sekretaris LPPM', 'total_poin' => 576, 'klasifikasi_id' => $klas3?->id, 'tunjangan_baru' => 1750000],
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Sekretaris LPKPM', 'total_poin' => 576, 'klasifikasi_id' => $klas3?->id, 'tunjangan_baru' => 1750000],
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Sekretaris LKPKA', 'total_poin' => 576, 'klasifikasi_id' => $klas3?->id, 'tunjangan_baru' => 1750000],
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Kabag. Perencanaan dan Monev', 'total_poin' => 569, 'klasifikasi_id' => $klas3?->id, 'tunjangan_baru' => 1750000],
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Kabag. Umum', 'total_poin' => 561, 'klasifikasi_id' => $klas3?->id, 'tunjangan_baru' => 1750000],

            // Operasional B (klasifikasi 2)
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Pengembangan Pendidikan', 'total_poin' => 486, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1700000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Penjaminan Mutu Eksternal', 'total_poin' => 486, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1700000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Penjaminan Mutu Internal', 'total_poin' => 486, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1700000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Sekretaris Pustikom', 'total_poin' => 475, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabag. Humas & Marketing', 'total_poin' => 470, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabag Kerjasama', 'total_poin' => 470, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabag Pusat Data Perguruan Tinggi', 'total_poin' => 466, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabag Akademik dan Kemahasiswaan', 'total_poin' => 466, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Publikasi dan HAKI', 'total_poin' => 461, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Pengabdian kepada Masyarakat', 'total_poin' => 461, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Penelitian', 'total_poin' => 461, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabag Kepegawaian', 'total_poin' => 461, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabag Diklat dan Pengembangan', 'total_poin' => 461, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Kurikulum', 'total_poin' => 448, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Ormawa', 'total_poin' => 430, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Bina Pribadi Islam', 'total_poin' => 419, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Pusat Karir dan Alumni', 'total_poin' => 417, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],
            ['level_struktur_id' => $levelOpB?->id, 'nama_jabatan' => 'Kabid Al Qur\'an', 'total_poin' => 417, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1650000],

            // Operasional A (Ketua Prodi — klasiifikasi 1-2, labeled 5* in source)
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Ketua Prodi Besar (S2 Pendas & PGSD)', 'total_poin' => 406, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1600000],
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Ketua Prodi Menengah (PG PAUD)', 'total_poin' => 369, 'klasifikasi_id' => $klas1?->id, 'tunjangan_baru' => 1500000],
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Ketua Prodi Kecil', 'total_poin' => 332, 'klasifikasi_id' => $klas1?->id, 'tunjangan_baru' => 1400000],

            // Operasional A — Kepala UPT (§10c No.7, acceptance test: Ka. UPT Perpustakaan = 1.9M)
            ['level_struktur_id' => $levelOpA?->id, 'nama_jabatan' => 'Kepala UPT', 'total_poin' => 486, 'klasifikasi_id' => $klas2?->id, 'tunjangan_baru' => 1900000],

            // Operasional C (klasifikasi 1)
            ['level_struktur_id' => $levelOpC?->id, 'nama_jabatan' => 'Sekretaris Prodi Besar', 'total_poin' => 326, 'klasifikasi_id' => $klas1?->id, 'tunjangan_baru' => 1300000],
            ['level_struktur_id' => $levelOpC?->id, 'nama_jabatan' => 'Sekretaris Prodi Menengah', 'total_poin' => 285, 'klasifikasi_id' => $klas1?->id, 'tunjangan_baru' => 1100000],
            ['level_struktur_id' => $levelOpC?->id, 'nama_jabatan' => 'Sekretaris Prodi Kecil', 'total_poin' => 208, 'klasifikasi_id' => $klas1?->id, 'tunjangan_baru' => 1050000],
            ['level_struktur_id' => $levelOpC?->id, 'nama_jabatan' => 'Kasubag Humas & Marketing', 'total_poin' => 205, 'klasifikasi_id' => $klas1?->id, 'tunjangan_baru' => 1000000],

            // Operasional D — tanpa jabatan struktural (poin 0, tunjangan 0)
            ['level_struktur_id' => $levelOpD?->id, 'nama_jabatan' => 'Dosen tetap dan karyawan (tanpa jabatan struktural)', 'total_poin' => 0, 'klasifikasi_id' => null, 'tunjangan_baru' => 0],
        ];

        foreach ($data as $row) {
            JabatanStrukturalPoin::firstOrCreate(
                ['nama_jabatan' => $row['nama_jabatan']],
                $row
            );
        }
    }
}
