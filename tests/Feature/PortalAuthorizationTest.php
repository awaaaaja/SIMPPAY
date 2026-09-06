<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortalAuthorizationTest extends TestCase
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

    private function createPegawaiForUser(User $user): Pegawai
    {
        return Pegawai::create([
            'nik' => fake()->unique()->numerify('################'),
            'nama_pegawai' => 'Test User',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function test_pegawai_can_access_portal_dashboard(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertOk();
    }

    #[Test]
    public function test_tendik_can_access_portal_dashboard(): void
    {
        $user = $this->createUserWithRole('tendik');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertOk();
    }

    #[Test]
    public function test_pegawai_cannot_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    #[Test]
    public function test_pegawai_cannot_access_bpsdm_dashboard(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('bpsdm.dashboard'));

        $response->assertForbidden();
    }

    #[Test]
    public function test_tendik_cannot_access_admin_dashboard(): void
    {
        $user = $this->createUserWithRole('tendik');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    #[Test]
    public function test_tendik_cannot_access_bpsdm_dashboard(): void
    {
        $user = $this->createUserWithRole('tendik');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('bpsdm.dashboard'));

        $response->assertForbidden();
    }

    #[Test]
    public function test_admin_cannot_access_portal_dashboard(): void
    {
        $user = $this->createUserWithRole('admin');
        Pegawai::create([
            'nik' => '1000000000000001',
            'nama_pegawai' => 'Admin User',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertForbidden();
    }

    #[Test]
    public function test_bpsdm_cannot_access_portal_dashboard(): void
    {
        $user = $this->createUserWithRole('bpsdm');
        Pegawai::create([
            'nik' => '1000000000000002',
            'nama_pegawai' => 'BPSDM User',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertForbidden();
    }

    #[Test]
    public function test_pegawai_can_access_slip_gaji_page(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('portal.slip.gaji'));

        $response->assertOk();
    }

    #[Test]
    public function test_pegawai_can_access_profile_page(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('portal.profile'));

        $response->assertOk();
    }

    #[Test]
    public function test_pegawai_cannot_access_admin_payroll_run(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('admin.payroll-run.index'));

        $response->assertForbidden();
    }

    #[Test]
    public function test_pegawai_cannot_access_admin_pegawai_resource(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->createPegawaiForUser($user);

        $response = $this->actingAs($user)->get(route('admin.pegawai.index'));

        $response->assertForbidden();
    }
}
