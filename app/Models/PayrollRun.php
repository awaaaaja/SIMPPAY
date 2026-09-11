<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $table = 'payroll_runs';

    protected $fillable = [
        'job_id',
        'periode',
        'status',
        'formula_version',
        'calculated_by',
        'calculated_at',
        'finalized_by',
        'finalized_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'periode' => 'date',
            'calculated_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }
}
