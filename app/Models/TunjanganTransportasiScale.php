<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TunjanganTransportasiScale extends Model
{
    protected $table = 'tunjangan_transportasi_scale';

    protected $fillable = ['level_struktur_id', 'keterangan', 'golongan_range', 'nominal'];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2'];
    }

    public function levelStruktur(): BelongsTo
    {
        return $this->belongsTo(LevelStruktur::class);
    }
}
