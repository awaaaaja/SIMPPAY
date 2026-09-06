<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\HrChatbotAgent;
use App\Ai\Agents\PayrollAssistantAgent;
use App\Ai\Agents\ReportGeneratorAgent;
use App\Http\Controllers\Controller;
use App\Models\PayrollAnomaly;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Exceptions\AIExecutionException;

class AiController extends Controller
{
    public function payrollQuery(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        try {
            $agent = new PayrollAssistantAgent;
            $response = $agent->prompt($request->input('question'));

            return response()->json([
                'answer' => (string) $response,
            ]);
        } catch (AIExecutionException $e) {
            report($e);

            return response()->json([
                'message' => 'Fitur AI sementara tidak tersedia, coba lagi nanti.',
            ], 503);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Fitur AI sementara tidak tersedia, coba lagi nanti.',
            ], 503);
        }
    }

    public function anomalies(Request $request): JsonResponse
    {
        try {
            $anomalies = PayrollAnomaly::with('payrollDetail.pegawai.jabatan')
                ->where('status_review', 'pending')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();

            return response()->json([
                'data' => $anomalies,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Gagal mengambil data anomali.',
            ], 500);
        }
    }

    public function report(Request $request, PayrollService $payrollService): JsonResponse
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m-d',
        ]);

        try {
            $periode = Carbon::parse($request->input('periode'))->startOfMonth();

            $run = PayrollRun::where('periode', $periode)->where('status', 'finalized')->first();

            if (! $run) {
                return response()->json([
                    'message' => 'Data payroll final untuk periode ini belum tersedia.',
                ], 404);
            }

            $details = PayrollDetail::where('payroll_run_id', $run->id)
                ->with('pegawai.jabatan')
                ->get()
                ->map(fn ($d) => [
                    'nama' => $d->pegawai->nama_pegawai ?? '-',
                    'jabatan' => $d->pegawai->jabatan->nama_jabatan ?? '-',
                    'total_gaji' => $d->total_gaji,
                ])
                ->toArray();

            $agent = new ReportGeneratorAgent($periode->format('Y-m'), $details);
            $result = $agent->prompt("Buatkan ringkasan laporan gaji periode {$periode->format('Y-m')}.");

            return response()->json([
                'data' => $result->toArray(),
            ]);
        } catch (AIExecutionException $e) {
            report($e);

            return response()->json([
                'message' => 'Fitur AI sementara tidak tersedia, coba lagi nanti.',
            ], 503);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Fitur AI sementara tidak tersedia, coba lagi nanti.',
            ], 503);
        }
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        try {
            $user = $request->user();
            $pegawai = $user->pegawai;

            if (! $pegawai) {
                return response()->json([
                    'message' => 'Akun Anda belum terkait dengan data pegawai.',
                ], 404);
            }

            $agent = (new HrChatbotAgent($pegawai))
                ->forUser($user);

            $response = $agent->prompt($request->input('message'));

            return response()->json([
                'answer' => (string) $response,
            ]);
        } catch (AIExecutionException $e) {
            report($e);

            return response()->json([
                'message' => 'Fitur AI sementara tidak tersedia, coba lagi nanti.',
            ], 503);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Fitur AI sementara tidak tersedia, coba lagi nanti.',
            ], 503);
        }
    }
}
