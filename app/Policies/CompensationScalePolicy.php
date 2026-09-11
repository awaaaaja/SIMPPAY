<?php

namespace App\Policies;

use App\Models\User;

class CompensationScalePolicy
{
    /**
     * Kebijakan khusus area kompensasi — admin DAN bpsdm boleh update.
     * Ini pengecualian dari BasePolicy (bpsdm=read-only di semua modul lain).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'bpsdm']);
    }

    public function view(User $user, $model): bool
    {
        return $user->hasAnyRole(['admin', 'bpsdm']);
    }

    public function update(User $user, $model = null): bool
    {
        return $user->hasAnyRole(['admin', 'bpsdm']);
    }
}
