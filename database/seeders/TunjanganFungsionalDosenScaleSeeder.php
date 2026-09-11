<?php

namespace Database\Seeders;

use App\Models\TunjanganFungsionalDosenScale;
use Illuminate\Database\Seeder;

class TunjanganFungsionalDosenScaleSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §7 — Tunjangan Fungsional Dosen Tetap
        $data = [
            // Asisten Ahli
            ['jabatan_fungsional' => 'Asisten Ahli', 'angka_kredit' => 100, 'pangkat' => 'Penata Muda', 'golongan_ruang' => 'III.a', 'nominal' => 250000],
            ['jabatan_fungsional' => 'Asisten Ahli', 'angka_kredit' => 150, 'pangkat' => 'Penata Muda Tk. 1', 'golongan_ruang' => 'III.b', 'nominal' => 350000],
            // Lektor
            ['jabatan_fungsional' => 'Lektor', 'angka_kredit' => 200, 'pangkat' => 'Penata', 'golongan_ruang' => 'III.c', 'nominal' => 550000],
            ['jabatan_fungsional' => 'Lektor', 'angka_kredit' => 300, 'pangkat' => 'Penata Tk. 1', 'golongan_ruang' => 'III.d', 'nominal' => 750000],
            // Lektor Kepala
            ['jabatan_fungsional' => 'Lektor Kepala', 'angka_kredit' => 400, 'pangkat' => 'Pembina', 'golongan_ruang' => 'IV.a', 'nominal' => 1000000],
            ['jabatan_fungsional' => 'Lektor Kepala', 'angka_kredit' => 550, 'pangkat' => 'Pembina Tk. 1', 'golongan_ruang' => 'IV.b', 'nominal' => 1250000],
            ['jabatan_fungsional' => 'Lektor Kepala', 'angka_kredit' => 700, 'pangkat' => 'Pembina Utama Muda', 'golongan_ruang' => 'IV.c', 'nominal' => 1500000],
            // Guru Besar
            ['jabatan_fungsional' => 'Guru Besar', 'angka_kredit' => 850, 'pangkat' => 'Pembina Utama Madya', 'golongan_ruang' => 'IV.d', 'nominal' => 2000000],
            ['jabatan_fungsional' => 'Guru Besar', 'angka_kredit' => 1050, 'pangkat' => 'Pembina Utama', 'golongan_ruang' => 'IV.e', 'nominal' => 2500000],
        ];

        foreach ($data as $item) {
            TunjanganFungsionalDosenScale::firstOrCreate(
                ['jabatan_fungsional' => $item['jabatan_fungsional'], 'angka_kredit' => $item['angka_kredit']],
                $item
            );
        }
    }
}
