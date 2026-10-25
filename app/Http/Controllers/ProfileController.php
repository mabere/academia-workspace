<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $oldPhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')->store('avatars', 'public');
            if (! $newPhotoPath) {
                return back()->withErrors(['profile_photo' => 'Foto gagal disimpan. Silakan coba lagi.'])->withInput();
            }

            $oldPhotoPath = $user->profile_photo_path;
            $user->profile_photo_path = $newPhotoPath;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        if ($oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return back()->with('status', 'Profil berhasil diperbarui.');
    }
}
