<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TunjanganVariabelScaleLog extends Model
{
    protected $table = 'tunjangan_variabel_scale_log';

    protected $fillable = [
        'scale_id',
        'golongan_ruang_id',
        'nominal_lama',
        'nominal_baru',
        'updated_by',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'nominal_lama' => 'decimal:2',
            'nominal_baru' => 'decimal:2',
        ];
    }

    public function scale(): BelongsTo
    {
        return $this->belongsTo(TunjanganVariabelScale::class, 'scale_id');
    }

    public function golonganRuang(): BelongsTo
    {
        return $this->belongsTo(GolonganRuang::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
