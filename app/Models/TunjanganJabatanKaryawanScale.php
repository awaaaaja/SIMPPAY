<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TunjanganJabatanKaryawanScale extends Model
{
    protected $table = 'tunjangan_jabatan_karyawan_scale';

    protected $fillable = ['golongan_ruang_id', 'nominal'];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2'];
    }

    public function golonganRuang(): BelongsTo
    {
        return $this->belongsTo(GolonganRuang::class);
    }
}
