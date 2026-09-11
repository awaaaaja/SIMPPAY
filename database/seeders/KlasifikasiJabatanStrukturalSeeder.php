<?php

namespace Database\Seeders;

use App\Models\KlasifikasiJabatanStruktural;
use Illuminate\Database\Seeder;

class KlasifikasiJabatanStrukturalSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §10b — 6 Klasifikasi Jabatan Struktural
        $data = [
            ['klasifikasi' => 1, 'poin_min' => 200, 'poin_max' => 400, 'level_jabatan' => 'Sekprod/Kaprod', 'tunjangan_min' => 1050000, 'tunjangan_max' => 1500000],
            ['klasifikasi' => 2, 'poin_min' => 401, 'poin_max' => 500, 'level_jabatan' => 'Kabid/Kabag', 'tunjangan_min' => 1600000, 'tunjangan_max' => 1700000],
            ['klasifikasi' => 3, 'poin_min' => 501, 'poin_max' => 600, 'level_jabatan' => 'Sekretaris Lembaga', 'tunjangan_min' => 1750000, 'tunjangan_max' => 1800000],
            ['klasifikasi' => 4, 'poin_min' => 601, 'poin_max' => 700, 'level_jabatan' => 'Kepala UPT', 'tunjangan_min' => 1900000, 'tunjangan_max' => 2000000],
            ['klasifikasi' => 5, 'poin_min' => 701, 'poin_max' => 800, 'level_jabatan' => 'Kabiro/KaLembaga', 'tunjangan_min' => 2100000, 'tunjangan_max' => 2500000],
            ['klasifikasi' => 6, 'poin_min' => 801, 'poin_max' => 1000, 'level_jabatan' => 'Pimpinan', 'tunjangan_min' => 7500000, 'tunjangan_max' => 8250000],
        ];

        foreach ($data as $item) {
            KlasifikasiJabatanStruktural::firstOrCreate(
                ['klasifikasi' => $item['klasifikasi']],
                $item
            );
        }
    }
}
