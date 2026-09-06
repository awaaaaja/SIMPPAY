<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetRiwayatAbsensiPegawai;
use App\Ai\Tools\GetSlipGajiMilikSendiri;
use App\Models\Pegawai;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations as RemembersConversationsTrait;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\RemembersConversations;
use Laravel\Ai\Promptable;

#[Provider(['openrouter', 'openai'])]
class HrChatbotAgent implements Agent, HasTools, RemembersConversations
{
    use Promptable, RemembersConversationsTrait;

    public function __construct(private Pegawai $pegawai) {}

    public function instructions(): string
    {
        return "Kamu asisten HR untuk pegawai {$this->pegawai->nama_pegawai}. "
            .'Kamu HANYA boleh menjawab tentang data milik pegawai ini sendiri. '
            .'Tolak dengan sopan permintaan data pegawai lain. '
            .'Gunakan bahasa Indonesia yang ramah dan profesional.';
    }

    public function tools(): iterable
    {
        return [
            new GetSlipGajiMilikSendiri($this->pegawai),
            new GetRiwayatAbsensiPegawai($this->pegawai),
        ];
    }
}
