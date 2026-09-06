<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kehadiran extends Model
{
    protected $table = 'kehadiran';

    protected $fillable = [
        'pegawai_id',
        'periode',
        'hadir',
        'sakit',
        'alpha',
    ];

    protected function casts(): array
    {
        return [
            'periode' => 'date',
            'hadir' => 'integer',
            'sakit' => 'integer',
            'alpha' => 'integer',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
