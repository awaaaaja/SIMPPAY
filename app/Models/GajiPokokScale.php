<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GajiPokokScale extends Model
{
    protected $table = 'gaji_pokok_scale';

    protected $fillable = ['golongan_ruang_id', 'mkg', 'nominal'];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2'];
    }

    public function golonganRuang(): BelongsTo
    {
        return $this->belongsTo(GolonganRuang::class);
    }
}
