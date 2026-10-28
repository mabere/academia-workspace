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
        ['pillar' => 'Pendidikan & Pengajaran', 'name' => 'WriteWise ELT', 'description' => 'Tugas menulis, revisi mahasiswa, dan feedback dosen.', 'status' => 'Pilot tersedia', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'LitSynth ELT', 'description' => 'Sumber privat, matriks kajian, dan blueprint instrumen.', 'status' => 'Tersedia · lokal', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'Validasi pakar LitSynth', 'description' => 'Undangan per versi, respons ahli, dan ringkasan Aiken’s V/CVI.', 'status' => 'Tersedia', 'tone' => 'green'],
        ['pillar' => 'Pengabdian', 'name' => 'GuruBahasa AI', 'description' => 'Studio draf dan kurasi materi bahasa.', 'status' => 'Manual tersedia · AI nonaktif', 'tone' => 'amber'],
        ['pillar' => 'Lintas pilar', 'name' => 'Output & Evidence', 'description' => 'Temukan, pratinjau, dan unduh artefak modul aktif sesuai akses proyek.', 'status' => 'Tersedia untuk staf', 'tone' => 'green'],
        ['pillar' => 'Penelitian', 'name' => 'IndoEFL CorpusLab', 'description' => 'Korpus lokal dengan gerbang protokol etik, persetujuan, anonimisasi, anotasi, KWIC, dan ekspor terbatas.', 'status' => 'Tersedia setelah protokol disetujui', 'tone' => 'amber'],
    ];
@endphp

{{-- HEADER GREETING --}}
<section class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        @if(auth()->user()->isStaff())
            <p class="text-xs font-bold uppercase tracking-wider text-teal-800">Ruang Kerja Tri Dharma Dosen</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-1.5 text-xs sm:text-sm text-slate-600">{{ $roleLabels[auth()->user()->role] ?? 'Pengguna' }} · Ringkasan aktivitas dan kinerja akademik Anda.</p>
        @elseif(auth()->user()->role === 'student')
            <p class="text-xs font-bold uppercase tracking-wider text-teal-800">Portal Belajar Mahasiswa</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-1.5 text-xs sm:text-sm text-slate-600">
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
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Ruang Publik</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-1.5 text-xs sm:text-sm text-slate-600">Selamat datang di Academia Workspace.</p>
        @endif
    </div>

    @if(auth()->user()->isStaff())
        <div class="flex items-center gap-2">
            <a href="{{ route('courses.create') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                + Tambah Mata Kuliah
            </a>
            <a href="{{ route('projects.create') }}" class="rounded-xl bg-[#05285c] px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-[#041e45] transition">
                + Buat Proyek
            </a>
        </div>
    @elseif(auth()->user()->role === 'student')
        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#05285c] px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-[#041e45] transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            Buka Mata Kuliah Saya
        </a>
    @endif
</section>

