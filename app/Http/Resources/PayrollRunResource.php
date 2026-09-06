<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'periode' => $this->periode?->format('Y-m-d'),
            'status' => $this->status,
            'calculated_at' => $this->calculated_at?->toIso8601String(),
            'finalized_at' => $this->finalized_at?->toIso8601String(),
        ];
    }
}
