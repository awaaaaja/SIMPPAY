<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LevelStruktur extends Model
{
    protected $table = 'level_struktur';

    protected $fillable = ['kode', 'nama', 'urutan'];

    public function tunjanganTransportasiScale(): HasMany
    {
        return $this->hasMany(TunjanganTransportasiScale::class);
    }

    public function jabatanStrukturalPoin(): HasMany
    {
        return $this->hasMany(JabatanStrukturalPoin::class);
    }
}
