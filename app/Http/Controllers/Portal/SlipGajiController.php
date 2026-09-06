<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use Inertia\Inertia;
use Inertia\Response;

class SlipGajiController extends Controller
{
    public function index(): Response
    {
        $user = request()->user();
        $pegawai = $user->pegawai()->with('jabatan')->first();

        if (! $pegawai) {
            return Inertia::render('Portal/SlipGaji', [
                'slips' => [],
                'periodes' => [],
                'pegawai' => null,
                'selectedPeriodeId' => null,
            ]);
        }

        $periodes = PayrollRun::where('status', 'finalized')
            ->orderBy('periode', 'desc')
            ->get(['id', 'periode', 'status']);

        $selectedPeriodeId = request()->input('periode_id');

        $query = PayrollDetail::where('pegawai_id', $pegawai->id)
            ->with('payrollRun')
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_details.payroll_run_id')
            ->where('payroll_runs.status', 'finalized')
            ->select('payroll_details.*');

        if ($selectedPeriodeId) {
            $query->where('payroll_details.payroll_run_id', $selectedPeriodeId);
        }

        $slips = $query->orderBy('payroll_runs.periode', 'desc')->get();

        return Inertia::render('Portal/SlipGaji', [
            'slips' => $slips,
            'periodes' => $periodes,
            'pegawai' => $pegawai,
            'selectedPeriodeId' => $selectedPeriodeId,
        ]);
    }
}
