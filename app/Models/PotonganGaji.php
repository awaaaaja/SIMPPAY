<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PotonganGaji extends Model
{
    protected $table = 'potongan_gaji';

    protected $fillable = [
        'nama_potongan',
        'tipe',
        'nilai',
        'is_alpha_penalty',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'is_alpha_penalty' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    /**
     * Enforce singleton is_alpha_penalty: when saving a record with
     * is_alpha_penalty=true, unset all other alpha penalty records.
     * This prevents ambiguity in PayrollService — only one active
     * alpha penalty source is allowed at any time.
     *
     * Design decision (Sprint 4): auto-unset others instead of
     * rejecting the save, because it's more user-friendly when
     * admin toggles between records.
     */
    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->is_alpha_penalty) {
                self::where('is_alpha_penalty', true)
                    ->where('id', '!=', $model->id)
                    ->update(['is_alpha_penalty' => false]);
            }
        });
    }
}
