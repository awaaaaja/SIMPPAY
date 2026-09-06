<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jabatan extends Model
{
    use SoftDeletes;

    protected $table = 'jabatan';

    protected $fillable = [
        'nama_jabatan',
        'gaji_pokok',
        'tj_transport',
        'uang_makan',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'tj_transport' => 'decimal:2',
            'uang_makan' => 'decimal:2',
        ];
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }

    public function activePegawai(): HasMany
    {
        return $this->pegawai()->where('status_pegawai', 'aktif');
    }
}
