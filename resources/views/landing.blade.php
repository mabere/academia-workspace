@extends('layouts.hub')
@section('title', 'ELT Academic Hub — Mendukung Tri Dharma')
@section('content')
<section class="grid gap-12 py-12 md:grid-cols-[1.2fr_.8fr] md:items-center md:py-20">
    <div>
        <p class="mb-4 text-sm font-semibold uppercase tracking-[.18em] text-teal-700">Connecting Teaching, Research, and Community Engagement</p>
        <h1 class="max-w-3xl text-4xl font-bold leading-tight tracking-tight text-slate-950 md:text-6xl">Satu ruang kerja untuk mendukung Tri Dharma dosen.</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">Kelola proyek akademik dan kembangkan alur kerja pengajaran, penelitian, serta pengabdian. AI membantu menyiapkan draf; keputusan akademik tetap di tangan dosen.</p>
        <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('register') }}" class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white hover:bg-slate-700">Buat akun</a><a href="{{ route('login') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 font-semibold hover:bg-slate-100">Masuk</a></div>
    </div>
    <div class="grid gap-3">
        @foreach([['01','Pendidikan & Pengajaran','WriteWise ELT · tugas, rubrik, dan umpan balik formatif'],['02','Penelitian','LitSynth ELT · sintesis literatur dengan jejak sumber'],['03','Pengabdian','GuruBahasa AI · draf materi untuk pendampingan guru']] as [$number,$title,$desc])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex gap-4"><span class="text-sm font-bold text-teal-700">{{ $number }}</span><div><h2 class="font-semibold">{{ $title }}</h2><p class="mt-1 text-sm leading-6 text-slate-600">{{ $desc }}</p></div></div></article>
        @endforeach
    </div>
</section>
<p class="border-t border-slate-200 pt-5 text-sm text-slate-500">Konten dan hasil AI merupakan draf untuk ditinjau. Modul diaktifkan bertahap berdasarkan kesiapan dan validasi pengguna.</p>
@endsection
