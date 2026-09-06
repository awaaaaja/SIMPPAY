<?php

namespace Tests\Feature\Api;

use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AbsensiApiTest extends TestCase
{
    use RefreshDatabase;

    private Jabatan $jabatan;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->jabatan = Jabatan::create([
            'nama_jabatan' => 'Staff',
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'status' => 'aktif',
        ]);
    }

    #[Test]
    public function test_list_absensi_with_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['absensi:read']);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        Kehadiran::create([
            'pegawai_id' => $pegawai->id,
            'periode' => Carbon::create(2026, 1, 1),
            'hadir' => 22,
            'sakit' => 1,
            'alpha' => 1,
        ]);

        $response = $this->getJson('/api/v1/absensi?periode=2026-01-01');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'periode', 'hadir']]]);
    }

    #[Test]
    public function test_list_absensi_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/absensi');

        $response->assertUnauthorized();
    }

    #[Test]
    public function test_store_absensi_with_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['absensi:write']);

        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $response = $this->postJson('/api/v1/absensi', [
            'pegawai_id' => $pegawai->id,
            'periode' => '2026-02-01',
            'hadir' => 20,
            'sakit' => 2,
            'alpha' => 0,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('kehadiran', [
            'pegawai_id' => $pegawai->id,
            'hadir' => 20,
        ]);
    }

    #[Test]
    public function test_store_absensi_unauthenticated(): void
    {
        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $response = $this->postJson('/api/v1/absensi', [
            'pegawai_id' => $pegawai->id,
            'periode' => '2026-02-01',
            'hadir' => 20,
            'sakit' => 2,
            'alpha' => 0,
        ]);

        $response->assertUnauthorized();
    }
}
