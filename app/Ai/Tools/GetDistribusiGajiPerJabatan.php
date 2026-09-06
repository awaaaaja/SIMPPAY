<?php

namespace App\Ai\Tools;

use App\Models\PayrollDetail;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetDistribusiGajiPerJabatan implements Tool
{
    public function description(): string
    {
        return 'Mendapatkan distribusi total gaji per jabatan dalam periode tertentu.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'periode' => $schema->string()->required()->description('Periode dalam format YYYY-MM (contoh: 2026-01)'),
        ];
    }

    public function handle(Request $request): string
    {
        $validated = $request->validate([
            'periode' => 'required|string|date_format:Y-m',
        ]);

        $periode = Carbon::parse($validated['periode'])->startOfMonth();

        $details = PayrollDetail::with('pegawai.jabatan')
            ->whereHas('payrollRun', fn ($q) => $q->where('periode', $periode)->where('status', 'finalized'))
            ->get();

        if ($details->isEmpty()) {
            return "Tidak ada data gaji final untuk periode {$validated['periode']}.";
        }

        $distribusi = $details
            ->groupBy(fn ($d) => $d->pegawai->jabatan->nama_jabatan ?? 'Tidak diketahui')
            ->map(fn ($items, $jabatan) => [
                'jabatan' => $jabatan,
                'total' => $items->sum('total_gaji'),
                'jumlah_pegawai' => $items->count(),
            ])
            ->values();

        $lines = $distribusi->map(fn ($d) => "- {$d['jabatan']}: Rp ".number_format($d['total'], 0, ',', '.')." ({$d['jumlah_pegawai']} pegawai)")->implode("\n");

        return "Distribusi gaji per jabatan periode {$validated['periode']}:\n{$lines}";
    }
}
