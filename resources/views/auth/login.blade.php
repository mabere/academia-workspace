@extends('layouts.hub')
@section('title', 'Masuk — ELT Academic Hub')
@section('content')
<section class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-7 shadow-sm"><h1 class="text-2xl font-bold">Masuk</h1><p class="mt-2 text-sm text-slate-600">Lanjutkan ke ruang kerja akademik Anda.</p>
<form method="post" action="{{ route('login') }}" class="mt-6 space-y-4">@csrf
    <label class="block text-sm font-medium">Email<input required type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="mt-1 w-full rounded-lg border-slate-300"></label>
    <label class="block text-sm font-medium">Kata sandi<input required type="password" name="password" autocomplete="current-password" class="mt-1 w-full rounded-lg border-slate-300"></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="rounded border-slate-300"> Ingat saya</label>
    <button class="w-full rounded-lg bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-700">Masuk</button>
</form><p class="mt-5 text-sm text-slate-600">Belum punya akun? <a class="font-semibold text-teal-800" href="{{ route('register') }}">Daftar</a></p></section>
@endsection
