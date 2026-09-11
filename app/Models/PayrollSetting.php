<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PayrollSetting extends Model
{
    protected $table = 'payroll_settings';

    protected $fillable = [
        'tunjangan_makan_per_hari',
        'is_confirmed_tunjangan_makan',
        'lembur_basis_gaji_dasar',
        'is_confirmed_lembur_basis',
        'lembur_jam_kerja_sebulan',
        'is_confirmed_lembur_jam',
        'tunjangan_variabel_default_percentage',
        'is_confirmed_tunjangan_variabel',
        'bpjs_kesehatan_aktif',
        'is_confirmed_bpjs_kes',
        'bpjs_tk_aktif',
        'is_confirmed_bpjs_tk',
        'bpjs_tk_kategori_risiko_jkk',
        'is_confirmed_bpjs_tk_risiko',
        // UA-2025 Potongan columns
        'potongan_bpjs_kes_pct',
        'potongan_bpjs_tk_jkk_pct',
        'potongan_bpjs_tk_jkm_pct',
        'potongan_bpjs_tk_jht_pct',
        'potongan_bpjs_tk_jp_pct',
        'potongan_sosial_pct',
        'potongan_pendidikan_anak_pct',
        'is_confirmed_ua_potongan',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tunjangan_makan_per_hari' => 'decimal:2',
            'tunjangan_variabel_default_percentage' => 'decimal:2',
            'lembur_jam_kerja_sebulan' => 'integer',
            'bpjs_kesehatan_aktif' => 'boolean',
            'bpjs_tk_aktif' => 'boolean',
            'is_confirmed_tunjangan_makan' => 'boolean',
            'is_confirmed_lembur_basis' => 'boolean',
            'is_confirmed_lembur_jam' => 'boolean',
            'is_confirmed_tunjangan_variabel' => 'boolean',
            'is_confirmed_bpjs_kes' => 'boolean',
            'is_confirmed_bpjs_tk' => 'boolean',
            'is_confirmed_bpjs_tk_risiko' => 'boolean',
            // UA-2025 Potongan casts
            'potongan_bpjs_kes_pct' => 'decimal:2',
            'potongan_bpjs_tk_jkk_pct' => 'decimal:2',
            'potongan_bpjs_tk_jkm_pct' => 'decimal:2',
            'potongan_bpjs_tk_jht_pct' => 'decimal:2',
            'potongan_bpjs_tk_jp_pct' => 'decimal:2',
            'potongan_sosial_pct' => 'decimal:2',
            'potongan_pendidikan_anak_pct' => 'decimal:2',
            'is_confirmed_ua_potongan' => 'boolean',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs()
    {
        return $this->hasMany(PayrollSettingLog::class);
    }

    /**
     * Get the singleton instance. Creates one with defaults if none exists.
     * Cached for 1 hour — invalidated on update.
     */
    public static function instance(): self
    {
        $id = Cache::remember('payroll_settings_instance_id', 3600, function () {
            return static::firstOrCreate([], [
                'lembur_jam_kerja_sebulan' => 120,
                'is_confirmed_lembur_jam' => true,
                'lembur_basis_gaji_dasar' => 'gaji_pokok_saja',
                'is_confirmed_lembur_basis' => false,
                'tunjangan_variabel_default_percentage' => 100,
                'is_confirmed_tunjangan_variabel' => false,
                'tunjangan_makan_per_hari' => null,
                'is_confirmed_tunjangan_makan' => false,
                'bpjs_kesehatan_aktif' => true,
                'is_confirmed_bpjs_kes' => true,
                'bpjs_tk_aktif' => false,
                'is_confirmed_bpjs_tk' => false,
                'bpjs_tk_kategori_risiko_jkk' => null,
                'is_confirmed_bpjs_tk_risiko' => false,
            ])->id;
        });

        return static::findOrFail($id);
    }

    /**
     * Invalidate cache after update.
     */
    public static function clearCache(): void
    {
        Cache::forget('payroll_settings_instance_id');
    }
}
