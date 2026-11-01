@extends('layouts.hub')

@section('title', 'Profil Saya · ELT Academic Hub')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke dashboard</a>
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Profil saya</h1>
            <p class="mt-1.5 text-sm text-slate-600">Perbarui nama, email, dan foto profil akun Anda.</p>

            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-5 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 sm:flex-row sm:items-center">
                    @if ($user->profile_photo_path)
                        <img src="{{ Storage::disk('public')->url($user->profile_photo_path) }}" alt="Foto profil {{ $user->name }}" class="h-24 w-24 rounded-full border border-slate-200 object-cover">
                    @else
                        <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-teal-100 text-2xl font-bold text-teal-900" aria-label="Belum ada foto profil">{{ $user->initials() }}</div>
                    @endif
                    <label for="profile_photo" class="block flex-1 text-sm font-semibold text-slate-700">Foto profil
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-2 text-sm">
                        <span class="mt-1.5 block text-xs font-normal text-slate-500">JPG, PNG, atau WebP. Maksimal 2 MB. Unggah foto baru untuk mengganti foto saat ini.</span>
                        @error('profile_photo')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label for="name" class="block text-sm font-semibold text-slate-700">Nama
                    <input id="name" name="name" required maxlength="120" autocomplete="name" value="{{ old('name', $user->name) }}" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 px-3.5 text-sm focus:border-teal-700 focus:ring-teal-700">
                    @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <label for="email" class="block text-sm font-semibold text-slate-700">Email
                    <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" value="{{ old('email', $user->email) }}" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 px-3.5 text-sm focus:border-teal-700 focus:ring-teal-700">
                    @error('email')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>

                <div class="pt-2">
                    <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-teal-700 transition">Simpan profil</button>
                </div>
            </form>
        </section>
    </div>
@endsection
