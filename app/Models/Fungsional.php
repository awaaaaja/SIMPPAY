<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fungsional extends Model
{
    protected $table = 'fungsional';

    protected $fillable = [
        'nama_fungsional',
        'angka_kredit',
        'pangkat',
        'golongan',
    ];

    protected function casts(): array
    {
        return [
            'angka_kredit' => 'decimal:2',
        ];
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }
}
