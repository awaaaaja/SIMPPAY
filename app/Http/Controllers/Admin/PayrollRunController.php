<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CalculatePayrollJob;
use App\Models\PayrollRun;
use App\Services\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollRunController extends Controller
{
    public function index(Request $request): Response
    {
        $runs = PayrollRun::query()
            ->with(['calculatedBy', 'finalizedBy'])
            ->orderBy('periode', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/PayrollRun/Index', [
            'runs' => $runs,
            'can' => [
                'create' => $request->user()->can('create', PayrollRun::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
        ]);
    }

    public function show(PayrollRun $payroll_run): Response
    {
        $payroll_run->load(['details.pegawai.jabatan', 'calculatedBy', 'finalizedBy']);

        return Inertia::render('Admin/PayrollRun/Show', [
            'run' => $payroll_run,
            'can' => [
                'update' => request()->user()->can('update', $payroll_run),
            ],
        ]);
    }

    public function calculate(Request $request): RedirectResponse
    {
        $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
            'formula_version' => ['sometimes', 'string', 'in:legacy,ua-2025'],
        ]);

        $periode = $request->input('periode');
        $formulaVersion = $request->input('formula_version', 'legacy');

        CalculatePayrollJob::dispatch($periode, $request->user()->id, $formulaVersion);

        return redirect()->route('admin.payroll-run.index')
            ->with('success', "Perhitungan gaji periode {$periode} ({$formulaVersion}) sedang diproses.");
    }

    public function finalize(PayrollRun $payroll_run, PayrollService $service): RedirectResponse
    {
        $service->finalize($payroll_run, request()->user());

        return redirect()->route('admin.payroll-run.show', $payroll_run->id)
            ->with('success', 'Payroll run berhasil difinalisasi.');
    }

    public function void(PayrollRun $payroll_run, PayrollService $service): RedirectResponse
    {
        request()->validate([
            'void_reason' => ['required', 'string', 'min:3'],
        ]);

        $service->void($payroll_run, request('void_reason'), request()->user());

        return redirect()->route('admin.payroll-run.index')
            ->with('success', 'Payroll run berhasil dibatalkan.');
    }
}
