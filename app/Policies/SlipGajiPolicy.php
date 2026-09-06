<?php

namespace App\Policies;

use App\Models\PayrollDetail;
use App\Models\User;

class SlipGajiPolicy extends BasePolicy
{
    /**
     * Admin/bpsdm can print anyone's slip. Pegawai/tendik only themselves.
     */
    public function cetak(User $user, PayrollDetail $detail): bool
    {
        if ($user->hasAnyRole([$this->roleAdmin, $this->roleReadonly])) {
            return true;
        }

        // pegawai/tendik: must own the payroll detail
        return $user->pegawai->id === $detail->pegawai_id;
    }
}
