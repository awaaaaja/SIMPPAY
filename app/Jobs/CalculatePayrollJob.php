<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculatePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public string $periode,
        public int $userId,
        public string $formulaVersion = 'legacy',
    ) {}

    public function handle(PayrollService $service): void
    {
        $user = User::findOrFail($this->userId);
        $periode = Carbon::parse($this->periode)->startOfMonth();

        $service->calculateBatch($periode, $user, $this->formulaVersion);
    }
}
