<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JabatanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_jabatan' => $this->nama_jabatan,
            'gaji_pokok' => $this->gaji_pokok,
            'tj_transport' => $this->tj_transport,
            'uang_makan' => $this->uang_makan,
        ];
    }
}
