<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TunjanganFungsionalDosenScale extends Model
{
    protected $table = 'tunjangan_fungsional_dosen_scale';

    protected $fillable = ['jabatan_fungsional', 'angka_kredit', 'pangkat', 'golongan_ruang', 'nominal'];

    protected function casts(): array
    {
        return ['nominal' => 'decimal:2'];
    }
}
