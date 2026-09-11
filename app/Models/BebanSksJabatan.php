<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BebanSksJabatan extends Model
{
    protected $table = 'beban_sks_jabatan';

    protected $fillable = ['nama_jabatan', 'jabatan_struktural_poin_id', 'sks_perkuliahan', 'sks_penelitian', 'sks_adm', 'sks_jabatan', 'total'];

    public function jabatanStrukturalPoin(): BelongsTo
    {
        return $this->belongsTo(JabatanStrukturalPoin::class);
    }
}
