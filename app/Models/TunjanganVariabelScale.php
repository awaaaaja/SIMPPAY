<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TunjanganVariabelScale extends Model
{
    protected $table = 'tunjangan_variabel_scale';

    protected $fillable = ['golongan_ruang_id', 'nominal_maksimum'];

    protected function casts(): array
    {
        return ['nominal_maksimum' => 'decimal:2'];
    }

    public function golonganRuang(): BelongsTo
    {
        return $this->belongsTo(GolonganRuang::class);
    }
}
