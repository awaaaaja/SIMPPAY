<?php

namespace Tests\Feature\Ai;

use App\Ai\Agents\HrChatbotAgent;
use App\Ai\Tools\GetRiwayatAbsensiPegawai;
use App\Ai\Tools\GetSlipGajiMilikSendiri;
use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HrChatbotAgentTest extends TestCase
{
    use RefreshDatabase;

    private Pegawai $pegawaiA;

    private Pegawai $pegawaiB;

    private User $userA;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Dosen',
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'status' => 'aktif',
        ]);

        $this->userA = User::factory()->create();
        $this->userA->assignRole('pegawai');

        $this->pegawaiA = Pegawai::create([
            'nik' => '1111111111111111',
            'nama_pegawai' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
            'user_id' => $this->userA->id,
        ]);

        $userB = User::factory()->create();
        $userB->assignRole('pegawai');

        $this->pegawaiB = Pegawai::create([
            'nik' => '2222222222222222',
            'nama_pegawai' => 'Andi Wijaya',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
            'user_id' => $userB->id,
        ]);
    }

    #[Test]
    public function test_slip_tool_returns_pegawai_a_data_not_pegawai_b(): void
    {
        $run = PayrollRun::create([
            'periode' => Carbon::create(2026, 1, 1),
            'status' => 'finalized',
            'calculated_by' => $this->userA->id,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $run->id,
            'pegawai_id' => $this->pegawaiA->id,
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 10500000,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $run->id,
            'pegawai_id' => $this->pegawaiB->id,
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 6500000,
        ]);

        $tool = new GetSlipGajiMilikSendiri($this->pegawaiA);
        $request = new Request(['periode' => '2026-01']);
        $result = $tool->handle($request);

        $this->assertStringContainsString('10.500.000', $result);
        $this->assertStringNotContainsString('6.500.000', $result);
    }

    #[Test]
    public function test_absensi_tool_returns_pegawai_a_data_not_pegawai_b(): void
    {
        $periode = Carbon::create(2026, 1, 1);

        Kehadiran::create([
            'pegawai_id' => $this->pegawaiA->id,
            'periode' => $periode,
            'hadir' => 22,
            'sakit' => 1,
            'alpha' => 1,
        ]);

        Kehadiran::create([
            'pegawai_id' => $this->pegawaiB->id,
            'periode' => $periode,
            'hadir' => 20,
            'sakit' => 0,
            'alpha' => 2,
        ]);

        $tool = new GetRiwayatAbsensiPegawai($this->pegawaiA);
        $request = new Request(['bulan' => '2026-01']);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Hadir 22', $result);
        $this->assertStringNotContainsString('Hadir 20', $result);
    }

    #[Test]
    public function test_agent_instructions_reference_correct_pegawai(): void
    {
        $agent = new HrChatbotAgent($this->pegawaiA);

        $this->assertStringContainsString('Budi Santoso', $agent->instructions());
        $this->assertStringNotContainsString('Andi Wijaya', $agent->instructions());
    }

    #[Test]
    public function test_agent_tools_count_and_types(): void
    {
        $agent = new HrChatbotAgent($this->pegawaiA);

        $tools = iterator_to_array($agent->tools());

        $this->assertCount(2, $tools);
        $this->assertInstanceOf(GetSlipGajiMilikSendiri::class, $tools[0]);
        $this->assertInstanceOf(GetRiwayatAbsensiPegawai::class, $tools[1]);
    }

    #[Test]
    public function test_hr_chatbot_requires_auth(): void
    {
        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'Berapa gaji saya?',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function test_hr_chatbot_controller_no_pegawai_linked(): void
    {
        $userNoPegawai = User::factory()->create();
        $userNoPegawai->assignRole('pegawai');

        Sanctum::actingAs($userNoPegawai);

        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'Berapa gaji saya?',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Akun Anda belum terkait dengan data pegawai.',
        ]);
    }
}