{{-- TAMPILAN 1: DOSEN / STAFF --}}
@if(auth()->user()->isStaff())
    {{-- MODUL ACADEMIC HUB (KHUSUS DOSEN / STAFF) --}}
    <section aria-labelledby="modules-heading" class="mb-10">
        <div class="mb-4">
            <h2 id="modules-heading" class="text-xl font-bold text-slate-900">Modul Ruang Kerja Tri Dharma</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-600">Alat bantu pengajaran, sintesis riset literatur, dan diseminasi pengabdian masyarakat.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($modules as $module)
                <article class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs hover:border-slate-300 transition">
                    <p class="text-xs font-bold uppercase tracking-wider text-teal-800">{{ $module['pillar'] }}</p>
                    <h3 class="mt-2 text-base font-bold text-slate-900">{{ $module['name'] }}</h3>
                    <p class="mt-1.5 min-h-12 text-xs leading-relaxed text-slate-600">{{ $module['description'] }}</p>
                    <span class="mt-3 inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $module['tone'] === 'green' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/60' : 'bg-amber-50 text-amber-800 border border-amber-200/60' }}">
                        {{ $module['status'] }}
                    </span>
                    @if($module['name'] === 'Output & Evidence')
                        <div class="mt-3 pt-2 border-t border-slate-100">
                            <a href="{{ route('outputs.index') }}" class="inline-flex text-xs font-bold text-teal-800 hover:text-teal-950">
                                Buka Output & Evidence &rarr;
                            </a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    {{-- RINGKASAN KERJA DOSEN --}}
    <section class="mt-10" aria-labelledby="work-summary-heading">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 id="work-summary-heading" class="text-xl font-bold text-slate-900">Ringkasan Kerja Dosen</h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-600">Dihitung dari proyek aktif yang Anda miliki atau kelola sebagai editor.</p>
            </div>
            <a href="{{ route('projects.index') }}" class="text-xs font-bold text-teal-800 hover:underline">
                Lihat Semua Proyek &rarr;
            </a>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach([['Proyek Aktif', $dashboardStats['projects']], ['Tugas WriteWise', $dashboardStats['assignments']], ['Submisi Mahasiswa', $dashboardStats['submissions']]] as [$label, $value])
                <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $label }}</p>
                    <p class="mt-1.5 text-3xl font-extrabold text-[#05285c]">{{ $value }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- PROYEK TERBARU --}}
    <section class="mt-10" aria-labelledby="projects-heading">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 id="projects-heading" class="text-xl font-bold text-slate-900">Proyek Tri Dharma Terbaru</h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-600">Wadah aktivitas penelitian, pengajaran, dan pengabdian.</p>
            </div>
        </div>
        @if($projects->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <h3 class="font-bold text-slate-800">Belum Ada Proyek Aktif</h3>
                <p class="mt-1 text-xs text-slate-500">Mulai buat proyek Tri Dharma untuk mengorganisir penugasan dan riset Anda.</p>
                <a href="{{ route('projects.create') }}" class="mt-4 inline-flex rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">
                    Buat Proyek Baru
                </a>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs transition hover:border-teal-500 hover:shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-base font-bold text-slate-900">{{ $project->name }}</h3>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-700">
                                {{ $project->owner_id === auth()->id() ? 'Pemilik' : 'Editor' }}
                            </span>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-500">{{ $project->assignments_count }} tugas &middot; {{ $project->members_count }} editor</p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach($project->pillars as $pillar)
                                <span class="rounded-md bg-teal-50 px-2 py-0.5 text-[10px] font-bold text-teal-900 border border-teal-200/50">
                                    {{ ['teaching'=>'Pengajaran', 'research'=>'Penelitian', 'community'=>'Pengabdian'][$pillar] ?? $pillar }}
                                </span>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- TUGAS TERBARU DOSEN --}}
    <section class="mt-10" aria-labelledby="assignments-heading">
        <h2 id="assignments-heading" class="text-xl font-bold text-slate-900">Tugas Perkuliahan Terbaru</h2>
        @if($assignments->isEmpty())
            <p class="mt-3 rounded-2xl border border-slate-200 bg-white p-6 text-xs text-slate-500">Belum ada tugas di proyek aktif yang Anda kelola.</p>
        @else
            <div class="mt-4 divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-2xs">
                @foreach($assignments as $assignment)
                    <a href="{{ route('assignments.show', $assignment) }}" class="flex flex-wrap items-center justify-between gap-2 p-4 text-xs hover:bg-slate-50/80 transition">
                        <span>
                            <span class="font-bold text-slate-900 text-sm">{{ $assignment->title }}</span>
                            <span class="ml-2 text-slate-500 font-medium">{{ $assignment->project->name }}</span>
                        </span>
                        <span class="text-slate-600 font-medium">
                            <span class="font-bold text-teal-800">{{ $assignment->submissions_count }}</span> submisi dikumpulkan &middot;
                            <span class="font-bold">{{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

{{-- TAMPILAN 2: MAHASISWA --}}
@elseif(auth()->user()->role === 'student')
    <div class="space-y-10">
        {{-- STATISTIK BELAJAR MAHASISWA (4 KPI CARDS) --}}
        <section aria-labelledby="student-kpi-heading">
            <h2 id="student-kpi-heading" class="sr-only">Statistik Aktivitas Pembelajaran</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-teal-100 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Mata Kuliah Diikuti</p>
                    <p class="mt-1.5 text-3xl font-extrabold text-[#05285c]">{{ $dashboardStats['courses_count'] }}</p>
                    <p class="mt-1 text-[11px] text-teal-800 font-medium">Kelas aktif semester berjalan</p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Beban SKS</p>
                    <p class="mt-1.5 text-3xl font-extrabold text-blue-900">{{ $dashboardStats['total_sks'] }} SKS</p>
                    <p class="mt-1 text-[11px] text-slate-500">Beban kredit perkuliahan</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tugas Akademik</p>
                    <p class="mt-1.5 text-3xl font-extrabold text-amber-900">{{ $dashboardStats['assignments'] }}</p>
                    <p class="mt-1 text-[11px] text-slate-500">Tugas tersedia di WriteWise</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-2xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tugas Dikumpulkan</p>
                    <p class="mt-1.5 text-3xl font-extrabold text-emerald-900">{{ $dashboardStats['submissions'] }}</p>
                    <p class="mt-1 text-[11px] text-slate-500">Riwayat draf & submisi</p>
                </div>
            </div>
        </section>

        {{-- MATA KULIAH SAYA SEMESTER INI --}}
        <section aria-labelledby="student-courses-heading">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="student-courses-heading" class="text-xl font-bold text-slate-900">Mata Kuliah Semester Ini</h2>
                    <p class="mt-1 text-xs sm:text-sm text-slate-600">Akses langsung ruang kelas, bahan ajar, dan jadwal pertemuan Anda.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-xs font-bold text-teal-800 hover:text-teal-950 transition">
                    Lihat Semua Mata Kuliah &rarr;
                </a>
            </div>

            @if($enrolledCourses->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-800">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-slate-800">Belum Ada Mata Kuliah yang Terdaftar</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
                        Akun Anda belum ditambahkan ke kelas perkuliahan semester ini. Silakan hubungi dosen pengampu mata kuliah Anda agar ditambahkan ke daftar kelas.
                    </p>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($enrolledCourses as $course)
                        <div class="group flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs hover:border-teal-600 hover:shadow-sm transition">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-900 border border-teal-200/60">
                                        {{ $course->code }} &middot; {{ $course->class_group }}
                                    </span>
                                    <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                                        {{ $course->credit_hours }} SKS
                                    </span>
                                </div>

                                <h3 class="mt-3 text-base font-bold text-slate-900 group-hover:text-teal-800 transition line-clamp-1">
                                    <a href="{{ route('courses.show', $course) }}" title="{{ $course->name }}">
                                        {{ $course->name }}
                                    </a>
                                </h3>

                                <p class="mt-1 text-xs font-medium text-slate-500">
                                    Dosen: <strong class="text-slate-700">{{ $course->lecturer->name }}</strong>
                                </p>

                                <div class="mt-2.5 flex items-center gap-1.5 text-xs text-slate-600">
                                    <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="truncate">{{ $course->scheduleLabel() }} ({{ $course->room ?: 'Daring' }})</span>
                                </div>

                                <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                    <span><strong>{{ $course->materials_count }}</strong> Bahan Ajar</span>
                                    <span><strong>{{ $course->sessions_count }}/16</strong> Sesi Berjalan</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-slate-400">
                                    {{ $course->academicPeriod?->name }}
                                </span>
                                <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-1 text-xs font-bold text-teal-800 hover:text-teal-950 transition">
                                    Masuk Kelas &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- TUGAS & REVISI SAYA (WRITEWISE) --}}
        <section aria-labelledby="student-work-heading" class="scroll-mt-6">
            <div class="mb-4">
                <h2 id="student-work-heading" class="text-xl font-bold text-slate-900">Tugas dan Revisi Saya</h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-600">Tugas penulisan akademik yang ditugaskan kepada akun Anda di WriteWise.</p>
            </div>

            @if($assignments->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-xs text-slate-500">
                    Belum ada tugas akademik yang ditugaskan kepada akun Anda saat ini.
                </div>
            @else
                <div class="divide-y divide-slate-100 rounded-2xl border border-slate-200/90 bg-white overflow-hidden shadow-2xs">
                    @foreach($assignments as $assignment)
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 hover:bg-slate-50/70 transition">
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-sm">
                                    <a href="{{ route('assignments.show', $assignment) }}" class="hover:text-teal-800">
                                        {{ $assignment->title }}
                                    </a>
                                </h3>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $assignment->project->name }}
                                    @if($assignment->due_at)
                                        &middot; <span class="text-amber-700 font-medium">Batas Waktu: {{ $assignment->due_at->translatedFormat('d F Y, H:i') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('assignments.show', $assignment) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-800 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                                    <span>Buka Tugas</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

{{-- TAMPILAN 3: PENGGUNA UMUM --}}
@else
    <section class="mt-10 rounded-3xl border border-slate-200 bg-white p-8 shadow-xs">
        <h2 class="text-xl font-bold text-slate-900">Selamat datang di Academia Workspace</h2>
        <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed max-w-2xl">
            Akun umum dapat mengakses fitur publik dan materi terbuka. Akses kelas perkuliahan, penugasan, dan pelaporan Tri Dharma hanya dapat diakses oleh Dosen dan Mahasiswa yang terdaftar.
        </p>
        <a href="{{ route('home') }}" class="mt-5 inline-flex items-center gap-1.5 font-bold text-xs text-teal-800 hover:text-teal-950">
            <span>Buka Beranda Utama</span>
            <span>&rarr;</span>
        </a>
    </section>
@endif
@endsection