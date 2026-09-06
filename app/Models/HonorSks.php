<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class HonorSks extends Model
{
    protected $table = 'honor_sks';

    protected $fillable = [
        'kategori_honor_sks_id',
        'honor',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'honor' => 'decimal:2',
        ];
    }

    /**
     * Update honor and log the change. Every update MUST produce an
     * HonorSksLog entry — this is an audit trail requirement (PRD FR-30).
     * No silent updates allowed.
     */
    public function updateHonor(float $newHonor, ?int $userId = null): self
    {
        $oldHonor = (float) $this->honor;

        if ($oldHonor === $newHonor) {
            return $this;
        }

        $this->update([
            'honor' => $newHonor,
            'last_updated_by' => $userId ?? Auth::id(),
        ]);

        HonorSksLog::create([
            'honor_sks_id' => $this->id,
            'honor_lama' => $oldHonor,
            'honor_baru' => $newHonor,
            'updated_by' => $userId ?? Auth::id(),
            'updated_at' => now(),
        ]);

        return $this->fresh();
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriHonorSks::class, 'kategori_honor_sks_id');
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
