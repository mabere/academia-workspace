@extends('layouts.hub')
@section('title', 'Fakultas, Program Studi & Periode — Academic Hub')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-teal-700">Master Unit Akademik Dosen</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Fakultas, Program Studi & Semester</h1>
            <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-500">
                Kelola identitas Fakultas, Program Studi tempat Anda mengajar, serta Periode Semester aktif untuk kelengkapan atribut Mata Kuliah, Daftar Nilai A4, dan Laporan BKD Dikti.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('students.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                <span>Direktori Mahasiswa</span>
            </a>
            <a href="{{ route('courses.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-800 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-900 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Mata Kuliah</span>
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-800">
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
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Fakultas</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-slate-900">{{ $faculties->count() }}</span>
                <span class="text-xs text-slate-500">Unit Fakultas</span>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Program Studi</span>
            <div class="mt-1 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-teal-800">{{ $studyPrograms->count() }}</span>
                <span class="text-xs text-slate-500">Lintas Jenjang</span>
            </div>
        </div>
        <div class="rounded-2xl border border-teal-200 bg-teal-50/50 p-4 shadow-sm">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-teal-700">Semester Aktif Saat Ini</span>
            <div class="mt-1 flex items-baseline justify-between gap-2">
                <span class="truncate text-base font-bold text-teal-950">{{ $activePeriod?->name ?? 'Belum Ditetapkan' }}</span>
                @if ($activePeriod)
                    <span class="shrink-0 rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-800">Aktif</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Grid Utama: Kiri = Fakultas, Kanan = Program Studi --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- KOLOM KIRI: FAKULTAS (5 Kolom) --}}
        <section class="lg:col-span-5 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">1. Daftar Fakultas</h2>
                    <p class="text-xs text-slate-500">Tambahkan fakultas yang menaungi program studi pengajaran Anda.</p>
                </div>

                {{-- Form Tambah Fakultas --}}
                <form method="post" action="{{ route('academic-structure.faculties.store') }}" class="mt-4 space-y-3 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                    @csrf
                    <p class="text-xs font-bold text-slate-700">+ Tambah Fakultas Baru</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Kode Singkat *</label>
                            <input type="text" name="code" required maxlength="20" placeholder="FKIP" value="{{ old('code') }}"
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs uppercase font-bold">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-600">Nama Fakultas *</label>
                            <input type="text" name="name" required maxlength="150" placeholder="Fakultas Keguruan dan Ilmu Pendidikan" value="{{ old('name') }}"
                                class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600">Deskripsi / Keterangan (Opsional)</label>
                        <input type="text" name="description" maxlength="500" placeholder="Keterangan singkat fakultas..." value="{{ old('description') }}"
                            class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition">
                            Simpan Fakultas
                        </button>
                    </div>
                </form>

                {{-- List Fakultas --}}
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($faculties as $faculty)
                        <div class="py-3.5 first:pt-0 last:pb-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded bg-slate-900 px-2 py-0.5 font-mono text-[11px] font-bold text-white">{{ $faculty->code }}</span>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $faculty->name }}</h3>
                                    </div>
                                    @if ($faculty->description)
                                        <p class="mt-1 text-xs text-slate-500">{{ $faculty->description }}</p>
                                    @endif
                                    <p class="mt-1 text-[11px] font-medium text-teal-700">{{ $faculty->study_programs_count }} Program Studi</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if ($faculty->study_programs_count === 0)
                                        <form method="post" action="{{ route('academic-structure.faculties.destroy', $faculty) }}"
                                            onsubmit="return confirm('Hapus fakultas {{ $faculty->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Edit Fakultas Inline --}}
                            <details class="mt-2 group">
                                <summary class="cursor-pointer text-[11px] font-semibold text-slate-500 hover:text-teal-700">
                                    Edit Fakultas
                                </summary>
                                <form method="post" action="{{ route('academic-structure.faculties.update', $faculty) }}"
                                    class="mt-2 space-y-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid gap-2 sm:grid-cols-3">
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-600">Kode</label>
                                            <input type="text" name="code" required maxlength="20" value="{{ $faculty->code }}"
                                                class="mt-0.5 w-full rounded border-slate-300 bg-white text-xs uppercase font-bold">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-[10px] font-semibold text-slate-600">Nama Fakultas</label>
                                            <input type="text" name="name" required maxlength="150" value="{{ $faculty->name }}"
                                                class="mt-0.5 w-full rounded border-slate-300 bg-white text-xs">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600">Deskripsi</label>
                                        <input type="text" name="description" maxlength="500" value="{{ $faculty->description }}"
                                            class="mt-0.5 w-full rounded border-slate-300 bg-white text-xs">
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="rounded bg-teal-800 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-teal-900">
                                            Perbarui Fakultas
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-500">Belum ada data fakultas. Silakan tambahkan fakultas terlebih dahulu.</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- KOLOM KANAN: PROGRAM STUDI (7 Kolom) --}}
        <section class="lg:col-span-7 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">2. Daftar Program Studi (Prodi)</h2>
                    <p class="text-xs text-slate-500">Program studi tempat mata kuliah diselenggarakan dan homebase mahasiswa.</p>
                </div>

                {{-- Form Tambah Prodi --}}
                @if ($faculties->isEmpty())
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900">
                        Tambahkan minimal satu <strong>Fakultas</strong> di kolom sebelah kiri terlebih dahulu sebelum membuat Program Studi.
                    </div>
                @else
                    <form method="post" action="{{ route('academic-structure.study-programs.store') }}" class="mt-4 space-y-3 rounded-xl border border-teal-200 bg-teal-50/40 p-4">
                        @csrf
                        <p class="text-xs font-bold text-teal-950">+ Tambah Program Studi Baru</p>
                        <div class="grid gap-3 sm:grid-cols-12">
                            <div class="sm:col-span-5">
                                <label class="block text-[11px] font-semibold text-slate-700">Fakultas Induk *</label>
                                <select name="faculty_id" required class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                                    @foreach ($faculties as $fac)
                                        <option value="{{ $fac->id }}">{{ $fac->code }} — {{ $fac->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700">Kode Prodi *</label>
                                <input type="text" name="code" required maxlength="20" placeholder="PBI-S1"
                                    class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs uppercase font-bold">
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-[11px] font-semibold text-slate-700">Jenjang *</label>
                                <select name="level" required class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs font-semibold">
                                    @foreach (['S1', 'S2', 'S3', 'D4', 'D3', 'Profesi'] as $lvl)
                                        <option value="{{ $lvl }}">{{ $lvl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-[11px] font-semibold text-slate-700">Nama Program Studi *</label>
                                <input type="text" name="name" required maxlength="150" placeholder="Pendidikan Bahasa Inggris"
                                    class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                            </div>
                            <button type="submit" class="rounded-lg bg-teal-800 px-4 py-2.5 text-xs font-semibold text-white hover:bg-teal-900 transition">
                                Simpan Prodi
                            </button>
                        </div>
                    </form>
                @endif

                {{-- Tabel Program Studi --}}
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($studyPrograms as $prodi)
                        <div class="py-3.5 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded bg-teal-100 px-2 py-0.5 font-mono text-[11px] font-bold text-teal-900">{{ $prodi->code }}</span>
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700">{{ $prodi->level }}</span>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $prodi->name }}</h3>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Fakultas: <strong class="text-slate-700">{{ $prodi->faculty?->name ?? '-' }} ({{ $prodi->faculty?->code ?? '-' }})</strong>
                                    </p>
                                    <div class="mt-1.5 flex items-center gap-3 text-[11px] font-semibold text-slate-600">
                                        <span>{{ $prodi->courses_count }} Mata Kuliah</span>
                                        <span>&middot;</span>
                                        <a href="{{ route('students.index', ['study_program_id' => $prodi->id]) }}" class="text-teal-700 hover:underline">
                                            {{ $prodi->students_count }} Mahasiswa Terdaftar
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($prodi->courses_count === 0)
                                        <form method="post" action="{{ route('academic-structure.study-programs.destroy', $prodi) }}"
                                            onsubmit="return confirm('Hapus Program Studi {{ $prodi->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Edit Prodi Inline --}}
                            <details class="mt-2 group">
                                <summary class="cursor-pointer text-[11px] font-semibold text-slate-500 hover:text-teal-700">
                                    Edit Program Studi
                                </summary>
                                <form method="post" action="{{ route('academic-structure.study-programs.update', $prodi) }}"
                                    class="mt-2 space-y-2.5 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid gap-2 sm:grid-cols-12">
                                        <div class="sm:col-span-5">
                                            <label class="block text-[10px] font-semibold text-slate-600">Fakultas</label>
                                            <select name="faculty_id" required class="mt-0.5 w-full rounded border-slate-300 bg-white text-xs">
                                                @foreach ($faculties as $fac)
                                                    <option value="{{ $fac->id }}" @selected($prodi->faculty_id === $fac->id)>{{ $fac->code }} — {{ $fac->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block text-[10px] font-semibold text-slate-600">Kode Prodi</label>
                                            <input type="text" name="code" required maxlength="20" value="{{ $prodi->code }}"
                                                class="mt-0.5 w-full rounded border-slate-300 bg-white text-xs uppercase font-bold">
                                        </div>
                                        <div class="sm:col-span-4">
                                            <label class="block text-[10px] font-semibold text-slate-600">Jenjang</label>
                                            <select name="level" required class="mt-0.5 w-full rounded border-slate-300 bg-white text-xs">
                                                @foreach (['S1', 'S2', 'S3', 'D4', 'D3', 'Profesi'] as $lvl)
                                                    <option value="{{ $lvl }}" @selected($prodi->level === $lvl)>{{ $lvl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-end gap-2">
                                        <div class="flex-1">
                                            <label class="block text-[10px] font-semibold text-slate-600">Nama Program Studi</label>
                                            <input type="text" name="name" required maxlength="150" value="{{ $prodi->name }}"
                                                class="mt-0.5 w-full rounded border-slate-300 bg-white text-xs">
                                        </div>
                                        <button type="submit" class="rounded bg-teal-800 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-teal-900">
                                            Perbarui Prodi
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-500">Belum ada Program Studi yang terdaftar.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    {{-- BAGIAN 3: PERIODE SEMESTER AKADEMIK --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">3. Periode Semester Akademik</h2>
                <p class="text-xs text-slate-500">Atur tahun akademik dan semester aktif untuk pengelompokan mata kuliah serta pelaporan BKD.</p>
            </div>
        </div>

        <div class="mt-4 grid gap-6 lg:grid-cols-12">
            {{-- Form Tambah Periode --}}
            <form method="post" action="{{ route('academic-structure.periods.store') }}" class="lg:col-span-5 space-y-3 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                @csrf
                <p class="text-xs font-bold text-slate-800">+ Tambah Periode Semester Baru</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600">Nama Periode *</label>
                        <input type="text" name="name" required maxlength="80" placeholder="2026/2027 Genap"
                            class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600">Tahun Akademik *</label>
                        <input type="text" name="academic_year" required maxlength="20" placeholder="2026/2027"
                            class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600">Semester *</label>
                        <select name="semester" required class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                            <option value="odd">Ganjil (Odd)</option>
                            <option value="even">Genap (Even)</option>
                            <option value="short">Antara (Short)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required value="{{ now()->format('Y-m-d') }}"
                            class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600">Tanggal Selesai *</label>
                        <input type="date" name="end_date" required value="{{ now()->addMonths(5)->format('Y-m-d') }}"
                            class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        <span>Tetapkan sebagai semester aktif</span>
                    </label>
                    <button type="submit" class="rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition">
                        Simpan Periode
                    </button>
                </div>
            </form>

            {{-- Daftar Periode --}}
            <div class="lg:col-span-7 overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="py-2.5 px-3">Periode Semester</th>
                            <th class="py-2.5 px-3">Rentang Tanggal</th>
                            <th class="py-2.5 px-3 text-center">Mata Kuliah</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($academicPeriods as $period)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $period->name }}</div>
                                    <div class="text-[11px] text-slate-500">TA {{ $period->academic_year }}</div>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $period->start_date?->format('d/m/Y') }} &ndash; {{ $period->end_date?->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3 text-center font-semibold text-slate-700">
                                    {{ $period->courses_count }} MK
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if ($period->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800">
                                            Semester Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                            Arsip / Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if (! $period->is_active)
                                            <form method="post" action="{{ route('academic-structure.periods.activate', $period) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-teal-200 bg-teal-50 px-2.5 py-1 text-[11px] font-semibold text-teal-800 hover:bg-teal-100 transition">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                        @if ($period->courses_count === 0 && $period->projects_count === 0)
                                            <form method="post" action="{{ route('academic-structure.periods.destroy', $period) }}"
                                                onsubmit="return confirm('Hapus periode {{ $period->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">Belum ada periode akademik.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
