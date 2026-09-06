<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'pegawai', 'guard_name' => 'web']);
        Role::create(['name' => 'bpsdm', 'guard_name' => 'web']);
        Role::create(['name' => 'tendik', 'guard_name' => 'web']);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $user = User::factory()->create(['username' => 'admin123']);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'username' => 'admin123',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create(['username' => 'admin123']);

        $this->post('/login', [
            'username' => 'admin123',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_admin_redirected_to_admin_dashboard(): void
    {
        $user = User::factory()->create(['username' => 'admin_user']);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'username' => 'admin_user',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_bpsdm_redirected_to_bpsdm_dashboard(): void
    {
        $user = User::factory()->create(['username' => 'bpsdm_user']);
        $user->assignRole('bpsdm');

        $response = $this->post('/login', [
            'username' => 'bpsdm_user',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('bpsdm.dashboard', absolute: false));
    }

    public function test_pegawai_redirected_to_portal_dashboard(): void
    {
        $user = User::factory()->create(['username' => 'pegawai_user']);
        $user->assignRole('pegawai');

        $response = $this->post('/login', [
            'username' => 'pegawai_user',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('portal.dashboard', absolute: false));
    }

    public function test_tendik_redirected_to_portal_dashboard(): void
    {
        $user = User::factory()->create(['username' => 'tendik_user']);
        $user->assignRole('tendik');

        $response = $this->post('/login', [
            'username' => 'tendik_user',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('portal.dashboard', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * FR-05: Rate limiting — 5x gagal → lockout 1 menit.
     */
    public function test_rate_limiting_after_5_failed_attempts(): void
    {
        $user = User::factory()->create(['username' => 'rateuser']);

        // 5 attempts with wrong password
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'rateuser',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'username' => 'rateuser',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * FR-04: Lazy password rehash — user dengan legacy md5 hash bisa login
     * dan password di-migrate ke bcrypt.
     *
     * Asumsi: CI3 lama memakai md5() untuk hashing password.
     * Source CI3 tidak tersedia di repo ini — ini asumsi yang perlu diverifikasi.
     */
    public function test_lazy_password_rehash_from_md5(): void
    {
        $plainPassword = 'secret123';
        $md5Hash = md5($plainPassword);

        $user = User::factory()->create([
            'username' => 'legacyuser',
            'password' => 'invalid-hash-to-prevent-direct-bcrypt-login', // placeholder
            'legacy_password_hash' => $md5Hash,
            'legacy_password_migrated' => false,
        ]);

        $response = $this->post('/login', [
            'username' => 'legacyuser',
            'password' => $plainPassword,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();

        // Verify password was re-hashed to bcrypt
        $user->refresh();
        $this->assertTrue($user->legacy_password_migrated);
        $this->assertNull($user->legacy_password_hash);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    /**
     * FR-04: Lazy password rehash — user dengan legacy plain text hash.
     */
    public function test_lazy_password_rehash_from_plaintext(): void
    {
        $plainPassword = 'mypassword';

        $user = User::factory()->create([
            'username' => 'plainuser',
            'password' => 'invalid-hash',
            'legacy_password_hash' => $plainPassword, // plain text stored as-is
            'legacy_password_migrated' => false,
        ]);

        $response = $this->post('/login', [
            'username' => 'plainuser',
            'password' => $plainPassword,
        ]);

        $this->assertAuthenticated();

        $user->refresh();
        $this->assertTrue($user->legacy_password_migrated);
        $this->assertNull($user->legacy_password_hash);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    /**
     * Pastikan user yang sudah di-migrate tidak diproses ulang.
     */
    public function test_already_migrated_user_not_reprocessed(): void
    {
        $user = User::factory()->create([
            'username' => 'migrateduser',
            'legacy_password_migrated' => true,
            'legacy_password_hash' => 'old-hash-still-here',
        ]);

        $this->post('/login', [
            'username' => 'migrateduser',
            'password' => 'password',
        ]);

        $user->refresh();
        // Hash should not be re-processed
        $this->assertTrue($user->legacy_password_migrated);
        $this->assertNotNull($user->legacy_password_hash);
    }
}
