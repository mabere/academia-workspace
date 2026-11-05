@extends('layouts.hub')
@section('title', 'Fakultas, Program Studi & Periode — Academic Hub')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-teal-800 border border-teal-200/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                    Master Unit Akademik Dosen
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Fakultas, Program Studi & Semester</h1>
            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600">
                Kelola identitas Fakultas, Program Studi tempat Anda mengajar, serta Periode Semester aktif untuk kelengkapan atribut Mata Kuliah, Daftar Nilai A4, dan Laporan BKD Dikti.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('students.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                <span>Direktori Mahasiswa</span>
            </a>
            <a href="{{ route('courses.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-800 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-teal-900 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Mata Kuliah</span>
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Terdapat kendala pada input data:</p>
            <ul class="mt-1 list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ringkasan Statistik Cepat --}}
    @php
        $activePeriod = $academicPeriods->firstWhere('is_active', true);
    @endphp
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Fakultas</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $faculties->count() }}</span>
                <span class="text-xs font-medium text-slate-500">Unit Fakultas</span>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-teal-800">Total Program Studi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-teal-900">{{ $studyPrograms->count() }}</span>
                <span class="text-xs font-medium text-teal-700">Lintas Jenjang</span>
            </div>
        </div>
        <div class="rounded-2xl border border-teal-200 bg-teal-50/50 p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-teal-800">Semester Aktif Saat Ini</span>
            <div class="mt-2 flex items-baseline justify-between gap-2">
                <span class="truncate text-lg sm:text-xl font-extrabold text-teal-950">{{ $activePeriod?->name ?? 'Belum Ditetapkan' }}</span>
                @if ($activePeriod)
                    <span class="shrink-0 rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800">Aktif</span>
                @endif
            </div>
        </div>
    </div>

    @include('academic-structure.units')
    @include('academic-structure.periods')
</div>
@endsection
