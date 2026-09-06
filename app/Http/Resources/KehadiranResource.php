<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KehadiranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pegawai_id' => $this->pegawai_id,
            'periode' => $this->periode?->format('Y-m-d'),
            'hadir' => $this->hadir,
            'sakit' => $this->sakit,
            'alpha' => $this->alpha,
            'pegawai' => new PegawaiResource($this->whenLoaded('pegawai')),
        ];
    }
}
