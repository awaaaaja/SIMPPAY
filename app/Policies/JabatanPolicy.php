<?php

namespace App\Policies;

use App\Models\User;

class JabatanPolicy extends BasePolicy
{
    public function delete(User $user, $model): bool
    {
        return $user->hasRole($this->roleAdmin);
    }
}
