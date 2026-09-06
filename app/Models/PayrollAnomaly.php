<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAnomaly extends Model
{
    protected $table = 'payroll_anomalies';

    protected $fillable = [
        'payroll_detail_id',
        'tipe',
        'nilai_sebelumnya',
        'nilai_sekarang',
        'persentase_deviasi',
        'catatan_ai',
        'status_review',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_sebelumnya' => 'decimal:2',
            'nilai_sekarang' => 'decimal:2',
            'persentase_deviasi' => 'decimal:2',
        ];
    }

    public function payrollDetail(): BelongsTo
    {
        return $this->belongsTo(PayrollDetail::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
