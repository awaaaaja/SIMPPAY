<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SlipGajiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Jabatan $jabatan;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->jabatan = Jabatan::create([
            'nama_jabatan' => 'Staff Administrasi',
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'status' => 'aktif',
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createPegawaiForUser(User $user, string $name = 'Budi'): Pegawai
    {
        return Pegawai::create([
            'nik' => fake()->unique()->numerify('################'),
            'nama_pegawai' => $name,
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'user_id' => $user->id,
        ]);
    }

    private function createPayrollDetail(Pegawai $pegawai, string $periode = '2026-01'): PayrollDetail
    {
        $run = PayrollRun::firstOrCreate(
            ['periode' => Carbon::parse($periode)->startOfMonth()],
            ['status' => 'finalized', 'calculated_by' => 1]
        );

        return PayrollDetail::create([
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
    }

    #[Test]
    public function test_admin_can_print_own_slip_gaji(): void
    {
        $admin = $this->createUserWithRole('admin');
        $detail = $this->createPayrollDetail(
            Pegawai::create([
                'nik' => '1000000000000001',
                'nama_pegawai' => 'Admin User',
                'jenis_kelamin' => 'L',
                'status_pegawai' => 'aktif',
                'jabatan_id' => $this->jabatan->id,
                'user_id' => $admin->id,
            ])
        );

        $response = $this->actingAs($admin)
            ->get(route('admin.slip-gaji.pdf', $detail->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function test_admin_can_print_anyone_slip_gaji(): void
    {
        $admin = $this->createUserWithRole('admin');
        $pegawaiUser = $this->createUserWithRole('pegawai');
        $pegawai = $this->createPegawaiForUser($pegawaiUser, 'Budi');
        $detail = $this->createPayrollDetail($pegawai);

        $response = $this->actingAs($admin)
            ->get(route('admin.slip-gaji.pdf', $detail->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function test_bpsdm_can_print_anyone_slip_gaji(): void
    {
        $bpsdm = $this->createUserWithRole('bpsdm');
        $pegawaiUser = $this->createUserWithRole('pegawai');
        $pegawai = $this->createPegawaiForUser($pegawaiUser, 'Budi');
        $detail = $this->createPayrollDetail($pegawai);

        $response = $this->actingAs($bpsdm)
            ->get(route('bpsdm.slip-gaji.pdf', $detail->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function test_pegawai_can_print_own_slip_gaji_via_portal(): void
    {
        $pegawaiUser = $this->createUserWithRole('pegawai');
        $pegawai = $this->createPegawaiForUser($pegawaiUser, 'Budi');
        $detail = $this->createPayrollDetail($pegawai);

        $response = $this->actingAs($pegawaiUser)
            ->get(route('portal.slip-gaji.pdf', $detail->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function test_pegawai_cannot_print_other_pegawai_slip_gaji(): void
    {
        $pegawaiA = $this->createUserWithRole('pegawai');
        $pegA = $this->createPegawaiForUser($pegawaiA, 'Pegawai A');

        $pegawaiB = $this->createUserWithRole('pegawai');
        $pegB = $this->createPegawaiForUser($pegawaiB, 'Pegawai B');

        $detail = $this->createPayrollDetail($pegB);

        $response = $this->actingAs($pegawaiA)
            ->get(route('portal.slip-gaji.pdf', $detail->id));

        $response->assertForbidden();
    }

    #[Test]
    public function test_tendik_can_print_own_slip_gaji_via_portal(): void
    {
        $tendikUser = $this->createUserWithRole('tendik');
        $pegawai = $this->createPegawaiForUser($tendikUser, 'Tendik User');
        $detail = $this->createPayrollDetail($pegawai);

        $response = $this->actingAs($tendikUser)
            ->get(route('portal.slip-gaji.pdf', $detail->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function test_unauthenticated_user_cannot_print_slip_gaji(): void
    {
        $admin = $this->createUserWithRole('admin');
        $detail = $this->createPayrollDetail(
            Pegawai::create([
                'nik' => '1000000000000002',
                'nama_pegawai' => 'Admin User',
                'jenis_kelamin' => 'P',
                'status_pegawai' => 'aktif',
                'jabatan_id' => $this->jabatan->id,
                'user_id' => $admin->id,
            ])
        );

        $response = $this->get(route('admin.slip-gaji.pdf', $detail->id));

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_admin_can_print_laporan_gaji_pdf(): void
    {
        $admin = $this->createUserWithRole('admin');

        $pegawai = Pegawai::create([
            'nik' => '1000000000000003',
            'nama_pegawai' => 'Staff',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $this->createPayrollDetail($pegawai, '2026-01');

        $response = $this->actingAs($admin)
            ->get(route('admin.laporan-gaji.pdf', ['periode' => '2026-01']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function test_admin_can_print_laporan_absensi_pdf(): void
    {
        $admin = $this->createUserWithRole('admin');

        $pegawai = Pegawai::create([
            'nik' => '1000000000000004',
            'nama_pegawai' => 'Staff',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        Kehadiran::create([
            'pegawai_id' => $pegawai->id,
            'periode' => Carbon::parse('2026-01')->startOfMonth(),
            'hadir' => 20,
            'sakit' => 1,
            'alpha' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.laporan-absensi.pdf', ['periode' => '2026-01']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function test_bpsdm_can_print_laporan_gaji_pdf(): void
    {
        $bpsdm = $this->createUserWithRole('bpsdm');

        $pegawai = Pegawai::create([
            'nik' => '1000000000000005',
            'nama_pegawai' => 'Staff',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $this->createPayrollDetail($pegawai, '2026-02');

        $response = $this->actingAs($bpsdm)
            ->get(route('bpsdm.laporan-gaji.pdf', ['periode' => '2026-02']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
