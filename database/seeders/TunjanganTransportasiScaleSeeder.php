<?php

namespace Database\Seeders;

use App\Models\LevelStruktur;
use App\Models\TunjanganTransportasiScale;
use Illuminate\Database\Seeder;

class TunjanganTransportasiScaleSeeder extends Seeder
{
    public function run(): void
    {
        // SOURCE §8 — Tunjangan Transportasi per Level Struktur
        $levelA = LevelStruktur::where('kode', 'Strategis A')->first();
        $levelB = LevelStruktur::where('kode', 'Strategis B')->first();
        $levelC = LevelStruktur::where('kode', 'Strategis C')->first();
        $levelOpA = LevelStruktur::where('kode', 'Operasional A')->first();
        $levelOpB = LevelStruktur::where('kode', 'Operasional B')->first();
        $levelOpC = LevelStruktur::where('kode', 'Operasional C')->first();
        $levelOpD = LevelStruktur::where('kode', 'Operasional D')->first();

        $rows = [
            // §8 No.1: Strategis A — Rektor, Wakil Rektor
            ['level_struktur_id' => $levelA?->id, 'keterangan' => 'Rektor, Wakil Rektor', 'golongan_range' => null, 'nominal' => 754000],
            // §8 No.2: Strategis B, C — Selain Rektor, Wakil Rektor
            ['level_struktur_id' => $levelB?->id, 'keterangan' => 'Strategis B', 'golongan_range' => null, 'nominal' => 629200],
            ['level_struktur_id' => $levelC?->id, 'keterangan' => 'Strategis C', 'golongan_range' => null, 'nominal' => 629200],
            // §8 No.3: Operasional A
            ['level_struktur_id' => $levelOpA?->id, 'keterangan' => 'Operasional A', 'golongan_range' => null, 'nominal' => 566800],
            // §8 No.4: Operasional B, C
            ['level_struktur_id' => $levelOpB?->id, 'keterangan' => 'Operasional B', 'golongan_range' => null, 'nominal' => 504400],
            ['level_struktur_id' => $levelOpC?->id, 'keterangan' => 'Operasional C', 'golongan_range' => null, 'nominal' => 504400],
            // §8 No.5: Operasional D — per golongan range
            ['level_struktur_id' => $levelOpD?->id, 'keterangan' => 'Gol IIA–IIIB', 'golongan_range' => 'IIA-IIIB', 'nominal' => 390000],
            ['level_struktur_id' => $levelOpD?->id, 'keterangan' => 'Gol IIIC–IIID', 'golongan_range' => 'IIIC-IIID', 'nominal' => 442000],
            ['level_struktur_id' => $levelOpD?->id, 'keterangan' => 'Gol IVA–IVC', 'golongan_range' => 'IVA-IVC', 'nominal' => 494000],
            ['level_struktur_id' => $levelOpD?->id, 'keterangan' => 'Gol IVD–IVE', 'golongan_range' => 'IVD-IVE', 'nominal' => 546000],
        ];

        foreach ($rows as $row) {
            TunjanganTransportasiScale::firstOrCreate(
                ['keterangan' => $row['keterangan']],
                $row
            );
        }
    }
}
