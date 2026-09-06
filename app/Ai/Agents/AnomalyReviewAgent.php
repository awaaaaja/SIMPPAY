<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Provider(['openrouter', 'openai'])]
class AnomalyReviewAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private string $namaPegawai,
        private string $jabatan,
        private float $nilaiSebelumnya,
        private float $nilaiSekarang,
        private float $persentaseDeviasi,
        private string $tipe,
    ) {}

    public function instructions(): string
    {
        return 'Kamu adalah analis HR. Tulis catatan naratif tentang anomali gaji berikut dalam bahasa Indonesia. '
            .'Fokus pada: ringkasan singkat, kemungkinan penyebab, dan rekomendasi tindakan. '
            .'Gunakan format JSON sesuai schema yang didefinisikan.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ringkasan' => $schema->string()->required()->description('Ringkasan singkat anomali yang terjadi'),
            'kemungkinan_penyebab' => $schema->string()->required()->description('Kemungkinan penyebab anomali'),
            'rekomendasi_tindakan' => $schema->string()->required()->description('Rekomendasi tindakan yang perlu diambil'),
        ];
    }
}
