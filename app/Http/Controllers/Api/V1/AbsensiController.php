<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\KehadiranResource;
use App\Models\Kehadiran;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AbsensiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Kehadiran::with('pegawai');

        if ($request->filled('periode')) {
            $periode = Carbon::parse($request->input('periode'))->startOfMonth();
            $query->where('periode', $periode);
        }

        if ($request->filled('pegawai_id')) {
            $query->where('pegawai_id', $request->input('pegawai_id'));
        }

        $absensi = $query->orderBy('periode', 'desc')->paginate($request->integer('per_page', 15));

        return KehadiranResource::collection($absensi);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'periode' => 'required|date_format:Y-m-d',
            'hadir' => 'required|integer|min:0',
            'sakit' => 'required|integer|min:0',
            'alpha' => 'required|integer|min:0',
        ]);

        $periode = Carbon::parse($request->input('periode'))->startOfMonth();

        $absensi = Kehadiran::updateOrCreate(
            [
                'pegawai_id' => $request->input('pegawai_id'),
                'periode' => $periode,
            ],
            [
                'hadir' => $request->input('hadir'),
                'sakit' => $request->input('sakit'),
                'alpha' => $request->input('alpha'),
            ]
        );

        return (new KehadiranResource($absensi->load('pegawai')))
            ->response()
            ->setStatusCode($absensi->wasRecentlyCreated ? 201 : 200);
    }
}
