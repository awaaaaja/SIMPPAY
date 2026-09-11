<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KlasifikasiJabatanStruktural extends Model
{
    protected $table = 'klasifikasi_jabatan_struktural';

    protected $fillable = ['klasifikasi', 'poin_min', 'poin_max', 'level_jabatan', 'tunjangan_min', 'tunjangan_max'];

    protected function casts(): array
    {
        return [
            'tunjangan_min' => 'decimal:2',
            'tunjangan_max' => 'decimal:2',
        ];
    }

    public function jabatanStrukturalPoin(): HasMany
    {
        return $this->hasMany(JabatanStrukturalPoin::class, 'klasifikasi_id');
    }
}
