<?php

namespace Tests\Feature\Api;

use App\Jobs\CalculatePayrollJob;
use App\Models\Jabatan;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PenggajianApiTest extends TestCase
{
    use RefreshDatabase;

    private Jabatan $jabatan;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->jabatan = Jabatan::create([
            'nama_jabatan' => 'Dosen',
            'gaji_pokok' => 8000000,
            'tj_transport' => 1500000,
            'uang_makan' => 1000000,
            'status' => 'aktif',
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    #[Test]
    public function test_list_penggajian_with_token(): void
    {
        Sanctum::actingAs($this->admin, ['penggajian:read']);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $run = PayrollRun::create([
            'periode' => Carbon::create(2026, 1, 1),
            'status' => 'finalized',
            'calculated_by' => $this->admin->id,
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

        $response = $this->getJson('/api/v1/penggajian?periode=2026-01-01');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'total_gaji']]]);
    }

    #[Test]
    public function test_list_penggajian_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/penggajian');

        $response->assertUnauthorized();
    }

    #[Test]
    public function test_process_payroll_dispatches_job(): void
    {
        Sanctum::actingAs($this->admin, ['payroll:process']);

        Queue::fake();

        $response = $this->postJson('/api/v1/penggajian/process', [
            'periode' => '2026-03-01',
        ]);

        $response->assertAccepted();
        Queue::assertPushed(CalculatePayrollJob::class);
    }

    #[Test]
    public function test_process_payroll_unauthenticated(): void
    {
        $response = $this->postJson('/api/v1/penggajian/process', [
            'periode' => '2026-03-01',
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function test_process_payroll_without_scope_denied(): void
    {
        Sanctum::actingAs($this->admin, ['penggajian:read']);

        $response = $this->postJson('/api/v1/penggajian/process', [
            'periode' => '2026-03-01',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function test_process_payroll_already_finalized(): void
    {
        Sanctum::actingAs($this->admin, ['payroll:process']);

        PayrollRun::create([
            'periode' => Carbon::create(2026, 4, 1),
            'status' => 'finalized',
            'calculated_by' => $this->admin->id,
        ]);

        $response = $this->postJson('/api/v1/penggajian/process', [
            'periode' => '2026-04-01',
        ]);

        $response->assertUnprocessable();
    }
}
