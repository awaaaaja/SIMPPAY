<?php

namespace Database\Seeders;

use App\Models\HonorSksScale;
use Illuminate\Database\Seeder;

class HonorSksScaleSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §17 — Honor Kelebihan SKS, 3 tabel gabungan
        $data = [
            // 17a. Dosen Tetap Program S1
            ['program' => 's1', 'status_dosen' => 'tetap', 'strata' => 'Profesor', 'nilai_sks' => 75000],
            ['program' => 's1', 'status_dosen' => 'tetap', 'strata' => 'S.3 / Dr', 'nilai_sks' => 65000],
            ['program' => 's1', 'status_dosen' => 'tetap', 'strata' => 'S.2 / Magister', 'nilai_sks' => 50000],
            // 17b. Dosen Tidak Tetap (identik dengan S1 tetap)
            ['program' => 's1', 'status_dosen' => 'tidak_tetap', 'strata' => 'Profesor', 'nilai_sks' => 75000],
            ['program' => 's1', 'status_dosen' => 'tidak_tetap', 'strata' => 'S.3 / Dr', 'nilai_sks' => 65000],
            ['program' => 's1', 'status_dosen' => 'tidak_tetap', 'strata' => 'S.2 / Magister', 'nilai_sks' => 50000],
            // 17c. Program S2
            ['program' => 's2', 'status_dosen' => 'tetap', 'strata' => 'Profesor', 'nilai_sks' => 100000],
            ['program' => 's2', 'status_dosen' => 'tetap', 'strata' => 'S.3 / Dr', 'nilai_sks' => 85000],
            ['program' => 's2', 'status_dosen' => 'tetap', 'strata' => 'S.2 / Magister', 'nilai_sks' => 65000],
            ['program' => 's2', 'status_dosen' => 'tidak_tetap', 'strata' => 'Profesor', 'nilai_sks' => 100000],
            ['program' => 's2', 'status_dosen' => 'tidak_tetap', 'strata' => 'S.3 / Dr', 'nilai_sks' => 85000],
            ['program' => 's2', 'status_dosen' => 'tidak_tetap', 'strata' => 'S.2 / Magister', 'nilai_sks' => 65000],
        ];

        foreach ($data as $item) {
            HonorSksScale::firstOrCreate(
                ['program' => $item['program'], 'status_dosen' => $item['status_dosen'], 'strata' => $item['strata']],
                ['nilai_sks' => $item['nilai_sks']]
            );
        }
    }
}
