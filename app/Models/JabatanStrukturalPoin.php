<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JabatanStrukturalPoin extends Model
{
    protected $table = 'jabatan_struktural_poin';

    protected $fillable = ['level_struktur_id', 'nama_jabatan', 'total_poin', 'klasifikasi_id', 'tunjangan_baru'];

    protected function casts(): array
    {
        return ['tunjangan_baru' => 'decimal:2'];
    }

    public function levelStruktur(): BelongsTo
    {
        return $this->belongsTo(LevelStruktur::class);
    }

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiJabatanStruktural::class, 'klasifikasi_id');
    }
}
