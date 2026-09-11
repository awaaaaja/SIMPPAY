<?php

namespace Database\Seeders;

use App\Models\GolonganRuang;
use App\Models\TunjanganJabatanKaryawanScale;
use Illuminate\Database\Seeder;

class TunjanganJabatanKaryawanScaleSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §8 — Tunjangan Jabatan Karyawan/Tendik per Golongan (17 baris)
        $data = [
            ['kode' => 'I.a',   'nominal' => 25000],
            ['kode' => 'I.b',   'nominal' => 50000],
            ['kode' => 'I.c',   'nominal' => 75000],
            ['kode' => 'I.d',   'nominal' => 100000],
            ['kode' => 'II.a',  'nominal' => 150000],
            ['kode' => 'II.b',  'nominal' => 200000],
            ['kode' => 'II.c',  'nominal' => 250000],
            ['kode' => 'II.d',  'nominal' => 300000],
            ['kode' => 'III.a', 'nominal' => 350000],
            ['kode' => 'III.b', 'nominal' => 450000],
            ['kode' => 'III.c', 'nominal' => 550000],
            ['kode' => 'III.d', 'nominal' => 650000],
            ['kode' => 'IV.a',  'nominal' => 700000],
            ['kode' => 'IV.b',  'nominal' => 750000],
            ['kode' => 'IV.c',  'nominal' => 800000],
            ['kode' => 'IV.d',  'nominal' => 850000],
            ['kode' => 'IV.e',  'nominal' => 900000],
        ];

        foreach ($data as $item) {
            $gr = GolonganRuang::where('kode', $item['kode'])->first();
            if ($gr) {
                TunjanganJabatanKaryawanScale::firstOrCreate(
                    ['golongan_ruang_id' => $gr->id],
                    ['nominal' => $item['nominal']]
                );
            }
        }
    }
}
