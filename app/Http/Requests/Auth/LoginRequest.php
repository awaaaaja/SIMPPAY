<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('username', $this->string('username'))->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        // ─── Lazy Password Rehash (FR-04) ────────────────────────────────
        // Source CI3 asli (Login.php / ModelPenggajian.php) TIDAK tersedia
        // di repo ini. Asumsi yang dipakai:
        //
        //   1. CI3 lama kemungkinan besar memakai md5() — paling umum di CI3.
        //   2. Fallback: plain text comparison (beberapa CI3 tidak hash).
        //   3. Deteksi md5: hash panjangnya 32 char hex.
        //   4. Bcrypt/argon2 ditangani oleh Hash::check bawaan Laravel.
        //
        // NOTE: Kalau CI3 memakai hash custom (sha1+salt, dll), tweak
        //       migrateLegacyPassword() di bawah.
        // ──────────────────────────────────────────────────────────────────

        $plainPassword = $this->string('password');

        // Check legacy hash first (before bcrypt) — user with unmigrated
        // legacy hash has a placeholder bcrypt that won't match plaintext.
        if ($user->legacy_password_hash && ! $user->legacy_password_migrated) {
            if ($this->verifyLegacyPassword($user, $plainPassword)) {
                $this->migrateLegacyPassword($user, $plainPassword);
                RateLimiter::clear($this->throttleKey());
                Auth::login($user, $this->boolean('remember'));

                return;
            }
            // Legacy hash didn't match — fall through to check current bcrypt
        }

        // Standard bcrypt check
        if (! Hash::check($plainPassword, $user->password)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Auth::login($user, $this->boolean('remember'));
    }

    /**
     * Verify legacy CI3 password against stored hash.
     *
     * Only md5 is supported for legacy migration. Plain text passwords
     * are NOT supported — users must reset their password via admin.
     */
    protected function verifyLegacyPassword(User $user, string $plainPassword): bool
    {
        $legacyHash = $user->legacy_password_hash;

        if (strlen($legacyHash) === 32 && ctype_xdigit($legacyHash)) {
            // CI3 md5 migration path
            return md5($plainPassword) === $legacyHash;
        }

        // No plain text fallback — security risk
        return false;
    }

    /**
     * Migrate legacy password to bcrypt.
     */
    protected function migrateLegacyPassword(User $user, string $plainPassword): void
    {
        $user->forceFill([
            'password' => $plainPassword, // hashed by 'hashed' cast
            'legacy_password_migrated' => true,
            'legacy_password_hash' => null,
        ])->save();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
