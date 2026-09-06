<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
    }

    /**
     * FR-03: Ganti password mandiri — validasi password lama + konfirmasi.
     */
    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('NewPass123', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect('/profile');
    }

    /**
     * FR-03: Validasi — min 8 char, campuran huruf-angka.
     */
    public function test_password_must_be_at_least_8_characters(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'Ab1',
                'password_confirmation' => 'Ab1',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_contain_letters_and_numbers(): void
    {
        $user = User::factory()->create();

        // Only letters, no numbers
        $response = $this
            ->actingAs($user)
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'abcdefgh',
                'password_confirmation' => 'abcdefgh',
            ]);

        $response->assertSessionHasErrors('password');

        // Only numbers, no letters
        $response = $this
            ->actingAs($user)
            ->put('/password', [
                'current_password' => 'password',
                'password' => '12345678',
                'password_confirmation' => '12345678',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'NewPass123',
                'password_confirmation' => 'DifferentPass123',
            ]);

        $response->assertSessionHasErrors('password');
    }
}
