<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PegawaiAnak extends Model
{
    protected $table = 'pegawai_anak';

    protected $fillable = [
        'pegawai_id',
        'nama_anak',
        'tempat_tanggal_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'anak_ke',
        'pekerjaan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
