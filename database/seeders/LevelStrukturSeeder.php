<?php

namespace Database\Seeders;

use App\Models\LevelStruktur;
use Illuminate\Database\Seeder;

class LevelStrukturSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §11 — 7 Level Struktur
        $data = [
            ['kode' => 'Strategis A',     'nama' => 'Strategis A',     'urutan' => 1],
            ['kode' => 'Strategis B',     'nama' => 'Strategis B',     'urutan' => 2],
            ['kode' => 'Strategis C',     'nama' => 'Strategis C',     'urutan' => 3],
            ['kode' => 'Operasional A',   'nama' => 'Operasional A',   'urutan' => 4],
            ['kode' => 'Operasional B',   'nama' => 'Operasional B',   'urutan' => 5],
            ['kode' => 'Operasional C',   'nama' => 'Operasional C',   'urutan' => 6],
            ['kode' => 'Operasional D',   'nama' => 'Operasional D',   'urutan' => 7],
        ];

        foreach ($data as $item) {
            LevelStruktur::firstOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
