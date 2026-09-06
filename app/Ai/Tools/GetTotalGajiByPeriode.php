<?php

namespace App\Ai\Tools;

use App\Models\PayrollDetail;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetTotalGajiByPeriode implements Tool
{
    public function description(): string
    {
        return 'Mendapatkan total gaji seluruh pegawai dalam periode tertentu.';
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

        $total = PayrollDetail::whereHas('payrollRun', fn ($q) => $q->where('periode', $periode)->where('status', 'finalized'))
            ->sum('total_gaji');

        $count = PayrollDetail::whereHas('payrollRun', fn ($q) => $q->where('periode', $periode)->where('status', 'finalized'))
            ->count();

        return "Total gaji seluruh pegawai periode {$validated['periode']}: Rp ".number_format($total, 0, ',', '.')." ({$count} pegawai).";
    }
}
