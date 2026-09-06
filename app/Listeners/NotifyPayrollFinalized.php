<?php

namespace App\Listeners;

use App\Events\PayrollFinalized;
use Illuminate\Support\Facades\Log;

class NotifyPayrollFinalized
{
    public function handle(PayrollFinalized $event): void
    {
        Log::info('Payroll run finalized', [
            'payroll_run_id' => $event->payrollRun->id,
            'periode' => $event->payrollRun->periode->format('Y-m'),
            'finalized_by' => $event->payrollRun->finalized_by,
        ]);
    }
}
