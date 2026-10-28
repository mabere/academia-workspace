@extends('layouts.hub')
@section('title', 'Masuk — Academia Workspace')
@section('content')
<div class="mx-auto max-w-xl py-6 sm:py-12">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
        {{-- Header Card --}}
        <div class="border-b border-slate-100 bg-linear-to-b from-slate-50 to-white px-6 py-8 text-center sm:px-10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#05285c]/5 p-2 shadow-2xs">
                <img src="{{ asset('apple-touch-icon.png') }}" width="48" height="48" alt="Logo" class="h-10 w-10 object-contain">
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Masuk ke Ruang Kerja</h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-600">
                Sistem Informasi Kinerja Dosen & Manajemen Tri Dharma Perguruan Tinggi
            </p>
        </div>

        {{-- Form Body --}}
        <div class="px-6 py-8 sm:px-10">
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

            <form method="post" action="{{ route('login') }}" class="space-y-5">
                @csrf

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
                            autofocus
                            class="has-icon-left w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600 @error('email') border-rose-300 bg-rose-50/30 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Field Password dengan Alpine Show/Hide --}}
                <div x-data="{ showPassword: false }">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="text-[11px] font-semibold text-teal-800 hover:text-teal-950 transition">
                            <span x-text="showPassword ? 'Sembunyikan' : 'Tampilkan'"></span>
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
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            autocomplete="current-password"
                            class="has-icon-left w-full rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600 @error('password') border-rose-300 bg-rose-50/30 @enderror">
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me & Help --}}
                <div class="flex items-center justify-between pt-1 text-xs">
                    <label class="inline-flex items-center gap-2 font-medium text-slate-600 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="rounded-md border-slate-300 text-teal-800 focus:ring-teal-700">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#05285c] px-5 py-3.5 text-sm font-semibold text-white shadow-md hover:bg-[#041e45] focus:outline-none focus:ring-2 focus:ring-[#05285c] focus:ring-offset-2 transition">
                    <span>Masuk ke Workspace</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            {{-- Footer Helper Card --}}
            <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs text-slate-600">
                <p>
                    Belum memiliki akun?
                    <a href="{{ route('register') }}" class="font-bold text-[#00a98f] hover:text-teal-900 underline transition">
                        Daftar Akun Baru
                    </a>
                </p>
                <div class="mt-4 flex items-center justify-center gap-2 text-[11px] text-slate-400">
                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Sesi terlindungi enkripsi standar institusi</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection