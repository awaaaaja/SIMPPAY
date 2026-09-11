<?php

namespace Database\Seeders;

use App\Models\GolonganRuang;
use Illuminate\Database\Seeder;

class GolonganRuangSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §4 — 17 Golongan/Ruang dari I.a sampai IV.e
        $data = [
            ['kode' => 'I.a',   'nama' => 'Golongan I Ruang a',   'urutan' => 1],
            ['kode' => 'I.b',   'nama' => 'Golongan I Ruang b',   'urutan' => 2],
            ['kode' => 'I.c',   'nama' => 'Golongan I Ruang c',   'urutan' => 3],
            ['kode' => 'I.d',   'nama' => 'Golongan I Ruang d',   'urutan' => 4],
            ['kode' => 'II.a',  'nama' => 'Golongan II Ruang a',  'urutan' => 5],
            ['kode' => 'II.b',  'nama' => 'Golongan II Ruang b',  'urutan' => 6],
            ['kode' => 'II.c',  'nama' => 'Golongan II Ruang c',  'urutan' => 7],
            ['kode' => 'II.d',  'nama' => 'Golongan II Ruang d',  'urutan' => 8],
            ['kode' => 'III.a', 'nama' => 'Golongan III Ruang a', 'urutan' => 9],
            ['kode' => 'III.b', 'nama' => 'Golongan III Ruang b', 'urutan' => 10],
            ['kode' => 'III.c', 'nama' => 'Golongan III Ruang c', 'urutan' => 11],
            ['kode' => 'III.d', 'nama' => 'Golongan III Ruang d', 'urutan' => 12],
            ['kode' => 'IV.a',  'nama' => 'Golongan IV Ruang a',  'urutan' => 13],
            ['kode' => 'IV.b',  'nama' => 'Golongan IV Ruang b',  'urutan' => 14],
            ['kode' => 'IV.c',  'nama' => 'Golongan IV Ruang c',  'urutan' => 15],
            ['kode' => 'IV.d',  'nama' => 'Golongan IV Ruang d',  'urutan' => 16],
            ['kode' => 'IV.e',  'nama' => 'Golongan IV Ruang e',  'urutan' => 17],
        ];

        foreach ($data as $item) {
            GolonganRuang::firstOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
