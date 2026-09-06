<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenSks extends Model
{
    protected $table = 'dosen_sks';

    protected $fillable = [
        'pegawai_id',
        'tahun_akademik_id',
        'sks_maksimal',
        'sks_terpakai',
        'sks_beban',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class);
    }
}
