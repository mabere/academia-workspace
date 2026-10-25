@extends('layouts.hub')
@section('title', 'Daftar — ELT Academic Hub')
@section('content')
<section class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-7 shadow-sm"><h1 class="text-2xl font-bold">Buat akun</h1><p class="mt-2 text-sm text-slate-600">Akun publik atau mahasiswa. Akun dosen diberikan oleh pengelola institusi.</p>
<form method="post" action="{{ route('register') }}" class="mt-6 space-y-4">@csrf
    <label class="block text-sm font-medium">Nama<input required name="name" value="{{ old('name') }}" autocomplete="name" class="mt-1 w-full rounded-lg border-slate-300"></label>
    <label class="block text-sm font-medium">Email<input required type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="mt-1 w-full rounded-lg border-slate-300"></label>
    <label class="block text-sm font-medium">Jenis akun<select name="role" class="mt-1 w-full rounded-lg border-slate-300"><option value="student">Mahasiswa</option><option value="public" @selected(old('role')==='public')>Pengguna umum</option></select></label>
    <label class="block text-sm font-medium">Kata sandi<input required minlength="10" type="password" name="password" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300"></label>
    <label class="block text-sm font-medium">Ulangi kata sandi<input required type="password" name="password_confirmation" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300"></label>
    <button class="w-full rounded-lg bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-700">Buat akun</button>
</form></section>
@endsection
