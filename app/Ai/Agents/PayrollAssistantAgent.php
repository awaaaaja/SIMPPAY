<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetDistribusiGajiPerJabatan;
use App\Ai\Tools\GetTotalGajiByJabatan;
use App\Ai\Tools\GetTotalGajiByPeriode;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

#[Provider(['openrouter', 'openai'])]
class PayrollAssistantAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'Kamu asisten data payroll untuk Admin SIMPPAY. Jawab HANYA berdasarkan hasil pemanggilan tools yang tersedia. Jangan mengarang angka. Selalu sebutkan periode data yang dipakai. Gunakan bahasa Indonesia yang jelas dan profesional.';
    }

    public function tools(): iterable
    {
        return [
            new GetTotalGajiByJabatan,
            new GetTotalGajiByPeriode,
            new GetDistribusiGajiPerJabatan,
        ];
    }
}
