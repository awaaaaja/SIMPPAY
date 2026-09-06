<?php

namespace Tests\Feature\Api;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PegawaiApiTest extends TestCase
{
    use RefreshDatabase;

    private Jabatan $jabatan;

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
    }

    #[Test]
    public function test_list_pegawai_with_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['pegawai:read']);

        Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $response = $this->getJson('/api/v1/pegawai');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'nik', 'nama_pegawai']]]);
    }

    #[Test]
    public function test_list_pegawai_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/pegawai');

        $response->assertUnauthorized();
    }

    #[Test]
    public function test_show_pegawai_with_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['pegawai:read']);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $response = $this->getJson("/api/v1/pegawai/{$pegawai->id}");

        $response->assertOk();
        $response->assertJsonPath('data.nik', '1234567890123456');
    }

    #[Test]
    public function test_create_pegawai_with_scope(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['pegawai:write']);

        $response = $this->postJson('/api/v1/pegawai', [
            'nik' => '9999999999999999',
            'nama_pegawai' => 'Andi',
            'jenis_kelamin' => 'L',
            'jabatan_id' => $this->jabatan->id,
            'status_pegawai' => 'aktif',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.nik', '9999999999999999');
    }

    #[Test]
    public function test_create_pegawai_without_scope_denied(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['pegawai:read']);

        $response = $this->postJson('/api/v1/pegawai', [
            'nik' => '9999999999999999',
            'nama_pegawai' => 'Andi',
            'jenis_kelamin' => 'L',
            'jabatan_id' => $this->jabatan->id,
            'status_pegawai' => 'aktif',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function test_update_pegawai_with_scope(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['pegawai:write']);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $response = $this->putJson("/api/v1/pegawai/{$pegawai->id}", [
            'nama_pegawai' => 'Budi Santoso',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.nama_pegawai', 'Budi Santoso');
    }
}
