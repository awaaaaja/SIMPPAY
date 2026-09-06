<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayrollDetailResource;
use App\Jobs\CalculatePayrollJob;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PenggajianController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PayrollDetail::with(['payrollRun', 'pegawai.jabatan']);

        if ($request->filled('periode')) {
            $periode = Carbon::parse($request->input('periode'))->startOfMonth();
            $query->whereHas('payrollRun', fn ($q) => $q->where('periode', $periode));
        }

        if ($request->filled('payroll_run_id')) {
            $query->where('payroll_run_id', $request->input('payroll_run_id'));
        }

        $details = $query->orderByDesc('id')->paginate($request->integer('per_page', 15));

        return PayrollDetailResource::collection($details);
    }

    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m-d',
        ]);

        $periode = Carbon::parse($request->input('periode'))->startOfMonth();

        $existingRun = PayrollRun::where('periode', $periode)->first();

        if ($existingRun && $existingRun->status === 'finalized') {
            return response()->json([
                'message' => 'Payroll for this period is already finalized. Void it first.',
            ], 422);
        }

        CalculatePayrollJob::dispatch(
            $periode->format('Y-m-d'),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Payroll calculation dispatched.',
            'periode' => $periode->format('Y-m-d'),
        ], 202);
    }

    public function jobStatus(Request $request, string $jobId): JsonResponse
    {
        $run = PayrollRun::where('job_id', $jobId)->first();

        if (! $run) {
            return response()->json(['message' => 'Job not found.'], 404);
        }

        return response()->json([
            'job_id' => $run->job_id,
            'status' => $run->status,
            'periode' => $run->periode?->format('Y-m-d'),
        ]);
    }
}
