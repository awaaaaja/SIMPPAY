<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSettingLog extends Model
{
    protected $table = 'payroll_settings_log';

    protected $fillable = [
        'field_name',
        'nilai_lama',
        'nilai_baru',
        'updated_by',
        'role',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
