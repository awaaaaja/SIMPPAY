<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GolonganRuang extends Model
{
    protected $table = 'golongan_ruang';

    protected $fillable = ['kode', 'nama', 'urutan'];

    public function gajiPokokScale(): HasMany
    {
        return $this->hasMany(GajiPokokScale::class);
    }

    public function tunjanganJabatanKaryawanScale(): HasMany
    {
        return $this->hasMany(TunjanganJabatanKaryawanScale::class);
    }

    public function tunjanganVariabelScale(): HasMany
    {
        return $this->hasMany(TunjanganVariabelScale::class);
    }
}
