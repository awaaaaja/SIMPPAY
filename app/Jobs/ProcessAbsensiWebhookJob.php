<?php

namespace App\Jobs;

use App\Models\Kehadiran;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAbsensiWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $nik,
        public string $periode,
        public int $hadir,
        public int $sakit,
        public int $alpha,
    ) {}

    public function handle(): void
    {
        $pegawai = Pegawai::where('nik', $this->nik)->firstOrFail();

        $periode = Carbon::parse($this->periode)->startOfMonth();

        Kehadiran::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'periode' => $periode,
            ],
            [
                'hadir' => $this->hadir,
                'sakit' => $this->sakit,
                'alpha' => $this->alpha,
            ]
        );
    }
}
