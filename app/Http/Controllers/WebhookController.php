<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAbsensiWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function absensi(Request $request): JsonResponse
    {
        $request->validate([
            'nik' => 'required|string|max:16',
            'periode' => 'required|date_format:Y-m-d',
            'hadir' => 'required|integer|min:0',
            'sakit' => 'required|integer|min:0',
            'alpha' => 'required|integer|min:0',
        ]);

        ProcessAbsensiWebhookJob::dispatch(
            $request->input('nik'),
            $request->input('periode'),
            $request->input('hadir'),
            $request->input('sakit'),
            $request->input('alpha'),
        );

        return response()->json(['message' => 'Webhook accepted.'], 202);
    }
}
