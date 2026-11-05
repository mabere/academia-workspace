@extends('layouts.hub')
@section('title', 'Direktori & Plotting Mahasiswa — Academic Hub')

@section('content')
<div class="space-y-6" x-data="{ panel: '{{ $errors->has('raw_list') ? 'bulk' : ($errors->any() ? 'single' : 'none') }}' }">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-teal-800 border border-teal-200/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                    Manajemen Peserta Perkuliahan
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Direktori & Plotting Mahasiswa</h1>
            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600">
                Kelola daftar mahasiswa pribadi Anda lintas Program Studi dan Angkatan, serta assign mahasiswa secara satuan maupun kolektif ke Mata Kuliah yang sedang diprogramkan.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" @click="panel = panel === 'single' ? 'none' : 'single'"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Mahasiswa</span>
            </button>
            <button type="button" @click="panel = panel === 'bulk' ? 'none' : 'bulk'"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-800 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-teal-900 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Paste Kolektif (Excel/SIAKAD)</span>
            </button>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Terdapat kesalahan input:</p>
            <ul class="mt-1 list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Statistik Ringkas --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Mahasiswa</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($totalStudents) }}</span>
                <span class="text-xs font-medium text-slate-500">Terdaftar di Direktori</span>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-teal-800">Aktif Memprogram MK</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-teal-900">{{ number_format($enrolledStudentsCount) }}</span>
                <span class="text-xs font-medium text-teal-700">Mahasiswa Ter-assign</span>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mata Kuliah Tersedia</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $courses->count() }}</span>
                <a href="{{ route('courses.index') }}" class="text-xs font-bold text-teal-800 hover:underline">Lihat Kelas &rarr;</a>
            </div>
        </div>
    </div>

    @include('students.single-create')
    @include('students.bulk-import')
    @include('students.directory-table')
</div>
@endsection
