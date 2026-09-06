<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    protected string $roleAdmin = 'admin';

    protected string $roleReadonly = 'bpsdm';

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([$this->roleAdmin, $this->roleReadonly]);
    }

    public function view(User $user, $model): bool
    {
        return $user->hasAnyRole([$this->roleAdmin, $this->roleReadonly]);
    }

    public function create(User $user): bool
    {
        return $user->hasRole($this->roleAdmin);
    }

    public function update(User $user, $model): bool
    {
        return $user->hasRole($this->roleAdmin);
    }

    public function delete(User $user, $model): bool
    {
        return $user->hasRole($this->roleAdmin);
    }
}
