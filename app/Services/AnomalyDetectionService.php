<?php

namespace App\Services;

use App\Models\PayrollAnomaly;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class AnomalyDetectionService
{
    public function detect(PayrollRun $run): array
    {
        $threshold = Config::get('simppay.anomaly_threshold', 30);

        $currentDetails = PayrollDetail::where('payroll_run_id', $run->id)
            ->with('pegawai.jabatan')
            ->get();

        $anomalies = [];

        foreach ($currentDetails as $detail) {
            $historical = $this->getHistoricalAverage($detail->pegawai_id, $run->periode);

            if ($historical === null) {
                continue;
            }

            if ($historical == 0) {
                continue;
            }

            $deviasi = abs($detail->total_gaji - $historical) / $historical * 100;

            if ($deviasi > $threshold) {
                $anomaly = PayrollAnomaly::create([
                    'payroll_detail_id' => $detail->id,
                    'tipe' => 'gaji_deviasi',
                    'nilai_sebelumnya' => $historical,
                    'nilai_sekarang' => $detail->total_gaji,
                    'persentase_deviasi' => round($deviasi, 2),
                    'catatan_ai' => null, // filled by AnomalyReviewAgent
                    'status_review' => 'pending',
                ]);

                $anomalies[] = $anomaly;
            }
        }

        return $anomalies;
    }

    private function getHistoricalAverage(int $pegawaiId, Carbon $currentPeriode): ?float
    {
        $sixMonthsAgo = $currentPeriode->copy()->subMonths(6);

        $avg = PayrollDetail::where('pegawai_id', $pegawaiId)
            ->whereHas('payrollRun', function ($q) use ($sixMonthsAgo, $currentPeriode) {
                $q->where('periode', '>=', $sixMonthsAgo)
                    ->where('periode', '<', $currentPeriode)
                    ->where('status', 'finalized');
            })
            ->avg('total_gaji');

        return $avg > 0 ? $avg : null;
    }
}
