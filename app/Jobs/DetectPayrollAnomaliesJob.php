<?php

namespace App\Jobs;

use App\Ai\Agents\AnomalyReviewAgent;
use App\Models\PayrollRun;
use App\Services\AnomalyDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectPayrollAnomaliesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public int $payrollRunId,
    ) {}

    public function handle(AnomalyDetectionService $detectionService): void
    {
        $run = PayrollRun::findOrFail($this->payrollRunId);

        $anomalies = $detectionService->detect($run);

        foreach ($anomalies as $anomaly) {
            try {
                $agent = new AnomalyReviewAgent(
                    namaPegawai: $anomaly->payrollDetail->pegawai->nama_pegawai ?? '-',
                    jabatan: $anomaly->payrollDetail->pegawai->jabatan->nama_jabatan ?? '-',
                    nilaiSebelumnya: (float) $anomaly->nilai_sebelumnya,
                    nilaiSekarang: (float) $anomaly->nilai_sekarang,
                    persentaseDeviasi: (float) $anomaly->persentase_deviasi,
                    tipe: $anomaly->tipe,
                );

                $result = $agent->prompt(
                    "Analisa anomali gaji untuk {$anomaly->payrollDetail->pegawai->nama_pegawai} "
                    ."(jabatan: {$anomaly->payrollDetail->pegawai->jabatan->nama_jabatan}): "
                    .'nilai sebelumnya Rp '.number_format($anomaly->nilai_sebelumnya, 0, ',', '.')
                    .', sekarang Rp '.number_format($anomaly->nilai_sekarang, 0, ',', '.')
                    .", deviasi {$anomaly->persentase_deviasi}%."
                );

                $structured = $result->structured();

                $anomaly->update([
                    'catatan_ai' => json_encode($structured, JSON_UNESCAPED_UNICODE),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('AnomalyReviewAgent failed for anomaly '.$anomaly->id.': '.$e->getMessage());

                $anomaly->update([
                    'catatan_ai' => json_encode([
                        'ringkasan' => "Deviasi gaji {$anomaly->persentase_deviasi}% terdeteksi.",
                        'kemungkinan_penyebab' => 'Perlu review manual.',
                        'rekomendasi_tindakan' => 'Hubungi HR untuk pengecekan.',
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }
}
