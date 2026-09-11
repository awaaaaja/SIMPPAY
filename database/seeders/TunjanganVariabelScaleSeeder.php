<?php

namespace Database\Seeders;

use App\Models\GolonganRuang;
use App\Models\TunjanganVariabelScale;
use Illuminate\Database\Seeder;

class TunjanganVariabelScaleSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §9 — Tunjangan Variabel per Golongan (nominal MAKSIMUM, 17 baris)
        // Catatan: pencairan = nominal × persentase kinerja (belum ada sistemnya)
        $data = [
            ['kode' => 'I.a',   'nominal_maksimum' => 50000],
            ['kode' => 'I.b',   'nominal_maksimum' => 100000],
            ['kode' => 'I.c',   'nominal_maksimum' => 150000],
            ['kode' => 'I.d',   'nominal_maksimum' => 200000],
            ['kode' => 'II.a',  'nominal_maksimum' => 250000],
            ['kode' => 'II.b',  'nominal_maksimum' => 300000],
            ['kode' => 'II.c',  'nominal_maksimum' => 350000],
            ['kode' => 'II.d',  'nominal_maksimum' => 400000],
            ['kode' => 'III.a', 'nominal_maksimum' => 450000],
            ['kode' => 'III.b', 'nominal_maksimum' => 500000],
            ['kode' => 'III.c', 'nominal_maksimum' => 550000],
            ['kode' => 'III.d', 'nominal_maksimum' => 600000],
            ['kode' => 'IV.a',  'nominal_maksimum' => 800000],
            ['kode' => 'IV.b',  'nominal_maksimum' => 950000],
            ['kode' => 'IV.c',  'nominal_maksimum' => 1100000],
            ['kode' => 'IV.d',  'nominal_maksimum' => 1250000],
            ['kode' => 'IV.e',  'nominal_maksimum' => 1400000],
        ];

        foreach ($data as $item) {
            $gr = GolonganRuang::where('kode', $item['kode'])->first();
            if ($gr) {
                TunjanganVariabelScale::firstOrCreate(
                    ['golongan_ruang_id' => $gr->id],
                    ['nominal_maksimum' => $item['nominal_maksimum']]
                );
            }
        }
    }
}
