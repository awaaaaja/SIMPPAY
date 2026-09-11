<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    protected $table = 'payroll_details';

    protected $fillable = [
        'payroll_run_id',
        'pegawai_id',
        'gaji_pokok',
        'tj_transport',
        'uang_makan',
        'potongan_alpha',
        'total_tunjangan_tambahan',
        'total_potongan_tambahan',
        'honor_kelebihan_sks',
        'actual_sks_taught',
        'total_gaji',
        'breakdown_json',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'tj_transport' => 'decimal:2',
            'uang_makan' => 'decimal:2',
            'potongan_alpha' => 'decimal:2',
            'total_tunjangan_tambahan' => 'decimal:2',
            'total_potongan_tambahan' => 'decimal:2',
            'honor_kelebihan_sks' => 'decimal:2',
            'actual_sks_taught' => 'decimal:1',
            'total_gaji' => 'decimal:2',
            'breakdown_json' => 'array',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
