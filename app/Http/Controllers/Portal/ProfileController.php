<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = request()->user();
        return Inertia::render('Portal/Profile', [
            'user' => $user->only('name', 'email', 'username', 'avatar'),
            'pegawai' => $user->pegawai ? $user->pegawai->only('no_hp', 'alamat') : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();
        $user->name = $validated['name'];
        $user->email = $validated['email'] ?? $user->email;

        if ($request->hasFile('avatar')) {
            $old = $user->avatar;
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $validated['avatar'];
            if ($old && \Storage::disk('public')->exists($old)) {
                \Storage::disk('public')->delete($old);
            }
        }

        $user->save();

        if ($user->pegawai) {
            $user->pegawai->update([
                'no_hp' => $validated['no_hp'] ?? $user->pegawai->no_hp,
                'alamat' => $validated['alamat'] ?? $user->pegawai->alamat,
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
