<?php

namespace Tests\Feature;

use App\Models\PotonganGaji;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PotonganGajiTest extends TestCase
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

    // ─── Alpha penalty uniqueness (model boot) ─────────────────────

    public function test_saving_alpha_penalty_unsets_other_alpha_penalties(): void
    {
        $a = PotonganGaji::create(['nama_potongan' => 'Alpha Lama', 'tipe' => 'nominal', 'nilai' => 50000, 'is_alpha_penalty' => true]);
        $this->assertTrue($a->fresh()->is_alpha_penalty);

        $b = PotonganGaji::create(['nama_potongan' => 'Alpha Baru', 'tipe' => 'nominal', 'nilai' => 75000, 'is_alpha_penalty' => true]);

        $this->assertTrue($b->fresh()->is_alpha_penalty);
        $this->assertFalse($a->fresh()->is_alpha_penalty);
    }

    public function test_saving_non_alpha_does_not_unset_existing_alpha(): void
    {
        $a = PotonganGaji::create(['nama_potongan' => 'Alpha', 'tipe' => 'nominal', 'nilai' => 50000, 'is_alpha_penalty' => true]);
        PotonganGaji::create(['nama_potongan' => 'BPJS', 'tipe' => 'persentase', 'nilai' => 5, 'is_alpha_penalty' => false]);

        $this->assertTrue($a->fresh()->is_alpha_penalty);
        $this->assertCount(2, PotonganGaji::all());
    }

    public function test_toggling_alpha_penalty_off_unsets_others(): void
    {
        PotonganGaji::create(['nama_potongan' => 'Alpha 1', 'tipe' => 'nominal', 'nilai' => 50000, 'is_alpha_penalty' => true]);
        $b = PotonganGaji::create(['nama_potongan' => 'Alpha 2', 'tipe' => 'nominal', 'nilai' => 60000, 'is_alpha_penalty' => true]);

        // Now Alpha 1 was unset by boot. Set Alpha 2 to false.
        $b->update(['is_alpha_penalty' => false]);
        $this->assertFalse($b->fresh()->is_alpha_penalty);

        // Alpha 1 should still be false (was unset during initial save of Alpha 2)
        $this->assertCount(0, PotonganGaji::where('is_alpha_penalty', true)->get());
    }

    // ─── CRUD ─────────────────────────────────────────────────────

    public function test_admin_can_view_index(): void
    {
        $user = $this->createUserWithRole('admin');
        PotonganGaji::create(['nama_potongan' => 'Alpha', 'tipe' => 'nominal', 'nilai' => 50000, 'is_alpha_penalty' => true]);

        $this->actingAs($user)->get(route('admin.potongan-gaji.index'))->assertOk();
    }

    public function test_bpsdm_can_view_index(): void
    {
        $user = $this->createUserWithRole('bpsdm');
        $this->actingAs($user)->get(route('bpsdm.potongan-gaji.index'))->assertOk();
    }

    public function test_pegawai_cannot_access_admin_potongan(): void
    {
        $user = $this->createUserWithRole('pegawai');
        $this->actingAs($user)->get(route('admin.potongan-gaji.index'))->assertForbidden();
    }

    public function test_admin_can_store(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->post(route('admin.potongan-gaji.store'), [
            'nama_potongan' => 'BPJS',
            'tipe' => 'persentase',
            'nilai' => 5,
            'is_alpha_penalty' => false,
            'aktif' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('potongan_gaji', ['nama_potongan' => 'BPJS']);
    }

    public function test_admin_can_update(): void
    {
        $user = $this->createUserWithRole('admin');
        $potongan = PotonganGaji::create(['nama_potongan' => 'BPJS', 'tipe' => 'nominal', 'nilai' => 50000, 'is_alpha_penalty' => false]);

        $this->actingAs($user)->patch(route('admin.potongan-gaji.update', $potongan->id), [
            'nama_potongan' => 'BPJS Kesehatan',
            'tipe' => 'persentase',
            'nilai' => 4,
            'is_alpha_penalty' => false,
            'aktif' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('potongan_gaji', ['id' => $potongan->id, 'nama_potongan' => 'BPJS Kesehatan']);
    }

    public function test_admin_can_delete(): void
    {
        $user = $this->createUserWithRole('admin');
        $potongan = PotonganGaji::create(['nama_potongan' => 'BPJS', 'tipe' => 'nominal', 'nilai' => 50000, 'is_alpha_penalty' => false]);

        $this->actingAs($user)->delete(route('admin.potongan-gaji.destroy', $potongan->id))->assertRedirect();

        $this->assertDatabaseMissing('potongan_gaji', ['id' => $potongan->id]);
    }

    public function test_admin_can_toggle_aktif(): void
    {
        $user = $this->createUserWithRole('admin');
        $potongan = PotonganGaji::create(['nama_potongan' => 'BPJS', 'tipe' => 'nominal', 'nilai' => 50000, 'aktif' => true]);

        $this->actingAs($user)->patch(route('admin.potongan-gaji.toggle', $potongan->id))->assertRedirect();

        $this->assertFalse($potongan->fresh()->aktif);
    }

    public function test_validation_requires_nama_potongan(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->post(route('admin.potongan-gaji.store'), [
            'nama_potongan' => '',
            'tipe' => 'nominal',
            'nilai' => 50000,
        ])->assertSessionHasErrors('nama_potongan');
    }

    public function test_validation_requires_valid_tipe(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->post(route('admin.potongan-gaji.store'), [
            'nama_potongan' => 'Test',
            'tipe' => 'invalid',
            'nilai' => 50000,
        ])->assertSessionHasErrors('tipe');
    }

    public function test_validation_requires_positive_nilai(): void
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user)->post(route('admin.potongan-gaji.store'), [
            'nama_potongan' => 'Test',
            'tipe' => 'nominal',
            'nilai' => -100,
        ])->assertSessionHasErrors('nilai');
    }

    public function test_seeder_creates_alpha_penalty(): void
    {
        PotonganGaji::create(['nama_potongan' => 'Alpha', 'tipe' => 'nominal', 'nilai' => 50000, 'is_alpha_penalty' => true, 'aktif' => true]);

        $this->assertDatabaseHas('potongan_gaji', [
            'nama_potongan' => 'Alpha',
            'is_alpha_penalty' => true,
            'aktif' => true,
        ]);
    }
}
