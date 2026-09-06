<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HonorSksLog extends Model
{
    protected $table = 'honor_sks_log';

    protected $fillable = [
        'honor_sks_id',
        'honor_lama',
        'honor_baru',
        'updated_by',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'honor_lama' => 'decimal:2',
            'honor_baru' => 'decimal:2',
            'updated_at' => 'datetime',
        ];
    }

    public function honorSks(): BelongsTo
    {
        return $this->belongsTo(HonorSks::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
