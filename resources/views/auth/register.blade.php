@extends('layouts.hub')
@section('title', 'Daftar Akun — Academia Workspace')
@section('content')
<div class="mx-auto max-w-xl py-6 sm:py-12">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
        {{-- Header Card --}}
        <div class="border-b border-slate-100 bg-linear-to-b from-slate-50 to-white px-6 py-8 text-center sm:px-10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#05285c]/5 p-2 shadow-2xs">
                <img src="{{ asset('apple-touch-icon.png') }}" width="48" height="48" alt="Logo" class="h-10 w-10 object-contain">
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Pendaftaran Akun Baru</h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-600">
                Bergabung ke ekosistem Academia Workspace untuk kegiatan pembelajaran dan akademik.
            </p>
        </div>

        {{-- Form Body --}}
        <div class="px-6 py-8 sm:px-10">
            {{-- Notice for Lecturer Accounts --}}
            <div class="mb-6 rounded-2xl border border-teal-200 bg-teal-50/70 p-4 text-xs text-teal-950">
                <div class="flex items-start gap-2.5">
                    <svg class="h-4 w-4 text-teal-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-bold">Informasi Akun Dosen / Tenaga Pendidik:</p>
                        <p class="mt-0.5 text-slate-600 leading-relaxed">
                            Formulir ini diperuntukkan bagi <strong>Mahasiswa</strong> dan <strong>Pengguna Umum</strong>. Akun Dosen dengan akses manajemen perkuliahan & BKD dikonfigurasikan secara terpusat oleh Administrator.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Error Flash Alert --}}
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50/80 p-4 text-xs text-rose-900">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="post" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Field Nama Lengkap --}}
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Nama Lengkap
                    </label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input
                            id="name"
                            required
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            autofocus
                            class="has-icon-left w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600 @error('name') border-rose-300 bg-rose-50/30 @enderror">
                    </div>
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Field Email --}}
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Alamat Email
                    </label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </div>
                        <input
                            id="email"
                            required
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            class="has-icon-left w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600 @error('email') border-rose-300 bg-rose-50/30 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Field Jenis Akun (Role) --}}
                <div>
                    <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Jenis Akun
                    </label>
                    <select
                        id="role"
                        required
                        name="role"
                        class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                        <option value="student" @selected(old('role', 'student') === 'student')>Mahasiswa (Akses Tugas, Materi & Presensi)</option>
                        <option value="public" @selected(old('role') === 'public')>Pengguna Umum / Peneliti Tamu</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Inputs dengan Alpine --}}
                <div x-data="{ showPass: false }" class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Kata Sandi
                            </label>
                            <button
                                type="button"
                                @click="showPass = !showPass"
                                class="text-[11px] font-semibold text-teal-800 hover:text-teal-950 transition">
                                <span x-text="showPass ? 'Sembunyikan' : 'Tampilkan'"></span>
                            </button>
                        </div>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                required
                                minlength="10"
                                :type="showPass ? 'text' : 'password'"
                                name="password"
                                autocomplete="new-password"
                                class="has-icon-left w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600 @error('password') border-rose-300 bg-rose-50/30 @enderror">
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">Minimal 10 karakter kombinasi huruf dan angka.</p>
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Ulangi Kata Sandi
                        </label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input
                                id="password_confirmation"
                                required
                                :type="showPass ? 'text' : 'password'"
                                name="password_confirmation"
                                autocomplete="new-password"
                                class="has-icon-left w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#05285c] px-5 py-3.5 text-sm font-semibold text-white shadow-md hover:bg-[#041e45] focus:outline-none focus:ring-2 focus:ring-[#05285c] focus:ring-offset-2 transition">
                        <span>Buat Akun Sekarang</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </button>
                </div>
            </form>

            {{-- Footer Link --}}
            <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs text-slate-600">
                <p>
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="font-bold text-[#00a98f] hover:text-teal-900 underline transition">
                        Masuk di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection