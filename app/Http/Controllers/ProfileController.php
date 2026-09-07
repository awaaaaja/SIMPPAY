<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user()->only('name', 'email', 'username', 'avatar'),
            'roles' => $request->user()->getRoleNames(),
            'status' => session('status'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
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

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
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

        return Redirect::route('profile.edit')->with('success', 'Password berhasil diubah.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
