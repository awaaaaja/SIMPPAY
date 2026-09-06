<?php

namespace App\Ai\Tools;

use App\Models\PayrollDetail;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetTotalGajiByJabatan implements Tool
{
    public function description(): string
    {
        return 'Mendapatkan total gaji seluruh pegawai pada jabatan tertentu dalam periode tertentu.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'nama_jabatan' => $schema->string()->required()->description('Nama jabatan (contoh: Dosen, Staff Marketing)'),
            'periode' => $schema->string()->required()->description('Periode dalam format YYYY-MM (contoh: 2026-01)'),
        ];
    }

    public function handle(Request $request): string
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string',
            'periode' => 'required|string|date_format:Y-m',
        ]);

        $periode = Carbon::parse($validated['periode'])->startOfMonth();

        $total = PayrollDetail::whereHas('pegawai.jabatan', fn ($q) => $q->where('nama_jabatan', $validated['nama_jabatan']))
            ->whereHas('payrollRun', fn ($q) => $q->where('periode', $periode)->where('status', 'finalized'))
            ->sum('total_gaji');

        $count = PayrollDetail::whereHas('pegawai.jabatan', fn ($q) => $q->where('nama_jabatan', $validated['nama_jabatan']))
            ->whereHas('payrollRun', fn ($q) => $q->where('periode', $periode)->where('status', 'finalized'))
            ->count();

        return "Total gaji jabatan {$validated['nama_jabatan']} periode {$validated['periode']}: Rp ".number_format($total, 0, ',', '.')." ({$count} pegawai).";
    }
}
