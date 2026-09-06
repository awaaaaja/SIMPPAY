<?php

namespace App\Ai\Tools;

use App\Models\Kehadiran;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetRiwayatAbsensiPegawai implements Tool
{
    public function __construct(private Pegawai $pegawai) {}

    public function description(): string
    {
        return 'Mendapatkan riwayat absensi (kehadiran) pegawai.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'bulan' => $schema->string()->description('Bulan dalam format YYYY-MM, kosongkan untuk 3 bulan terakhir (contoh: 2026-01)'),
        ];
    }

    public function handle(Request $request): string
    {
        $validated = $request->validate([
            'bulan' => 'nullable|string|date_format:Y-m',
        ]);

        $query = Kehadiran::where('pegawai_id', $this->pegawai->id)
            ->orderBy('periode', 'desc');

        if (! empty($validated['bulan'])) {
            $periode = Carbon::parse($validated['bulan'])->startOfMonth();
            $query->where('periode', $periode);

            $absensi = $query->first();

            if (! $absensi) {
                return "Data absensi untuk bulan {$validated['bulan']} belum tersedia.";
            }

            return "Absensi {$this->pegawai->nama_pegawai} ({$validated['bulan']}): Hadir {$absensi->hadir} hari, Sakit {$absensi->sakit} hari, Alpha {$absensi->alpha} hari.";
        }

        $absensi = $query->limit(3)->get();

        if ($absensi->isEmpty()) {
            return "Riwayat absensi belum tersedia untuk {$this->pegawai->nama_pegawai}.";
        }

        $lines = $absensi->map(fn ($a) => "- {$a->periode->format('Y-m')}: Hadir {$a->hadir}, Sakit {$a->sakit}, Alpha {$a->alpha}")->implode("\n");

        return "Riwayat absensi 3 bulan terakhir {$this->pegawai->nama_pegawai}:\n{$lines}";
    }
}
