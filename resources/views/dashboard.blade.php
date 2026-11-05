@extends('layouts.hub')
@section('title', 'Dashboard — Academia Workspace')
@section('content')
@php
    $roleLabels = [
        'superadmin' => 'Superadmin',
        'admin' => 'Administrator',
        'lecturer' => 'Dosen Pengampu',
        'student' => 'Mahasiswa',
        'public' => 'Pengguna Umum',
    ];

    $modules = [
        ['pillar' => 'Pendidikan & Pengajaran', 'name' => 'WriteWise ELT', 'description' => 'Tugas menulis akademik, draf revisi mahasiswa bertahap, dan umpan balik rubrik dosen.', 'status' => 'Pilot tersedia', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'LitSynth ELT', 'description' => 'Repositori sumber PDF privat, matriks kajian literatur, dan penyusunan blueprint instrumen.', 'status' => 'Tersedia · lokal', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'Validasi pakar LitSynth', 'description' => 'Undangan pakar per versi snapshot, penilaian butir, serta indeks deskriptif Aiken’s V/CVI.', 'status' => 'Tersedia', 'tone' => 'green'],
        ['pillar' => 'Pengabdian', 'name' => 'GuruBahasa AI', 'description' => 'Studio penyusunan dan kurasi materi bahasa kontekstual untuk sekolah dan komunitas.', 'status' => 'Manual tersedia · AI nonaktif', 'tone' => 'amber'],
        ['pillar' => 'Lintas pilar', 'name' => 'Output & Evidence', 'description' => 'Temukan, pratinjau, dan ekspor artefak bukti kinerja Tri Dharma serta evaluasi BKD.', 'status' => 'Tersedia untuk staf', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'IndoEFL CorpusLab', 'description' => 'Korpus lokal dengan gerbang protokol etik, persetujuan, anonimisasi, anotasi, KWIC, dan ekspor terbatas.', 'status' => 'Tersedia setelah protokol disetujui', 'tone' => 'amber'],
    ];
@endphp

{{-- HEADER GREETING BANNER --}}
<section class="relative mb-8 overflow-hidden rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
    <div class="pointer-events-none absolute -top-20 -right-20 h-64 w-64 rounded-full bg-teal-500/10 blur-3xl"></div>
    <div class="relative flex flex-wrap items-center justify-between gap-5">
        <div class="space-y-1.5">
            @if(auth()->user()->isStaff())
                <span class="inline-flex items-center gap-2 rounded-full bg-teal-50 border border-teal-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-teal-900">
                    <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                    Ruang Kerja Tri Dharma Dosen
                </span>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Halo, {{ auth()->user()->name }}</h1>
                <p class="text-sm text-slate-600">{{ $roleLabels[auth()->user()->role] ?? 'Pengguna' }} · Ringkasan kelas perkuliahan, proyek riset, dan bukti kinerja akademik Anda.</p>
            @elseif(auth()->user()->role === 'student')
                <span class="inline-flex items-center gap-2 rounded-full bg-teal-50 border border-teal-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-teal-900">
                    <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                    Portal Belajar Mahasiswa
                </span>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Halo, {{ auth()->user()->name }}</h1>
                <p class="text-sm text-slate-600">
                    Mahasiswa
                    @if(auth()->user()->studyProgram)
                        &middot; <span class="font-semibold text-slate-800">{{ auth()->user()->studyProgram->name }} ({{ auth()->user()->studyProgram->level }})</span>
                    @endif
                    @if(auth()->user()->nim)
                        &middot; NIM: <span class="font-mono font-bold text-teal-900">{{ auth()->user()->nim }}</span>
                    @endif
                    @if(auth()->user()->cohort_year)
                        &middot; Angkatan {{ auth()->user()->cohort_year }}
                    @endif
                </p>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-slate-600">
                    Ruang Publik
                </span>
                <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Halo, {{ auth()->user()->name }}</h1>
                <p class="text-sm text-slate-600">Selamat datang di Academia Workspace.</p>
            @endif
        </div>

        @if(auth()->user()->isStaff())
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                    + Tambah Mata Kuliah
                </a>
                <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#05285c] px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-[#041e45] transition">
                    + Buat Proyek
                </a>
            </div>
        @elseif(auth()->user()->role === 'student')
            <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#05285c] px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-[#041e45] transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Buka Mata Kuliah Saya</span>
            </a>
        @endif
    </div>
</section>

{{-- TAMPILAN 1: DOSEN / STAFF --}}
@if(auth()->user()->isStaff())
    @include('dashboard.staff')
@elseif(auth()->user()->role === 'student')
    @include('dashboard.student')
@else
    @include('dashboard.public')
@endif
@endsection
