<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\TunjanganGaji;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TunjanganGajiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'bpsdm', 'guard_name' => 'web']);
        Role::create(['name' => 'pegawai', 'guard_name' => 'web']);
    }

    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    // ─── Authorization ────────────────────────────────────────────

    public function test_admin_can_view_index(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->get(route('admin.tunjangan-gaji.index'))->assertOk();
    }

    public function test_bpsdm_can_view_index(): void
    {
        $user = $this->createUserWithRole('bpsdm');
        $this->actingAs($user)->get(route('bpsdm.tunjangan-gaji.index'))->assertOk();
    }

    public function test_pegawai_cannot_access_admin_tunjangan(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->actingAs($user)->get(route('admin.tunjangan-gaji.index'))->assertForbidden();
    }

    // ─── CRUD ─────────────────────────────────────────────────────

    public function test_admin_can_store_semua(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->post(route('admin.tunjangan-gaji.store'), [
            'nama_tunjangan' => 'Tunjangan Transport',
            'target_tipe' => 'semua',
            'nominal' => 500000,
            'aktif' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('tunjangan_gaji', ['nama_tunjangan' => 'Tunjangan Transport', 'target_tipe' => 'semua']);
    }

    public function test_admin_can_store_jabatan(): void
    {
        $jabatan = Jabatan::create(['nama_jabatan' => 'Dosen', 'gaji_pokok' => 5000000, 'tj_transport' => 500000, 'uang_makan' => 300000]);
        $user = $this->createUserWithRole('admin');

        $this->actingAs($user)->post(route('admin.tunjangan-gaji.store'), [
            'nama_tunjangan' => 'Tunjangan Struktural',
            'target_tipe' => 'jabatan',
            'jabatan_id' => $jabatan->id,
            'nominal' => 1000000,
            'aktif' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('tunjangan_gaji', ['nama_tunjangan' => 'Tunjangan Struktural', 'jabatan_id' => $jabatan->id]);
    }

    public function test_admin_can_store_pegawai(): void
    {
        $jabatan = Jabatan::create(['nama_jabatan' => 'Dosen', 'gaji_pokok' => 5000000, 'tj_transport' => 500000, 'uang_makan' => 300000]);
        $pegawai = Pegawai::create(['nik' => '1111111111', 'nama_pegawai' => 'Budi', 'jenis_kelamin' => 'L', 'jabatan_id' => $jabatan->id, 'status_pegawai' => 'aktif']);
        $user = $this->createUserWithRole('admin');

        $this->actingAs($user)->post(route('admin.tunjangan-gaji.store'), [
            'nama_tunjangan' => 'Tunjangan Khusus',
            'target_tipe' => 'pegawai',
            'pegawai_id' => $pegawai->id,
            'nominal' => 200000,
            'aktif' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('tunjangan_gaji', ['nama_tunjangan' => 'Tunjangan Khusus', 'pegawai_id' => $pegawai->id]);
    }

    public function test_admin_can_update(): void
    {
        $user = $this->createUserWithRole('admin');
        $tunjangan = TunjanganGaji::create(['nama_tunjangan' => 'Transport', 'target_tipe' => 'semua', 'nominal' => 500000]);

        $this->actingAs($user)->patch(route('admin.tunjangan-gaji.update', $tunjangan->id), [
            'nama_tunjangan' => 'Transport Plus',
            'target_tipe' => 'semua',
            'nominal' => 600000,
        ])->assertRedirect();

        $this->assertDatabaseHas('tunjangan_gaji', ['id' => $tunjangan->id, 'nama_tunjangan' => 'Transport Plus']);
    }

    public function test_admin_can_delete(): void
    {
        $user = $this->createUserWithRole('admin');
        $tunjangan = TunjanganGaji::create(['nama_tunjangan' => 'Transport', 'target_tipe' => 'semua', 'nominal' => 500000]);

        $this->actingAs($user)->delete(route('admin.tunjangan-gaji.destroy', $tunjangan->id))->assertRedirect();

        $this->assertDatabaseMissing('tunjangan_gaji', ['id' => $tunjangan->id]);
    }

    public function test_admin_can_toggle_aktif(): void
    {
        $user = $this->createUserWithRole('admin');
        $tunjangan = TunjanganGaji::create(['nama_tunjangan' => 'Transport', 'target_tipe' => 'semua', 'nominal' => 500000, 'aktif' => true]);

        $this->actingAs($user)->patch(route('admin.tunjangan-gaji.toggle', $tunjangan->id))->assertRedirect();

        $this->assertFalse($tunjangan->fresh()->aktif);
    }

    // ─── Validation ───────────────────────────────────────────────

    public function test_validation_requires_nama_tunjangan(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->post(route('admin.tunjangan-gaji.store'), [
            'nama_tunjangan' => '',
            'target_tipe' => 'semua',
            'nominal' => 500000,
        ])->assertSessionHasErrors('nama_tunjangan');
    }

    public function test_validation_requires_jabatan_when_target_jabatan(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->post(route('admin.tunjangan-gaji.store'), [
            'nama_tunjangan' => 'Struktural',
            'target_tipe' => 'jabatan',
            'jabatan_id' => '',
            'nominal' => 500000,
        ])->assertSessionHasErrors('jabatan_id');
    }

    public function test_validation_requires_pegawai_when_target_pegawai(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->post(route('admin.tunjangan-gaji.store'), [
            'nama_tunjangan' => 'Khusus',
            'target_tipe' => 'pegawai',
            'pegawai_id' => '',
            'nominal' => 500000,
        ])->assertSessionHasErrors('pegawai_id');
    }

    public function test_filter_by_target_tipe(): void
    {
        $user = $this->createUserWithRole('admin');
        TunjanganGaji::create(['nama_tunjangan' => 'Semua', 'target_tipe' => 'semua', 'nominal' => 500000]);
        TunjanganGaji::create(['nama_tunjangan' => 'Jabatan', 'target_tipe' => 'jabatan', 'nominal' => 1000000]);

        $this->actingAs($user)->get(route('admin.tunjangan-gaji.index', ['target_tipe' => 'semua']))->assertOk();
    }
}
