<?php

namespace App\Policies;

use App\Models\Pegawai;
use App\Models\User;

class PegawaiPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        // admin + bpsdm can view all; pegawai/tendik can view own
        return true;
    }

    public function view(User $user, $model): bool
    {
        if ($user->hasAnyRole([$this->roleAdmin, $this->roleReadonly])) {
            return true;
        }

        // pegawai/tendik hanya lihat data sendiri
        return $user->pegawai?->id === $model->id;
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
