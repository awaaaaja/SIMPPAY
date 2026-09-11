<?php

namespace Database\Seeders;

use App\Models\BebanSksJabatan;
use Illuminate\Database\Seeder;

class BebanSksJabatanSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §18 — Beban SKS Mengajar Dosen Tetap per Jabatan (total wajib 12)
        $data = [
            ['nama_jabatan' => 'Rektor', 'sks_perkuliahan' => 0, 'sks_penelitian' => 3, 'sks_adm' => 0, 'sks_jabatan' => 9],
            ['nama_jabatan' => 'Wakil Rektor 1', 'sks_perkuliahan' => 2, 'sks_penelitian' => 3, 'sks_adm' => 0, 'sks_jabatan' => 7],
            ['nama_jabatan' => 'Wakil Rektor 2', 'sks_perkuliahan' => 2, 'sks_penelitian' => 3, 'sks_adm' => 0, 'sks_jabatan' => 7],
            ['nama_jabatan' => 'Kepala Lembaga', 'sks_perkuliahan' => 3, 'sks_penelitian' => 3, 'sks_adm' => 0, 'sks_jabatan' => 6],
            ['nama_jabatan' => 'Kepala Biro', 'sks_perkuliahan' => 3, 'sks_penelitian' => 3, 'sks_adm' => 1, 'sks_jabatan' => 5],
            ['nama_jabatan' => 'Sekretaris Lembaga', 'sks_perkuliahan' => 4, 'sks_penelitian' => 3, 'sks_adm' => 2, 'sks_jabatan' => 3],
            ['nama_jabatan' => 'Kepala UPT', 'sks_perkuliahan' => 3, 'sks_penelitian' => 3, 'sks_adm' => 2, 'sks_jabatan' => 4],
            ['nama_jabatan' => 'Kepala Bidang/Kepala Bagian/Kepala Lab.', 'sks_perkuliahan' => 4, 'sks_penelitian' => 3, 'sks_adm' => 2, 'sks_jabatan' => 3],
            ['nama_jabatan' => 'Ka. Prodi', 'sks_perkuliahan' => 3, 'sks_penelitian' => 3, 'sks_adm' => 3, 'sks_jabatan' => 3],
            ['nama_jabatan' => 'Sekretaris Prodi', 'sks_perkuliahan' => 4, 'sks_penelitian' => 3, 'sks_adm' => 3, 'sks_jabatan' => 2],
            ['nama_jabatan' => 'Koord. TA PGSD/Kasub.Bag', 'sks_perkuliahan' => 5, 'sks_penelitian' => 3, 'sks_adm' => 4, 'sks_jabatan' => 0],
            ['nama_jabatan' => 'Dosen Tetap', 'sks_perkuliahan' => 6, 'sks_penelitian' => 3, 'sks_adm' => 3, 'sks_jabatan' => 0],
        ];

        foreach ($data as $item) {
            BebanSksJabatan::firstOrCreate(
                ['nama_jabatan' => $item['nama_jabatan']],
                array_merge($item, ['total' => 12])
            );
        }
    }
}
