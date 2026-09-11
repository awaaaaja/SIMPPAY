<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawai extends Model
{
    use SoftDeletes;

    protected $table = 'pegawai';

    protected $fillable = [
        'user_id',
        'jabatan_id',
        'struktural_id',
        'jabatan_struktural_poin_id',
        'fungsional_id',
        'golongan_ruang_id',
        'nik',
        'nama_pegawai',
        'jenis_kelamin',
        'tanggal_masuk',
        'tmt',
        'status_pegawai',
        'photo',
        'ktp',
        'nidn',
        'id_ptk',
        'nuptk',
        'email',
        'agama',
        'kewarganegaraan',
        'suku',
        'alamat',
        'tgl_lahir',
        'no_hp',
        'jurusan',
        'bidang_keahlian',
        'no_sk',
        'tgl_sk',
        'foto_sk',
        'status_dosen',
        'strata_pendidikan',
        'program_mengajar',
        'ikatan_kerja',
        'status_kawin',
        'nama_sm',
        'nip_sm',
        'nohp_sm',
        'pekerjaan_sm',
        'nama_ibu',
        'masa_jabatan',
        'tgl_sk_jabatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tmt' => 'date',
            'tgl_lahir' => 'date',
            'tgl_sk' => 'date',
            'tgl_sk_jabatan' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function struktural(): BelongsTo
    {
        return $this->belongsTo(Struktural::class);
    }

    public function fungsional(): BelongsTo
    {
        return $this->belongsTo(Fungsional::class);
    }

    public function golonganRuang(): BelongsTo
    {
        return $this->belongsTo(GolonganRuang::class);
    }

    public function jabatanStrukturalPoin(): BelongsTo
    {
        return $this->belongsTo(JabatanStrukturalPoin::class);
    }

    public function anak(): HasMany
    {
        return $this->hasMany(PegawaiAnak::class);
    }

    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class);
    }
}
