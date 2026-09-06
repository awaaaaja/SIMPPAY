<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Provider(['openrouter', 'openai'])]
class ReportGeneratorAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private string $periode,
        private array $dataLaporan,
    ) {}

    public function instructions(): string
    {
        return "Kamu adalah analis HR. Berikut data laporan gaji periode {$this->periode}. "
            .'Buat ringkasan naratif dalam bahasa Indonesia. '
            .'Sertakan insight tentang tren, distribusi per jabatan, dan hal menarik. '
            .'Gunakan format JSON sesuai schema yang didefinisikan.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ringkasan' => $schema->string()->required()->description('Ringkasan umum laporan gaji periode ini'),
            'insight' => $schema->array()->items($schema->string())->required()->description('Array insight atau temuan menarik'),
            'distribusi_per_jabatan' => $schema->array()->items(
                $schema->object()->properties([
                    'jabatan' => $schema->string(),
                    'total_gaji' => $schema->number(),
                    'jumlah_pegawai' => $schema->integer(),
                ])
            )->required()->description('Distribusi gaji per jabatan'),
        ];
    }
}
