<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriHonorSks extends Model
{
    protected $table = 'kategori_honor_sks';

    protected $fillable = [
        'nama_kategori',
    ];

    public function honorSks(): HasMany
    {
        return $this->hasMany(HonorSks::class);
    }
}
