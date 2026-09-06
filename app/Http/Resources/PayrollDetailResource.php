<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pegawai_id' => $this->pegawai_id,
            'gaji_pokok' => $this->gaji_pokok,
            'tj_transport' => $this->tj_transport,
            'uang_makan' => $this->uang_makan,
            'potongan_alpha' => $this->potongan_alpha,
            'total_tunjangan_tambahan' => $this->total_tunjangan_tambahan,
            'total_potongan_tambahan' => $this->total_potongan_tambahan,
            'honor_kelebihan_sks' => $this->honor_kelebihan_sks,
            'total_gaji' => $this->total_gaji,
            'breakdown_json' => $this->breakdown_json,
            'payroll_run' => new PayrollRunResource($this->whenLoaded('payrollRun')),
            'pegawai' => new PegawaiResource($this->whenLoaded('pegawai')),
        ];
    }
}
