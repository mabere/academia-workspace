@extends('layouts.hub')
@section('title', 'ELT Academic Hub — Mendukung Tri Dharma')
@section('content')
<section class="grid gap-12 py-12 md:grid-cols-[1.2fr_.8fr] md:items-center md:py-20">
    <div>
        <p class="mb-4 text-sm font-semibold uppercase tracking-[.18em] text-teal-700">Connecting Teaching, Research, and Community Engagement</p>
        <h1 class="max-w-3xl text-4xl font-bold leading-tight tracking-tight text-slate-950 md:text-6xl">Satu ruang kerja untuk mendukung Tri Dharma dosen.</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">Kelola proyek akademik dan kembangkan alur kerja pengajaran, penelitian, serta pengabdian. AI membantu menyiapkan draf; keputusan akademik tetap di tangan dosen.</p>
        <div class="mt-8 flex flex-wrap items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-3.5 font-semibold text-white shadow-sm hover:bg-slate-800 transition">
                    <span>Buka Dashboard</span>
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{ route('courses.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-100 transition">
                    <span>Mata Kuliah & Perkuliahan</span>
                </a>
            @else
                <a href="{{ route('register') }}"
                    class="rounded-xl bg-slate-900 px-6 py-3.5 font-semibold text-white shadow-sm hover:bg-slate-800 transition">Daftar Akun</a>
                <a href="{{ route('login') }}"
                    class="rounded-xl border border-slate-300 bg-white px-6 py-3.5 font-semibold text-slate-700 shadow-sm hover:bg-slate-100 transition">Masuk</a>
            @endauth
        </div>
    </div>
    <div class="grid gap-3">
        @foreach([['01','Pendidikan & Pengajaran','WriteWise ELT · tugas, rubrik, dan umpan balik formatif'],['02','Penelitian','LitSynth ELT · sintesis literatur dengan jejak sumber'],['03','Pengabdian','GuruBahasa AI · draf materi untuk pendampingan guru']] as [$number,$title,$desc])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex gap-4"><span class="text-sm font-bold text-teal-700">{{ $number }}</span><div><h2 class="font-semibold">{{ $title }}</h2><p class="mt-1 text-sm leading-6 text-slate-600">{{ $desc }}</p></div></div></article>
        @endforeach
    </div>
</section>
<p class="border-t border-slate-200 pt-5 text-sm text-slate-500">Konten dan hasil AI merupakan draf untuk ditinjau. Modul diaktifkan bertahap berdasarkan kesiapan dan validasi pengguna.</p>
@endsection
