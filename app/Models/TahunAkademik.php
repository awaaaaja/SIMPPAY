<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TahunAkademik extends Model
{
    protected $table = 'tahun_akademik';

    protected $fillable = [
        'kode_tahun',
        'tahun',
        'semester',
        'tanggal_mulai',
        'tanggal_berakhir',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_berakhir' => 'date',
            'aktif' => 'boolean',
        ];
    }

    /**
     * Activate this tahun akademik. Deactivates all others (service-enforced
     * singleton: only 1 active at a time). Then seeds default DosenSks for
     * all active dosen.
     */
    public function aktifkan(): self
    {
        return DB::transaction(function () {
            // Deactivate all others
            static::query()->where('aktif', true)->update(['aktif' => false]);

            $this->update(['aktif' => true]);

            // Seed default SKS for all active dosen, inheriting from previous tahun akademik
            $pegawais = Pegawai::where('status_pegawai', 'aktif')
                ->where('status_dosen', 'dosen')
                ->get();

            $prevTa = static::query()->where('id', '!=', $this->id)->latest()->first();

            foreach ($pegawais as $pegawai) {
                $defaultMaksimal = 12;
                if ($prevTa) {
                    $prevSks = DosenSks::where('pegawai_id', $pegawai->id)
                        ->where('tahun_akademik_id', $prevTa->id)
                        ->first();
                    if ($prevSks) {
                        $defaultMaksimal = $prevSks->sks_maksimal;
                    }
                }

                DosenSks::firstOrCreate([
                    'pegawai_id' => $pegawai->id,
                    'tahun_akademik_id' => $this->id,
                ], [
                    'sks_maksimal' => $defaultMaksimal,
                    'sks_terpakai' => 0,
                    'sks_beban' => 0,
                ]);
            }

            return $this->fresh();
        });
    }

    public function dosenSks(): HasMany
    {
        return $this->hasMany(DosenSks::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
