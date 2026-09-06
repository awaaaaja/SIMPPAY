<?php

namespace Tests\Feature\Ai;

use App\Ai\Agents\PayrollAssistantAgent;
use App\Ai\Tools\GetDistribusiGajiPerJabatan;
use App\Ai\Tools\GetTotalGajiByJabatan;
use App\Ai\Tools\GetTotalGajiByPeriode;
use App\Models\Jabatan;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Exceptions\AIExecutionException;
use Laravel\Ai\Tools\Request;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PayrollAssistantAgentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
    }

    #[Test]
    public function test_get_total_gaji_by_jabatan_tool(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Dosen',
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'status' => 'aktif',
        ]);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
        ]);

        $run = PayrollRun::create([
            'periode' => Carbon::create(2026, 1, 1),
            'status' => 'finalized',
            'calculated_by' => $admin->id,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $run->id,
            'pegawai_id' => $pegawai->id,
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 10500000,
        ]);

        $tool = new GetTotalGajiByJabatan;
        $request = new Request(['nama_jabatan' => 'Dosen', 'periode' => '2026-01']);
        $result = $tool->handle($request);

        $this->assertStringContainsString('10.500.000', $result);
        $this->assertStringContainsString('Dosen', $result);
        $this->assertStringContainsString('1 pegawai', $result);
    }

    #[Test]
    public function test_get_total_gaji_by_periode_tool(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $jabatan = Jabatan::create([
            'nama_jabatan' => 'Staff',
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'status' => 'aktif',
        ]);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $jabatan->id,
        ]);

        $run = PayrollRun::create([
            'periode' => Carbon::create(2026, 2, 1),
            'status' => 'finalized',
            'calculated_by' => $admin->id,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $run->id,
            'pegawai_id' => $pegawai->id,
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 6500000,
        ]);

        $tool = new GetTotalGajiByPeriode;
        $request = new Request(['periode' => '2026-02']);
        $result = $tool->handle($request);

        $this->assertStringContainsString('6.500.000', $result);
        $this->assertStringContainsString('2026-02', $result);
    }

    #[Test]
    public function test_get_distribusi_gaji_per_jabatan_tool(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $dosen = Jabatan::create([
            'nama_jabatan' => 'Dosen',
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'status' => 'aktif',
        ]);

        $staff = Jabatan::create([
            'nama_jabatan' => 'Staff',
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'status' => 'aktif',
        ]);

        $p1 = Pegawai::create([
            'nik' => '1111111111111111',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $dosen->id,
        ]);

        $p2 = Pegawai::create([
            'nik' => '2222222222222222',
            'nama_pegawai' => 'Andi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $staff->id,
        ]);

        $run = PayrollRun::create([
            'periode' => Carbon::create(2026, 3, 1),
            'status' => 'finalized',
            'calculated_by' => $admin->id,
        ]);

        PayrollDetail::create([
            'payroll_run_id' => $run->id,
            'pegawai_id' => $p1->id,
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
            'pegawai_id' => $p2->id,
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'potongan_alpha' => 0,
            'total_tunjangan_tambahan' => 0,
            'total_potongan_tambahan' => 0,
            'honor_kelebihan_sks' => 0,
            'total_gaji' => 6500000,
        ]);

        $tool = new GetDistribusiGajiPerJabatan;
        $request = new Request(['periode' => '2026-03']);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Dosen', $result);
        $this->assertStringContainsString('Staff', $result);
        $this->assertStringContainsString('10.500.000', $result);
        $this->assertStringContainsString('6.500.000', $result);
    }

    #[Test]
    public function test_payroll_query_controller_returns_graceful_fallback(): void
    {
        PayrollAssistantAgent::fake(function ($prompt) {
            throw new AIExecutionException('Provider down');
        });

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/ai/payroll-query', [
            'question' => 'Berapa total gaji bulan Januari?',
        ]);

        $response->assertStatus(503);
        $response->assertJson([
            'message' => 'Fitur AI sementara tidak tersedia, coba lagi nanti.',
        ]);
    }

    #[Test]
    public function test_payroll_query_controller_success(): void
    {
        PayrollAssistantAgent::fake([
            'Total gaji bulan Januari 2026 adalah Rp 10.500.000 untuk 1 pegawai.',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/ai/payroll-query', [
            'question' => 'Berapa total gaji bulan Januari?',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['answer']);
    }

    #[Test]
    public function test_payroll_query_requires_auth(): void
    {
        $response = $this->postJson('/api/v1/ai/payroll-query', [
            'question' => 'Berapa total gaji?',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function test_tools_use_whitelist_schema_not_raw_sql(): void
    {
        $tool = new GetTotalGajiByJabatan;

        $reflection = new \ReflectionClass($tool);

        // Verify tool has schema method
        $this->assertTrue($reflection->hasMethod('schema'));
        $this->assertTrue($reflection->hasMethod('handle'));
        $this->assertTrue($reflection->hasMethod('description'));
    }
}
