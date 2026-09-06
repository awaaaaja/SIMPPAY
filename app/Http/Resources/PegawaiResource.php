<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PegawaiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nik' => $this->nik,
            'nama_pegawai' => $this->nama_pegawai,
            'jenis_kelamin' => $this->jenis_kelamin,
            'status_pegawai' => $this->status_pegawai,
            'tanggal_masuk' => $this->tanggal_masuk?->format('Y-m-d'),
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'jabatan' => new JabatanResource($this->whenLoaded('jabatan')),
            'struktural' => new StrukturalResource($this->whenLoaded('struktural')),
            'fungsional' => new FungsionalResource($this->whenLoaded('fungsional')),
        ];
    }
}
