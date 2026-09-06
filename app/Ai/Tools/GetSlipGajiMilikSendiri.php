<?php

namespace App\Ai\Tools;

use App\Models\PayrollDetail;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetSlipGajiMilikSendiri implements Tool
{
    public function __construct(private Pegawai $pegawai) {}

    public function description(): string
    {
        return 'Mendapatkan rincian slip gaji pegawai untuk periode tertentu.';
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

        $detail = PayrollDetail::where('pegawai_id', $this->pegawai->id)
            ->whereHas('payrollRun', fn ($q) => $q->where('periode', $periode)->where('status', 'finalized'))
            ->first();

        if (! $detail) {
            return "Slip gaji untuk periode {$validated['periode']} belum tersedia.";
        }

        return "Slip gaji {$this->pegawai->nama_pegawai} ({$validated['periode']}):\n"
            .'- Gaji Pokok: Rp '.number_format($detail->gaji_pokok, 0, ',', '.')."\n"
            .'- Tj. Transport: Rp '.number_format($detail->tj_transport, 0, ',', '.')."\n"
            .'- Uang Makan: Rp '.number_format($detail->uang_makan, 0, ',', '.')."\n"
            .'- Potongan Alpha: Rp '.number_format($detail->potongan_alpha, 0, ',', '.')."\n"
            .'- Tunjangan Tambahan: Rp '.number_format($detail->total_tunjangan_tambahan, 0, ',', '.')."\n"
            .'- Potongan Tambahan: Rp '.number_format($detail->total_potongan_tambahan, 0, ',', '.')."\n"
            .'- Honor Kelebihan SKS: Rp '.number_format($detail->honor_kelebihan_sks, 0, ',', '.')."\n"
            .'- **Total Gaji: Rp '.number_format($detail->total_gaji, 0, ',', '.').'**';
    }
}
