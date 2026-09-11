<?php

namespace App\Policies;

use App\Models\User;

class PayrollSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'bpsdm']);
    }

    public function update(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'bpsdm']);
    }
}
