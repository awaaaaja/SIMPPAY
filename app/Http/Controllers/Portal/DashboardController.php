<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PayrollDetail;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = request()->user();
        $pegawai = $user->pegawai()->with(['jabatan', 'struktural', 'fungsional'])->first();

        if (! $pegawai) {
            return Inertia::render('Portal/Dashboard', [
                'pegawai' => null,
                'latestSlip' => null,
                'payrollHistory' => [],
            ]);
        }

        $latestSlip = PayrollDetail::where('pegawai_id', $pegawai->id)
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_details.payroll_run_id')
            ->where('payroll_runs.status', 'finalized')
            ->orderBy('payroll_runs.periode', 'desc')
            ->select('payroll_details.*', 'payroll_runs.periode')
            ->first();

        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $payrollHistory = PayrollDetail::where('pegawai_id', $pegawai->id)
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_details.payroll_run_id')
            ->where('payroll_runs.status', 'finalized')
            ->where('payroll_runs.periode', '>=', $sixMonthsAgo)
            ->select('payroll_runs.periode', 'payroll_details.total_gaji')
            ->orderBy('payroll_runs.periode', 'asc')
            ->get();

        return Inertia::render('Portal/Dashboard', [
            'pegawai' => $pegawai,
            'latestSlip' => $latestSlip,
            'payrollHistory' => $payrollHistory,
        ]);
    }

    public function riwayatAbsensi(): Response
    {
        $user = request()->user();
        $pegawai = $user->pegawai()->with(['jabatan', 'kehadiran' => function ($q) {
            $q->orderBy('periode', 'desc');
        }])->first();

        return Inertia::render('Portal/RiwayatAbsensi', [
            'pegawai' => $pegawai,
        ]);
    }
}
