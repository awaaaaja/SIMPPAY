<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $periodeAktif = PayrollRun::where('status', '!=', 'void')
            ->orderByDesc('periode')
            ->first();

        $totalPegawai = Pegawai::where('status_pegawai', 'aktif')->count();
        $totalJabatan = Jabatan::count();

        $payrollSummary = null;
        if ($periodeAktif) {
            $details = PayrollDetail::where('payroll_run_id', $periodeAktif->id)->get();
            $payrollSummary = [
                'periode' => $periodeAktif->periode->format('Y-m'),
                'status' => $periodeAktif->status,
                'total_pegawai' => $details->count(),
                'total_gaji' => $details->sum('total_gaji'),
            ];
        }

        $recentRuns = PayrollRun::with('calculatedBy', 'finalizedBy')
            ->orderByDesc('periode')
            ->limit(5)
            ->get()
            ->map(fn ($run) => [
                'id' => $run->id,
                'periode' => $run->periode->format('Y-m'),
                'status' => $run->status,
                'calculated_by' => $run->calculatedBy?->name,
                'finalized_by' => $run->finalizedBy?->name,
                'created_at' => $run->created_at->format('d M Y H:i'),
            ]);

        $payrollTrend = PayrollRun::where('status', 'finalized')
            ->orderByDesc('periode')
            ->limit(6)
            ->get()
            ->map(function ($run) {
                $total = PayrollDetail::where('payroll_run_id', $run->id)->sum('total_gaji');

                return [
                    'periode' => $run->periode->format('Y-m'),
                    'total' => (float) $total,
                ];
            })
            ->reverse()
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_pegawai' => $totalPegawai,
                'total_jabatan' => $totalJabatan,
                'periode_aktif' => $periodeAktif?->periode->format('Y-m'),
                'payroll_summary' => $payrollSummary,
            ],
            'recentRuns' => $recentRuns,
            'payrollTrend' => $payrollTrend,
        ]);
    }
}
