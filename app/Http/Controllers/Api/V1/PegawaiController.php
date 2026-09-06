<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PegawaiResource;
use App\Models\Pegawai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;

class PegawaiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pegawai::with(['jabatan', 'struktural', 'fungsional']);

        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->input('jabatan_id'));
        }

        if ($request->filled('status_pegawai')) {
            $query->where('status_pegawai', $request->input('status_pegawai'));
        }

        $pegawais = $query->orderBy('nama_pegawai')->paginate($request->integer('per_page', 15));

        return PegawaiResource::collection($pegawais);
    }

    public function show(Pegawai $pegawai): PegawaiResource
    {
        $pegawai->load(['jabatan', 'struktural', 'fungsional']);

        return new PegawaiResource($pegawai);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:16|unique:pegawai,nik',
            'nama_pegawai' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan_id' => 'required|exists:jabatan,id',
            'status_pegawai' => 'required|in:aktif,nonaktif',
            'tanggal_masuk' => 'nullable|date',
            'email' => 'nullable|email|max:100',
            'no_hp' => 'nullable|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $pegawai = Pegawai::create($validator->validated());

        return (new PegawaiResource($pegawai->load(['jabatan'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Pegawai $pegawai): PegawaiResource
    {
        $validator = Validator::make($request->all(), [
            'nama_pegawai' => 'sometimes|string|max:100',
            'jenis_kelamin' => 'sometimes|in:L,P',
            'jabatan_id' => 'sometimes|exists:jabatan,id',
            'status_pegawai' => 'sometimes|in:aktif,nonaktif',
            'email' => 'nullable|email|max:100',
            'no_hp' => 'nullable|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $pegawai->update($validator->validated());

        return new PegawaiResource($pegawai->fresh(['jabatan', 'struktural', 'fungsional']));
    }
}
