<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorSksScale extends Model
{
    protected $table = 'honor_sks_scale';

    protected $fillable = ['program', 'status_dosen', 'strata', 'nilai_sks'];

    protected function casts(): array
    {
        return ['nilai_sks' => 'decimal:2'];
    }
}
