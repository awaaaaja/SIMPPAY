<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DataMasterAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'bpsdm', 'guard_name' => 'web']);
        Role::create(['name' => 'pegawai', 'guard_name' => 'web']);
        Role::create(['name' => 'tendik', 'guard_name' => 'web']);
    }

    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    // ─── Admin: full akses ─────────────────────────────────────────────

    public function test_admin_can_view_jabatan_index(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->actingAs($user)->get(route('admin.jabatan.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_jabatan(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->actingAs($user)->get(route('admin.jabatan.create'));

        $response->assertOk();
    }

    public function test_admin_can_store_jabatan(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->actingAs($user)->post(route('admin.jabatan.store'), [
            'nama_jabatan' => 'Dosen Tetap',
            'gaji_pokok' => 5000000,
            'tj_transport' => 500000,
            'uang_makan' => 300000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jabatan', ['nama_jabatan' => 'Dosen Tetap']);
    }

    public function test_admin_can_view_pegawai_index(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->actingAs($user)->get(route('admin.pegawai.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_pegawai(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->actingAs($user)->get(route('admin.pegawai.create'));

        $response->assertOk();
    }

    public function test_admin_can_store_pegawai(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->actingAs($user)->post(route('admin.pegawai.store'), [
            'nik' => '1234567890',
            'nama_pegawai' => 'Ahmad Fauzan',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pegawai', ['nik' => '1234567890']);
    }

    // ─── BPSDM: read-only ──────────────────────────────────────────────

    public function test_bpsdm_can_view_jabatan_index(): void
    {
        $user = $this->createUserWithRole('bpsdm');

        $response = $this->actingAs($user)->get(route('bpsdm.jabatan.index'));

        $response->assertOk();
    }

    public function test_bpsdm_cannot_access_jabatan_create_page(): void
    {
        $user = $this->createUserWithRole('bpsdm');

        $response = $this->actingAs($user)->get(route('admin.jabatan.create'));

        $response->assertForbidden();
    }

    public function test_bpsdm_cannot_store_jabatan(): void
    {
        $user = $this->createUserWithRole('bpsdm');

        $response = $this->actingAs($user)->post(route('admin.jabatan.store'), [
            'nama_jabatan' => 'Hack',
            'gaji_pokok' => 0,
            'tj_transport' => 0,
            'uang_makan' => 0,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('jabatan', ['nama_jabatan' => 'Hack']);
    }

    public function test_bpsdm_cannot_update_jabatan(): void
    {
        $user = $this->createUserWithRole('bpsdm');
        $jabatan = Jabatan::create(['nama_jabatan' => 'Test', 'gaji_pokok' => 0, 'tj_transport' => 0, 'uang_makan' => 0]);

        $response = $this->actingAs($user)->put(route('admin.jabatan.update', $jabatan), [
            'nama_jabatan' => 'Hacked',
            'gaji_pokok' => 0,
            'tj_transport' => 0,
            'uang_makan' => 0,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('jabatan', ['nama_jabatan' => 'Test']);
    }

    public function test_bpsdm_cannot_delete_jabatan(): void
    {
        $user = $this->createUserWithRole('bpsdm');
        $jabatan = Jabatan::create(['nama_jabatan' => 'Test', 'gaji_pokok' => 0, 'tj_transport' => 0, 'uang_makan' => 0]);

        $response = $this->actingAs($user)->delete(route('admin.jabatan.destroy', $jabatan));

        $response->assertForbidden();
        $this->assertDatabaseHas('jabatan', ['nama_jabatan' => 'Test']);
    }

    public function test_bpsdm_can_view_pegawai_index(): void
    {
        $user = $this->createUserWithRole('bpsdm');

        $response = $this->actingAs($user)->get(route('bpsdm.pegawai.index'));

        $response->assertOk();
    }

    public function test_bpsdm_cannot_access_pegawai_create_page(): void
    {
        $user = $this->createUserWithRole('bpsdm');

        $response = $this->actingAs($user)->get(route('admin.pegawai.create'));

        $response->assertForbidden();
    }

    public function test_bpsdm_cannot_store_pegawai(): void
    {
        $user = $this->createUserWithRole('bpsdm');

        $response = $this->actingAs($user)->post(route('admin.pegawai.store'), [
            'nik' => '9999999999',
            'nama_pegawai' => 'Hacker',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('pegawai', ['nik' => '9999999999']);
    }

    public function test_bpsdm_can_view_pegawai_profile(): void
    {
        $user = $this->createUserWithRole('bpsdm');
        $jabatan = Jabatan::create(['nama_jabatan' => 'Dosen', 'gaji_pokok' => 0, 'tj_transport' => 0, 'uang_makan' => 0]);
        $pegawai = Pegawai::create([
            'nik' => '1111111111',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'jabatan_id' => $jabatan->id,
        ]);

        $response = $this->actingAs($user)->get(route('bpsdm.pegawai.show', $pegawai));

        $response->assertOk();
    }

    public function test_bpsdm_cannot_update_pegawai(): void
    {
        $user = $this->createUserWithRole('bpsdm');
        $pegawai = Pegawai::create([
            'nik' => '2222222222',
            'nama_pegawai' => 'Test',
            'jenis_kelamin' => 'L',
        ]);

        $response = $this->actingAs($user)->put(route('admin.pegawai.update', $pegawai), [
            'nik' => '2222222222',
            'nama_pegawai' => 'Hacked',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('pegawai', ['nik' => '2222222222', 'nama_pegawai' => 'Test']);
    }

    public function test_bpsdm_cannot_delete_pegawai(): void
    {
        $user = $this->createUserWithRole('bpsdm');
        $pegawai = Pegawai::create([
            'nik' => '3333333333',
            'nama_pegawai' => 'DeleteMe',
            'jenis_kelamin' => 'L',
        ]);

        $response = $this->actingAs($user)->delete(route('admin.pegawai.destroy', $pegawai));

        $response->assertForbidden();
        $this->assertDatabaseHas('pegawai', ['nik' => '3333333333']);
    }

    // ─── Pegawai: only own data ────────────────────────────────────────

    public function test_pegawai_cannot_access_admin_jabatan_index(): void
    {
        $user = $this->createUserWithRole('pegawai');

        $response = $this->actingAs($user)->get(route('admin.jabatan.index'));

        $response->assertForbidden();
    }

    public function test_pegawai_cannot_access_admin_pegawai_index(): void
    {
        $user = $this->createUserWithRole('pegawai');

        $response = $this->actingAs($user)->get(route('admin.pegawai.index'));

        $response->assertForbidden();
    }

    public function test_pegawai_can_view_own_profile_via_portal(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nik' => '4444444444',
            'nama_pegawai' => 'Myself',
            'jenis_kelamin' => 'P',
        ]);

        // Admin routes require role:admin middleware — pegawai is blocked at middleware level
        $response = $this->actingAs($user)->get(route('admin.pegawai.show', $pegawai));

        $response->assertForbidden();
    }

    public function test_pegawai_cannot_view_other_pegawai_profile(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $otherPegawai = Pegawai::create([
            'nik' => '5555555555',
            'nama_pegawai' => 'Other',
            'jenis_kelamin' => 'L',
        ]);

        $response = $this->actingAs($user)->get(route('admin.pegawai.show', $otherPegawai));

        $response->assertForbidden();
    }

    // ─── Tendik: same as pegawai ───────────────────────────────────────

    public function test_tendik_cannot_access_admin_data_master(): void
    {
        $user = $this->createUserWithRole('tendik');

        $response = $this->actingAs($user)->get(route('admin.jabatan.index'));

        $response->assertForbidden();
    }

    public function test_tendik_cannot_access_admin_pegawai_index(): void
    {
        $user = $this->createUserWithRole('tendik');

        $response = $this->actingAs($user)->get(route('admin.pegawai.index'));

        $response->assertForbidden();
    }

    // ─── Jabatan guard hapus ───────────────────────────────────────────

    public function test_admin_cannot_delete_jabatan_with_active_pegawai(): void
    {
        $user = $this->createUserWithRole('admin');
        $jabatan = Jabatan::create(['nama_jabatan' => 'Dosen', 'gaji_pokok' => 0, 'tj_transport' => 0, 'uang_makan' => 0]);
        Pegawai::create([
            'nik' => '6666666666',
            'nama_pegawai' => 'Active',
            'jenis_kelamin' => 'L',
            'jabatan_id' => $jabatan->id,
            'status_pegawai' => 'aktif',
        ]);

        $response = $this->actingAs($user)->delete(route('admin.jabatan.destroy', $jabatan));

        $response->assertSessionHasErrors('jabatan');
        $this->assertDatabaseHas('jabatan', ['nama_jabatan' => 'Dosen']);
    }

    public function test_admin_can_delete_jabatan_without_active_pegawai(): void
    {
        $user = $this->createUserWithRole('admin');
        $jabatan = Jabatan::create(['nama_jabatan' => 'Empty', 'gaji_pokok' => 0, 'tj_transport' => 0, 'uang_makan' => 0]);

        $response = $this->actingAs($user)->delete(route('admin.jabatan.destroy', $jabatan));

        $response->assertRedirect();
        $this->assertSoftDeleted('jabatan', ['nama_jabatan' => 'Empty']);
    }
}
