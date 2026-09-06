<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Kehadiran;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $secret;

    private Jabatan $jabatan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->secret = config('webhooks.secret');
        $this->jabatan = Jabatan::create([
            'nama_jabatan' => 'Staff',
            'gaji_pokok' => 5000000,
            'tj_transport' => 1000000,
            'uang_makan' => 500000,
            'status' => 'aktif',
        ]);
    }

    private function signPayload(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret);
    }

    #[Test]
    public function test_webhook_valid_signature_accepted(): void
    {
        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $payload = json_encode([
            'nik' => '1234567890123456',
            'periode' => '2026-01-01',
            'hadir' => 22,
            'sakit' => 1,
            'alpha' => 1,
        ]);

        $response = $this->postJson('/webhook/absensi', json_decode($payload, true), [
            'X-Signature' => $this->signPayload($payload),
        ]);

        $response->assertAccepted();
    }

    #[Test]
    public function test_webhook_invalid_signature_rejected(): void
    {
        $payload = json_encode([
            'nik' => '1234567890123456',
            'periode' => '2026-01-01',
            'hadir' => 22,
            'sakit' => 1,
            'alpha' => 1,
        ]);

        $response = $this->postJson('/webhook/absensi', json_decode($payload, true), [
            'X-Signature' => 'invalid-signature',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function test_webhook_missing_signature_rejected(): void
    {
        $response = $this->postJson('/webhook/absensi', [
            'nik' => '1234567890123456',
            'periode' => '2026-01-01',
            'hadir' => 22,
            'sakit' => 1,
            'alpha' => 1,
        ]);

        $response->assertUnauthorized();
    }

    #[Test]
    public function test_webhook_idempotent_duplicate_payload(): void
    {
        $pegawai = Pegawai::create([
            'nik' => '1234567890123456',
            'nama_pegawai' => 'Budi',
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'aktif',
            'jabatan_id' => $this->jabatan->id,
        ]);

        $payload = json_encode([
            'nik' => '1234567890123456',
            'periode' => '2026-02-01',
            'hadir' => 20,
            'sakit' => 2,
            'alpha' => 0,
        ]);

        $headers = ['X-Signature' => $this->signPayload($payload)];

        // Send twice
        $this->postJson('/webhook/absensi', json_decode($payload, true), $headers);
        $this->postJson('/webhook/absensi', json_decode($payload, true), $headers);

        // Process the jobs
        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        // Should have exactly 1 record, not 2
        $this->assertEquals(1, Kehadiran::where('pegawai_id', $pegawai->id)
            ->where('periode', Carbon::create(2026, 2, 1))
            ->count());
    }

    #[Test]
    public function test_webhook_validation_required_fields(): void
    {
        $payload = json_encode([]);
        $response = $this->postJson('/webhook/absensi', [], [
            'X-Signature' => $this->signPayload($payload),
        ]);

        $response->assertUnprocessable();
    }
}
