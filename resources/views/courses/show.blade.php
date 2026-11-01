@extends('layouts.hub')
@section('title', $course->name . ' (' . $course->code . ') — Academic Hub')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb Navigation --}}
    <nav class="flex items-center gap-2 text-xs font-medium text-slate-500">
        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-1.5 hover:text-teal-800 transition">
            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Mata Kuliah</span>
        </a>
        <svg class="h-3 w-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-mono font-bold text-teal-800 uppercase tracking-wide">{{ $course->code }}</span>
        <svg class="h-3 w-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-slate-800 font-semibold truncate max-w-[220px] sm:max-w-md">{{ $course->name }}</span>
    </nav>

    {{-- Executive Hero Banner & Cockpit Header --}}
    <div class="relative overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xs">
        {{-- Brand Accent Top Strip --}}
        <div class="h-2 w-full bg-gradient-to-r from-teal-800 via-teal-600 to-emerald-500"></div>

        <div class="p-6 sm:p-8 space-y-6">
            {{-- Baris Atas: Metadata Pills & Tombol Tindakan --}}
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-3 max-w-3xl">
                    {{-- Badges Strip --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-teal-50 px-3 py-1 font-mono text-xs font-extrabold uppercase tracking-wider text-teal-900 border border-teal-200/80 shadow-2xs">
                            <span class="h-2 w-2 rounded-full bg-teal-700"></span>
                            {{ $course->code }} · {{ $course->class_group }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 border border-slate-200/60">
                            {{ $course->credit_hours }} SKS
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-xl bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200/70">
                            {{ $course->academicPeriod->name }} · Semester {{ $course->semester_level }}
                        </span>
                        @if($course->isCompleted())
                            <span class="inline-flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 border border-slate-200">
                                Selesai
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-xl bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 border border-emerald-200/80">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Aktif Berjalan
                            </span>
                        @endif
                    </div>

                    {{-- Judul Mata Kuliah --}}
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl leading-snug">
                        {{ $course->name }}
                    </h1>

                    {{-- Detail Pengampu, Jadwal, & Fakultas --}}
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 pt-1 text-xs sm:text-sm text-slate-600">
                        {{-- Dosen Pengampu --}}
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-700 to-teal-900 font-bold text-xs text-white shadow-2xs">
                                {{ strtoupper(substr($course->lecturer->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-600">Dosen Pengampu</span>
                                <span class="block font-bold text-slate-900 truncate" title="{{ $course->lecturer->name }}">{{ $course->lecturer->name }}</span>
                            </div>
                        </div>

                        {{-- Jadwal & Ruang Kuliah --}}
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-800 border border-teal-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-600">Jadwal & Ruang</span>
                                <span class="block font-bold text-slate-800 truncate" title="{{ $course->scheduleLabel() }}">{{ $course->scheduleLabel() }}</span>
                            </div>
                        </div>

                        {{-- Program Studi & Fakultas --}}
                        <div class="flex items-center gap-2.5 sm:col-span-2 lg:col-span-1">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 border border-slate-200">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-600">Program Studi</span>
                                <span class="block font-bold text-slate-800 truncate" title="{{ $course->studyProgram->name }} ({{ $course->studyProgram->faculty->name ?? 'Fakultas' }})">
                                    {{ $course->studyProgram->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Cluster Kanan --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    @if(auth()->user()->isStaff())
                        <a href="{{ route('courses.sessions.print', $course) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-teal-900 transition">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Jurnal</span>
                        </a>
                    @endif

                    @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                        <a href="{{ route('courses.edit', $course) }}"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-teal-900 transition">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Edit MK</span>
                        </a>
                    @endif

                    <a href="{{ route('courses.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                        <span>&larr; Kembali</span>
                    </a>
                </div>
            </div>

            {{-- 4 Kartu Metrik Ringkas Cockpit (Executive Metrics Strip) --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 pt-2 border-t border-slate-100">
                {{-- KPI 1: Progress Sesi 16 Pertemuan --}}
                <div class="rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50/60 to-white p-3.5 shadow-2xs">
                    <div class="flex items-center justify-between text-slate-600 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-teal-800">Linimasa Sesi</span>
                        <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $sessions->count() }}</span>
                        <span class="text-xs font-bold text-slate-600">/ 16 Pertemuan</span>
                    </div>
                    <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-teal-700 transition-all duration-500" style="width: {{ min(100, round(($sessions->count() / 16) * 100)) }}%"></div>
                    </div>
                </div>

                {{-- KPI 2: Bahan Ajar Terbit --}}
                <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50/50 to-white p-3.5 shadow-2xs">
                    <div class="flex items-center justify-between text-slate-600 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Bahan Ajar</span>
                        <svg class="h-4 w-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $materials->count() }}</span>
                        <span class="text-xs font-bold text-slate-600">Modul Terbit</span>
                    </div>
                    <p class="mt-2 text-[11px] text-blue-950 font-semibold truncate">Multi-format siap akses</p>
                </div>

                {{-- KPI 3: Penugasan & Asesmen --}}
                <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50/50 to-white p-3.5 shadow-2xs">
                    <div class="flex items-center justify-between text-slate-600 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-900">Penugasan</span>
                        <svg class="h-4 w-4 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $assignments->count() }}</span>
                        <span class="text-xs font-bold text-slate-600">Tugas Kuliah</span>
                    </div>
                    <p class="mt-2 text-[11px] text-indigo-950 font-semibold truncate">Terintegrasi WriteWise</p>
                </div>

                {{-- KPI 4: Total Peserta Kelas --}}
                <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50/50 to-white p-3.5 shadow-2xs">
                    <div class="flex items-center justify-between text-slate-600 mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900">Peserta Kelas</span>
                        <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $students->count() }}</span>
                        <span class="text-xs font-bold text-slate-600">Mahasiswa</span>
                    </div>
                    <p class="mt-2 text-[11px] text-emerald-950 font-semibold truncate">Terdaftar di kelas</p>
                </div>
            </div>

            {{-- Kompartemen Modular CPMK & Deskripsi (Collapsible / Compact) --}}
            @if($course->description || $course->syllabus)
                @php
                    $cpmkItems = $course->cpmkList();
                @endphp
                <div class="border-t border-slate-100 pt-4" x-data="{ expanded: false }">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-md bg-teal-800 text-white shadow-2xs">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800">
                                Deskripsi & Capaian Pembelajaran (CPMK)
                            </span>
                        </div>
                        <button type="button" @click="expanded = !expanded" class="inline-flex items-center gap-1 text-xs font-bold text-teal-800 hover:text-teal-950 transition">
                            <span x-text="expanded ? 'Sembunyikan Detail' : 'Buka Detail Lengkap'"></span>
                            <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    {{-- Default Compact Preview (1 Baris Ringkas) --}}
                    <div x-show="!expanded" class="rounded-2xl bg-slate-50/80 border border-slate-200/80 p-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <p class="text-slate-600 truncate max-w-2xl">
                            <strong>Deskripsi:</strong> {{ Str::limit($course->description ?: 'Belum ada deskripsi singkat untuk mata kuliah ini.', 110) }}
                        </p>
                        <div class="flex items-center gap-2">
                            @if(count($cpmkItems) > 0)
                                <span class="rounded-full bg-teal-100 px-2.5 py-0.5 font-mono font-bold text-teal-900 text-[11px]">
                                    {{ count($cpmkItems) }} Butir CPMK
                                </span>
                            @endif
                            <button type="button" @click="expanded = true" class="text-xs font-bold text-teal-800 hover:underline">
                                Baca Selengkapnya &rarr;
                            </button>
                        </div>
                    </div>

                    {{-- Expanded Two-Column Cards --}}
                    <div x-show="expanded" x-cloak class="mt-3 grid gap-4 md:grid-cols-2 text-xs sm:text-sm">
                        {{-- Kartu Deskripsi Mata Kuliah --}}
                        @if($course->description)
                            <div class="rounded-2xl bg-slate-50/90 border border-slate-200 p-5 shadow-2xs">
                                <div class="flex items-center gap-2 text-slate-800 font-bold text-xs uppercase tracking-wider mb-2.5">
                                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    <span>Deskripsi Mata Kuliah</span>
                                </div>
                                <p class="leading-relaxed text-slate-700 whitespace-pre-line text-xs sm:text-sm">
                                    {{ $course->description }}
                                </p>
                            </div>
                        @endif

                        {{-- Kartu Capaian Pembelajaran (CPMK) Berpenomoran --}}
                        @if($course->syllabus)
                            <div class="rounded-2xl bg-teal-50/50 border border-teal-200/90 p-5 shadow-2xs">
                                <div class="flex items-center justify-between gap-2 mb-2.5">
                                    <div class="flex items-center gap-2 text-teal-950 font-bold text-xs uppercase tracking-wider">
                                        <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Capaian Pembelajaran (CPMK)</span>
                                    </div>
                                    @if(count($cpmkItems) > 0)
                                        <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900 border border-teal-200">
                                            {{ count($cpmkItems) }} Butir Capaian
                                        </span>
                                    @endif
                                </div>

                                @if(count($cpmkItems) > 1)
                                    <ol class="space-y-2.5">
                                        @foreach($cpmkItems as $idx => $item)
                                            <li class="flex items-start gap-2.5 text-slate-800 leading-relaxed text-xs sm:text-sm">
                                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-teal-800 text-[11px] font-black text-white mt-0.5 shadow-2xs">
                                                    {{ $idx + 1 }}
                                                </span>
                                                <span class="flex-1">{{ preg_replace('/^(?:(?:\d+|cpmk\s*\d*)[.:\)]\s*|[-*•]\s*)/iu', '', $item) }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                @elseif(count($cpmkItems) === 1)
                                    <div class="flex items-start gap-2.5 text-slate-800 leading-relaxed text-xs sm:text-sm">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-teal-800 text-[11px] font-black text-white mt-0.5 shadow-2xs">
                                            1
                                        </span>
                                        <span class="flex-1">{{ preg_replace('/^(?:(?:\d+|cpmk\s*\d*)[.:\)]\s*|[-*•]\s*)/iu', '', $cpmkItems[0]) }}</span>
                                    </div>
                                @else
                                    <p class="leading-relaxed text-slate-700 whitespace-pre-line text-xs sm:text-sm">{{ $course->syllabus }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Main Tabbed Interactive Command Center --}}
    <div x-data="courseDetailDashboard()" class="space-y-6">

        {{-- Banner Notifikasi Presensi Aktif untuk Mahasiswa (Hanya Muncul Jika Belum Hadir & Tidak Sedang di Tab Sesi) --}}
        @php
            $activeSession = $course->activeAttendanceSession();
            $myActiveAttendance = (!auth()->user()->isStaff() && $activeSession) ? $activeSession->studentAttendance(auth()->id()) : null;
        @endphp

        @if(!auth()->user()->isStaff() && $activeSession && !$myActiveAttendance)
            <div x-show="tab !== 'sessions'" x-cloak class="relative overflow-hidden rounded-2xl border-2 border-teal-500 bg-gradient-to-r from-teal-50 via-emerald-50 to-teal-50 p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="h-12 w-12 rounded-2xl bg-teal-800 text-white flex items-center justify-center font-black text-xl shrink-0 shadow-sm animate-bounce">
                            ⚡
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-teal-800 px-3 py-0.5 text-[11px] font-extrabold text-white uppercase tracking-wider">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 animate-ping"></span>
                                    Presensi Kelas Sedang Dibuka
                                </span>
                                <span class="text-sm font-extrabold text-slate-900">
                                    Pertemuan Ke-{{ $activeSession->session_number }}: {{ $activeSession->topic }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">
                                Masukkan 4-digit PIN presensi yang ditampilkan oleh dosen pengampu di layar proyektor kelas:
                            </p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('courses.sessions.check-in', [$course, $activeSession]) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="passcode" required maxlength="4" pattern="[0-9]{4}" placeholder="0000"
                            class="w-28 text-center font-mono font-black tracking-widest text-xl rounded-xl border-2 border-teal-400 focus:border-teal-700 focus:ring-teal-700 py-2.5 px-3 bg-white shadow-2xs">
                        <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2.5 text-xs sm:text-sm font-bold text-white hover:bg-teal-700 shadow-sm transition flex items-center gap-1.5">
                            <span>Kirim Presensi</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Segmented Tab Navigation Bar --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white p-1.5 shadow-2xs overflow-x-auto">
            <nav class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-bold whitespace-nowrap min-w-max">
                {{-- TAB 1: BAHAN AJAR --}}
                <button type="button" @click="tab = 'materials'"
                    :class="tab === 'materials'
                        ? 'bg-teal-800 text-white shadow-sm'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'"
                    class="rounded-xl px-4 py-2.5 transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Bahan Ajar Multi-Format</span>
                    <span :class="tab === 'materials' ? 'bg-teal-900/60 text-teal-100' : 'bg-slate-200/80 text-slate-700'"
                        class="rounded-full px-2 py-0.5 text-[11px] font-mono font-bold transition">
                        {{ $materials->count() }}
                    </span>
                </button>

                {{-- TAB 2: JURNAL & LINIMASA SESI --}}
                <button type="button" @click="tab = 'sessions'"
                    :class="tab === 'sessions'
                        ? 'bg-teal-800 text-white shadow-sm'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'"
                    class="rounded-xl px-4 py-2.5 transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Jurnal Perkuliahan & Presensi</span>
                    <span :class="tab === 'sessions' ? 'bg-teal-900/60 text-teal-100' : 'bg-slate-200/80 text-slate-700'"
                        class="rounded-full px-2 py-0.5 text-[11px] font-mono font-bold transition">
                        {{ $sessions->count() }}/16
                    </span>
                </button>

                {{-- TAB 3: PENUGASAN --}}
                <button type="button" @click="tab = 'assignments'"
                    :class="tab === 'assignments'
                        ? 'bg-teal-800 text-white shadow-sm'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'"
                    class="rounded-xl px-4 py-2.5 transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>{{ auth()->user()->isStaff() ? 'Penugasan & Penilaian' : 'Tugas Perkuliahan' }}</span>
                    <span :class="tab === 'assignments' ? 'bg-teal-900/60 text-teal-100' : 'bg-slate-200/80 text-slate-700'"
                        class="rounded-full px-2 py-0.5 text-[11px] font-mono font-bold transition">
                        {{ $assignments->count() }}
                    </span>
                </button>

                {{-- TAB 4: PESERTA KELAS --}}
                <button type="button" @click="tab = 'students'"
                    :class="tab === 'students'
                        ? 'bg-teal-800 text-white shadow-sm'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'"
                    class="rounded-xl px-4 py-2.5 transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>{{ auth()->user()->isStaff() ? 'Peserta Kelas' : 'Teman Sekelas' }}</span>
                    <span :class="tab === 'students' ? 'bg-teal-900/60 text-teal-100' : 'bg-slate-200/80 text-slate-700'"
                        class="rounded-full px-2 py-0.5 text-[11px] font-mono font-bold transition">
                        {{ $students->count() }}
                    </span>
                </button>
            </nav>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 1: BAHAN AJAR MULTI-FORMAT --}}
        {{-- ======================================================== --}}
        <div x-show="tab === 'materials'" class="space-y-6">
            {{-- Form Unggah Bahan Ajar Baru (Khusus Dosen / Staf) --}}
            @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                <div class="rounded-3xl border border-teal-200 bg-white p-6 shadow-xs" x-data="{ mOpen: false, mType: 'document' }">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-teal-50 text-teal-800 border border-teal-200 flex items-center justify-center font-bold text-lg shrink-0">
                                +
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Publikasi Bahan Ajar Perkuliahan</h3>
                                <p class="text-xs text-slate-500">Mendukung Dokumen (PDF, Word, PPT), Audio MP3, Video MP4, YouTube, Tautan, dan Teks Catatan.</p>
                            </div>
                        </div>

                        <button type="button" @click="mOpen = !mOpen"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-2xs transition">
                            <span x-text="mOpen ? 'Tutup Formulir' : '+ Unggah Bahan Ajar Baru'"></span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="mOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <form x-show="mOpen" x-cloak method="post" action="{{ route('courses.materials.store', $course) }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-slate-100 pt-5">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700">Judul Bahan Ajar *</label>
                                <input type="text" name="title" required placeholder="Contoh: Slide Pertemuan 3 - Essay Organization & Thesis Statement"
                                    class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Penempatan Pertemuan</label>
                                <select name="session_number" class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                                    <option value="">Materi Umum / Silabus RPS</option>
                                    @for($i = 1; $i <= 16; $i++)
                                        <option value="{{ $i }}">Pertemuan Ke-{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Format Bahan Ajar *</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-6">
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'document' ? 'border-teal-700 bg-teal-50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="document" x-model="mType" class="sr-only">
                                    <span>📄 Dokumen</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'audio' ? 'border-purple-600 bg-purple-50 text-purple-900 ring-1 ring-purple-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="audio" x-model="mType" class="sr-only">
                                    <span>🎧 Audio</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'video' ? 'border-rose-600 bg-rose-50 text-rose-900 ring-1 ring-rose-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="video" x-model="mType" class="sr-only">
                                    <span>🎥 Video</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'link' ? 'border-blue-600 bg-blue-50 text-blue-900 ring-1 ring-blue-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="link" x-model="mType" class="sr-only">
                                    <span>🔗 Tautan</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'text' ? 'border-amber-600 bg-amber-50 text-amber-900 ring-1 ring-amber-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="text" x-model="mType" class="sr-only">
                                    <span>📝 Teks</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="mType === 'archive' ? 'border-teal-700 bg-teal-50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="archive" x-model="mType" class="sr-only">
                                    <span>📦 Arsip ZIP</span>
                                </label>
                            </div>
                        </div>

                        {{-- File Input --}}
                        <div x-show="['document', 'audio', 'video', 'archive'].includes(mType)" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <label class="block text-xs font-bold text-slate-800">Unggah Berkas Materi (Maksimal 100 MB)</label>
                            <input type="file" name="file" class="mt-2 block w-full text-xs text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-teal-800 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-teal-700 cursor-pointer">
                            <p class="mt-1.5 text-[11px] text-slate-500">Format didukung: PDF, DOCX, PPTX, XLSX, TXT, MP3, WAV, M4A, OGG, MP4, WebM, ZIP.</p>
                        </div>

                        {{-- External Link Input --}}
                        <div x-show="mType === 'link'" x-cloak class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4">
                            <label class="block text-xs font-bold text-blue-950">Tautan / URL Eksternal *</label>
                            <input type="url" name="external_url" placeholder="https://www.youtube.com/watch?v=... atau Google Drive"
                                class="mt-1.5 w-full rounded-xl border-blue-300 text-xs sm:text-sm focus:border-blue-600 focus:ring-blue-600 bg-white">
                            <p class="mt-1.5 text-[11px] text-blue-800">Jika berupa link YouTube, player video akan disematkan secara otomatis di tampilan mahasiswa.</p>
                        </div>

                        {{-- Direct Text Input --}}
                        <div x-show="mType === 'text'" x-cloak class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4">
                            <label class="block text-xs font-bold text-amber-950">Isi Catatan / Bahan Bacaan Kuliah *</label>
                            <textarea name="content_text" rows="5" placeholder="Tuliskan materi, rangkuman teori, atau instruksi perkuliahan langsung..."
                                class="mt-1.5 w-full rounded-xl border-amber-300 text-xs sm:text-sm focus:border-amber-600 focus:ring-amber-600 bg-white"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700">Petunjuk Pembelajaran / Catatan untuk Mahasiswa</label>
                            <input type="text" name="description" placeholder="Misal: Pelajari sebelum pertemuan dimulai untuk diskusi kelompok."
                                class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                Simpan & Terbitkan Bahan Ajar
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Interactive Filter & Search Bar untuk Bahan Ajar --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                {{-- Filter Chips --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="materialFilter = 'all'"
                        :class="materialFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        Semua Format ({{ $materials->count() }})
                    </button>
                    <button type="button" @click="materialFilter = 'document'"
                        :class="materialFilter === 'document' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        📄 Dokumen / Slide
                    </button>
                    <button type="button" @click="materialFilter = 'audio'"
                        :class="materialFilter === 'audio' ? 'bg-purple-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        🎧 Audio
                    </button>
                    <button type="button" @click="materialFilter = 'video'"
                        :class="materialFilter === 'video' ? 'bg-rose-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        🎥 Video
                    </button>
                    <button type="button" @click="materialFilter = 'link'"
                        :class="materialFilter === 'link' ? 'bg-blue-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        🔗 Tautan
                    </button>
                    <button type="button" @click="materialFilter = 'text'"
                        :class="materialFilter === 'text' ? 'bg-amber-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        📝 Teks
                    </button>
                </div>

                {{-- Realtime Search Input --}}
                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="materialSearch" placeholder="Cari judul materi..."
                        class="w-full rounded-xl border-slate-300 pl-9 pr-3 py-1.5 text-xs focus:border-teal-700 focus:ring-teal-700 bg-slate-50/50">
                    <svg class="absolute left-3 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- Empty State --}}
            @if($materials->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-2xs">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-50 text-teal-700 mb-3">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Bahan Ajar Diterbitkan</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                        Dosen pengampu belum mengunggah berkas modul atau tautan referensi untuk ruang perkuliahan ini.
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($materials as $material)
                        @php
                            $mTypeCategory = $material->material_type;
                            if ($material->isWordDocument() || $material->isPowerpoint() || $material->isPdf()) {
                                $mTypeCategory = 'document';
                            }
                        @endphp
                        <div x-show="(materialFilter === 'all' || materialFilter === '{{ $mTypeCategory }}') && ('{{ strtolower(addslashes($material->title)) }}'.includes(materialSearch.toLowerCase()) || '{{ strtolower(addslashes($material->description ?? '')) }}'.includes(materialSearch.toLowerCase()))"
                            class="group rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs hover:border-teal-300 hover:shadow-md transition">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="flex items-start gap-4 max-w-3xl">
                                    {{-- Format Visual Tile Badge --}}
                                    <div class="h-12 w-12 rounded-2xl flex items-center justify-center font-bold text-lg shrink-0 shadow-2xs
                                        {{ $material->isAudio() ? 'bg-gradient-to-br from-purple-600 to-indigo-700 text-white' :
                                           ($material->isVideo() ? 'bg-gradient-to-br from-rose-500 to-red-700 text-white' :
                                           ($material->isPdf() ? 'bg-gradient-to-br from-red-600 to-rose-700 text-white' :
                                           ($material->isWordDocument() ? 'bg-gradient-to-br from-blue-600 to-sky-700 text-white' :
                                           ($material->isPowerpoint() ? 'bg-gradient-to-br from-amber-600 to-orange-700 text-white' :
                                           ($material->isLink() ? 'bg-gradient-to-br from-sky-600 to-blue-700 text-white' :
                                           ($material->isText() ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white' : 'bg-gradient-to-br from-slate-700 to-slate-900 text-white')))))) }}">
                                        @if($material->isAudio()) 🎧
                                        @elseif($material->isVideo()) 🎥
                                        @elseif($material->isPdf()) 📄
                                        @elseif($material->isWordDocument()) 📝
                                        @elseif($material->isPowerpoint()) 📊
                                        @elseif($material->isLink()) 🔗
                                        @elseif($material->isText()) 📜
                                        @else 📦
                                        @endif
                                    </div>

                                    <div class="space-y-1.5 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider
                                                {{ $material->isAudio() ? 'bg-purple-100 text-purple-900 border border-purple-200' :
                                                   ($material->isVideo() ? 'bg-rose-100 text-rose-900 border border-rose-200' :
                                                   ($material->isLink() ? 'bg-blue-100 text-blue-900 border border-blue-200' :
                                                   ($material->isText() ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-slate-100 text-slate-800 border border-slate-200'))) }}">
                                                {{ $material->typeLabel() }}
                                            </span>

                                            @if($material->session_number)
                                                <span class="rounded-lg bg-teal-50 px-2.5 py-0.5 text-[11px] font-bold text-teal-800 border border-teal-200">
                                                    Pertemuan Ke-{{ $material->session_number }}
                                                </span>
                                            @else
                                                <span class="rounded-lg bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                                                    Materi Umum / Silabus RPS
                                                </span>
                                            @endif

                                            <span class="text-xs text-slate-400">· Diunggah {{ $material->created_at->diffForHumans() }}</span>
                                        </div>

                                        <h3 class="text-base font-extrabold text-slate-900 leading-snug">
                                            {{ $material->title }}
                                        </h3>

                                        @if($material->description)
                                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $material->description }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($material->file_path)
                                        @if($material->isPdf())
                                            <a href="{{ route('courses.materials.stream', [$course, $material]) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-teal-700 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Buka PDF</span>
                                            </a>
                                        @elseif($material->isWordDocument())
                                            <button type="button"
                                                @click="openDocPreview('{{ addslashes($material->title) }}', '{{ $material->getGoogleDocsViewerUrl($course) }}', '{{ route('courses.materials.download', [$course, $material]) }}')"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-blue-700 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-blue-800 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span>Lihat Dokumen</span>
                                            </button>
                                        @elseif($material->isPowerpoint())
                                            <button type="button"
                                                @click="openDocPreview('{{ addslashes($material->title) }}', '{{ $material->getGoogleDocsViewerUrl($course) }}', '{{ route('courses.materials.download', [$course, $material]) }}')"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-amber-700 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-amber-800 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                                </svg>
                                                <span>Lihat Slide</span>
                                            </button>
                                        @endif

                                        <a href="{{ route('courses.materials.download', [$course, $material]) }}"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>Unduh ({{ $material->formattedFileSize() }})</span>
                                        </a>
                                    @elseif($material->external_url)
                                        <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-teal-700 transition">
                                            <span>Buka Tautan &rarr;</span>
                                        </a>
                                    @endif

                                    @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                                        <button type="button"
                                            @click="openEditMaterialModal({
                                                id: {{ $material->id }},
                                                title: '{{ addslashes($material->title) }}',
                                                session_number: '{{ $material->session_number ?? '' }}',
                                                material_type: '{{ $material->material_type }}',
                                                external_url: '{{ addslashes($material->external_url ?? '') }}',
                                                content_text: {{ json_encode($material->content_text ?? '') }},
                                                description: '{{ addslashes($material->description ?? '') }}',
                                                file_name: '{{ addslashes($material->file_name ?? '') }}',
                                                file_size: '{{ $material->formattedFileSize() }}',
                                                update_url: '{{ route('courses.materials.update', [$course, $material]) }}'
                                            })"
                                            class="rounded-xl p-2 text-slate-500 hover:bg-teal-50 hover:text-teal-800 transition" title="Edit bahan ajar">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <form method="post" action="{{ route('courses.materials.destroy', [$course, $material]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl p-2 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Hapus bahan ajar">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- PLAYER AUDIO MULTIMEDIA --}}
                            @if($material->isAudio() && $material->file_path)
                                <div class="mt-4 rounded-2xl border border-purple-200 bg-purple-50/50 p-4">
                                    <p class="text-xs font-bold text-purple-900 mb-2">Putar Audio Perkuliahan:</p>
                                    <audio controls class="w-full h-10">
                                        <source src="{{ route('courses.materials.stream', [$course, $material]) }}" type="{{ $material->mime_type ?: 'audio/mpeg' }}">
                                        Browser Anda tidak mendukung pemutar audio langsung.
                                    </audio>
                                </div>
                            @endif

                            {{-- PLAYER VIDEO MULTIMEDIA --}}
                            @if($material->isVideo() && $material->file_path)
                                <div class="mt-4 rounded-2xl border border-rose-200 bg-black/5 p-3">
                                    <video controls class="w-full max-h-96 rounded-xl">
                                        <source src="{{ route('courses.materials.stream', [$course, $material]) }}" type="{{ $material->mime_type ?: 'video/mp4' }}">
                                        Browser Anda tidak mendukung pemutar video langsung.
                                    </video>
                                </div>
                            @elseif($material->isLink() && $material->getYouTubeEmbedUrl())
                                <div class="mt-4 aspect-video w-full max-w-2xl overflow-hidden rounded-2xl shadow-sm border border-slate-200">
                                    <iframe class="h-full w-full" src="{{ $material->getYouTubeEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            @endif

                            {{-- KONTEN TEKS ARTIKEL --}}
                            @if($material->isText() && $material->content_text)
                                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50/40 p-5 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap font-serif">
                                    {{ $material->content_text }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 2: JURNAL PERKULIAHAN (16 PERTEMUAN) & PRESENSI --}}
        {{-- ======================================================== --}}
        <div x-show="tab === 'sessions'" x-cloak class="space-y-6">
            {{-- 1. SISI MAHASISWA: STATUS PRESENSI HARI INI --}}
            @if(!auth()->user()->isStaff() && $activeSession)
                @php
                    $myAttendance = $activeSession->studentAttendance(auth()->id());
                @endphp
                @if($myAttendance)
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50/80 p-5 shadow-xs flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="h-11 w-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow-2xs">
                                ✓
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Presensi Berhasil Terverifikasi</span>
                                <h3 class="text-sm font-extrabold text-slate-900">Pertemuan Ke-{{ $activeSession->session_number }}: {{ $activeSession->topic }}</h3>
                                <p class="text-xs text-slate-600">
                                    Kehadiran Anda telah dicatat pada pukul {{ $myAttendance->checked_in_at?->format('H:i') ?? '-' }} WIB.
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex rounded-full bg-emerald-200/80 px-4 py-1 text-xs font-black text-emerald-950 border border-emerald-300">
                            Status: Hadir di Kelas
                        </span>
                    </div>
                @else
                    <div id="quick-attendance" class="rounded-3xl border-2 border-teal-500 bg-gradient-to-r from-teal-50 via-emerald-50 to-teal-50 p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-800 px-3 py-0.5 text-[11px] font-bold text-white uppercase tracking-wider animate-pulse">
                                    ● Presensi Kuliah Sedang Dibuka
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900">Pertemuan Ke-{{ $activeSession->session_number }}: {{ $activeSession->topic }}</h3>
                                <p class="text-xs text-slate-600">
                                    Silakan masukkan 4-digit PIN presensi yang ditampilkan oleh dosen pengampu di layar proyektor kelas:
                                </p>
                            </div>
                            <form method="post" action="{{ route('courses.sessions.check-in', [$course, $activeSession]) }}" class="flex items-center gap-3">
                                @csrf
                                <input type="text" name="passcode" required maxlength="4" pattern="[0-9]{4}" placeholder="0000"
                                    class="w-32 text-center font-mono font-black tracking-widest text-2xl rounded-2xl border-2 border-teal-400 focus:border-teal-700 focus:ring-teal-700 shadow-xs bg-white py-2.5">
                                <button type="submit" class="rounded-2xl bg-teal-800 px-5 py-3 text-xs sm:text-sm font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                    Kirim Presensi
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            {{-- 2. SISI DOSEN: LAYAR PROYEKTOR KELAS KODE PIN ATAU KONTROL BUKA CEPAT --}}
            @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                @if($activeSession)
                    {{-- A. LAYAR PROYEKTOR PIN KELAS (AKTIF) --}}
                    <div class="rounded-3xl border-2 border-teal-600 bg-gradient-to-br from-slate-950 via-teal-950 to-slate-950 text-white p-6 sm:p-8 shadow-xl" x-data="{ showAttendees: false }">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/20 text-emerald-300 border border-emerald-400/40 px-3.5 py-1 text-xs font-extrabold uppercase tracking-wider">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                                        Presensi Kelas Sedang Berlangsung
                                    </span>
                                    <span class="text-xs font-bold text-teal-200">
                                        Pertemuan Ke-{{ $activeSession->session_number }}
                                    </span>
                                </div>
                                <h2 class="text-xl sm:text-2xl font-black text-white mt-2">{{ $activeSession->topic }}</h2>
                                <p class="text-xs text-slate-300 mt-1">
                                    Tampilkan kode 4-digit ini di layar kelas / proyektor agar mahasiswa dapat melakukan presensi mandiri.
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <form method="post" action="{{ route('courses.sessions.regenerate-code', [$course, $activeSession]) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-slate-700 bg-slate-800/80 px-3.5 py-2 text-xs font-bold text-slate-200 hover:bg-slate-700 hover:text-white transition shadow-sm" title="Acak ulang PIN jika bocor">
                                        🔄 Acak Ulang PIN
                                    </button>
                                </form>

                                <form method="post" action="{{ route('courses.sessions.close-attendance', [$course, $activeSession]) }}" onsubmit="return confirm('Tutup sesi presensi pertemuan ini?')">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-rose-600 hover:bg-rose-500 px-4 py-2 text-xs font-bold text-white transition shadow-sm">
                                        ✕ Tutup Presensi Sesi
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Display Kode Monospace Raksasa & Statistik Realtime --}}
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-6 border-t border-slate-800 pt-6">
                            <div class="flex items-center gap-6">
                                <div class="rounded-3xl bg-white/10 p-4 backdrop-blur border border-white/20 text-center shadow-inner">
                                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-teal-300 block mb-1">KODE PRESENSI PIN</span>
                                    <div class="text-4xl sm:text-5xl font-mono font-black tracking-widest text-emerald-300 select-all px-4">
                                        {{ $activeSession->passcode }}
                                    </div>
                                </div>

                                <div class="text-xs text-slate-300 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Aktif s.d: <strong class="text-white">{{ $activeSession->passcode_expires_at?->format('H:i') ?? 'Selesai' }} WIB</strong></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span>Sudah Hadir: <strong class="text-emerald-300 font-extrabold text-sm">{{ $activeSession->attendances->count() }}</strong> dari {{ $students->count() }} Mahasiswa ({{ round(($activeSession->attendances->count() / ($students->count() ?: 1)) * 100) }}%)</span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="showAttendees = !showAttendees"
                                class="rounded-xl border border-teal-400/40 bg-teal-800/60 px-4 py-2.5 text-xs font-bold text-teal-200 hover:bg-teal-700 hover:text-white transition">
                                <span x-text="showAttendees ? 'Sembunyikan Daftar Mahasiswa' : 'Lihat Mahasiswa Hadir (' + {{ $activeSession->attendances->count() }} + ')'"></span>
                            </button>
                        </div>

                        {{-- Attendee Drawer --}}
                        <div x-show="showAttendees" x-cloak class="mt-5 rounded-2xl bg-slate-900/90 p-4 border border-slate-800 max-h-60 overflow-y-auto">
                            @if($activeSession->attendances->isEmpty())
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada mahasiswa yang memasukkan kode presensi.</p>
                            @else
                                <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 text-xs">
                                    @foreach($activeSession->attendances as $att)
                                        <div class="flex items-center justify-between rounded-xl bg-slate-800/80 px-3 py-2 border border-slate-700/60">
                                            <div class="truncate mr-2">
                                                <div class="font-bold text-slate-100 truncate">{{ $att->student->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">{{ $att->student->nim ?: 'NIM -' }}</div>
                                            </div>
                                            <span class="rounded-lg bg-emerald-500/20 text-emerald-300 text-[10px] px-2 py-0.5 font-mono whitespace-nowrap">
                                                {{ $att->checked_in_at?->format('H:i') }} WIB
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- B. KONTROL BUKA CEPAT & FORM MANUAL (TIDAK ADA SESI AKTIF) --}}
                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Widget Buka Presensi Cepat (PIN 4-Digit) --}}
                        <div class="rounded-3xl border border-teal-200 bg-gradient-to-br from-teal-50/70 via-white to-teal-50/40 p-6 shadow-xs">
                            <div class="flex items-center gap-2.5 text-teal-900 font-extrabold text-sm">
                                <div class="h-8 w-8 rounded-xl bg-teal-800 text-white flex items-center justify-center font-bold">
                                    ⚡
                                </div>
                                <span>Buka Presensi Cepat (Kode PIN 4-Digit)</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-2 mb-4">
                                Buka sesi kuliah dan buat PIN 4-digit secara instan untuk presensi mandiri mahasiswa di kelas.
                            </p>

                            <form method="post" action="{{ route('courses.sessions.quick-open', $course) }}" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700">Pertemuan</label>
                                        <select name="session_number" class="mt-1 w-full rounded-xl border-slate-300 text-xs font-bold focus:border-teal-700 focus:ring-teal-700">
                                            @for($i = 1; $i <= 16; $i++)
                                                <option value="{{ $i }}" @selected($sessions->count() + 1 === $i)>Sesi #{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-700">Materi / Pokok Bahasan</label>
                                        <input type="text" name="topic" placeholder="Contoh: Academic Writing Mechanics..." class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3 pt-1">
                                    <div class="w-1/2">
                                        <select name="duration_minutes" class="w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-teal-700 focus:ring-teal-700">
                                            <option value="30">Aktif 30 Menit</option>
                                            <option value="60" selected>Aktif 60 Menit</option>
                                            <option value="90">Aktif 90 Menit</option>
                                            <option value="120">Aktif 120 Menit</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="w-1/2 rounded-xl bg-teal-800 py-2.5 px-3 text-xs font-bold text-white hover:bg-teal-700 transition shadow-sm">
                                        Buka & Generate PIN &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Widget Catat Manual Jurnal Lengkap --}}
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs" x-data="{ journalOpen: false }">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5 text-slate-800 font-extrabold text-sm">
                                    <div class="h-8 w-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                                        📝
                                    </div>
                                    <span>Pencatatan Jurnal Manual</span>
                                </div>
                                <button type="button" @click="journalOpen = !journalOpen" class="text-xs font-bold text-teal-800 hover:underline">
                                    <span x-text="journalOpen ? 'Tutup' : '+ Buka Form Manual'"></span>
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">
                                Masukkan riwayat jurnal pertemuan terdahulu lengkap dengan metode ajar dan catatan refleksi dosen.
                            </p>

                            <form x-show="journalOpen" x-cloak method="post" action="{{ route('courses.sessions.store', $course) }}" class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                                @csrf
                                <div class="grid gap-2 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700">Pertemuan *</label>
                                        <select name="session_number" required class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                            @for($i = 1; $i <= 16; $i++)
                                                <option value="{{ $i }}" @selected($sessions->count() + 1 === $i)>Sesi #{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700">Tanggal *</label>
                                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700">Mahasiswa Hadir *</label>
                                        <input type="number" name="attendance_count" required value="{{ $students->count() ?: 1 }}" min="0" class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                        <input type="hidden" name="total_students" value="{{ $students->count() ?: 1 }}">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700">Pokok Bahasan / Materi *</label>
                                    <input type="text" name="topic" required placeholder="Pokok bahasan perkuliahan..." class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700">Metode Pembelajaran</label>
                                    <input type="text" name="teaching_method" placeholder="Ceramah, Diskusi Kelompok, PBL..." class="mt-1 w-full rounded-xl border-slate-300 text-xs">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700">Catatan Refleksi Dosen</label>
                                    <textarea name="notes" rows="2" placeholder="Catatan refleksi kelas..." class="mt-1 w-full rounded-xl border-slate-300 text-xs"></textarea>
                                </div>

                                <div class="flex justify-end pt-1">
                                    <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2 text-xs font-bold text-white hover:bg-teal-700 transition">
                                        Simpan Jurnal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            {{-- 3. REKAPITULASI TABEL 16 PERTEMUAN (DENGAN SLIDE-OVER ATTENDEE MODAL) --}}
            <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs" x-data="{ activeAttendeeModal: null }">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">
                            Jurnal & Linimasa 16 Pertemuan Perkuliahan
                        </span>
                        <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900">
                            {{ $sessions->count() }} Tercatat
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if(auth()->user()->isStaff())
                            <form method="post" action="{{ route('courses.sessions.sync-attendance', $course) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-teal-900 transition" title="Sinkronkan persentase presensi seluruh mahasiswa">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Sinkronkan Presensi</span>
                                </button>
                            </form>
                            <span class="text-slate-300">·</span>
                        @endif
                        <a href="{{ route('courses.sessions.print', $course) }}" target="_blank"
                            class="inline-flex items-center gap-1 text-xs font-bold text-teal-800 hover:text-teal-950 transition">
                            <span>Cetak Laporan Format A4 &rarr;</span>
                        </a>
                    </div>
                </div>

                @if($sessions->isEmpty())
                    <div class="p-12 text-center text-sm text-slate-500">
                        Belum ada riwayat pertemuan perkuliahan yang dicatat untuk kelas ini.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="border-b border-slate-200 bg-slate-50/70 font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="py-3.5 px-4 w-16 text-center">Sesi</th>
                                    <th class="py-3.5 px-4 w-28">Tanggal</th>
                                    <th class="py-3.5 px-4">Pokok Bahasan / Materi</th>
                                    <th class="py-3.5 px-4 w-36">Metode</th>
                                    <th class="py-3.5 px-4 w-36 text-center">Kehadiran</th>
                                    <th class="py-3.5 px-4 w-28 text-center">Status Sesi</th>
                                    <th class="py-3.5 px-4">Catatan Dosen</th>
                                    @if(auth()->user()->isStaff())
                                        <th class="py-3.5 px-4 w-32 text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($sessions as $session)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-teal-50 font-mono font-bold text-teal-900 border border-teal-200/70 shadow-2xs">
                                                #{{ $session->session_number }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-600">
                                            {{ $session->date?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-slate-900 block text-xs sm:text-sm">{{ $session->topic }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            {{ $session->teaching_method ?: 'Tatap Muka' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if(auth()->user()->isStaff())
                                                <button type="button" @click="activeAttendeeModal = {{ $session->id }}"
                                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition shadow-2xs"
                                                    title="Klik untuk melihat daftar presensi mahasiswa">
                                                    {{ $session->attendance_count }}/{{ $session->total_students }} ({{ $session->attendancePercentage() }}%)
                                                </button>
                                            @else
                                                @if($session->hasStudentAttended(auth()->id()))
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-bold text-emerald-900 border border-emerald-200">
                                                        ✓ Hadir
                                                    </span>
                                                @elseif($session->isOpenForAttendance())
                                                    <a href="#quick-attendance" class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-0.5 text-xs font-bold text-amber-900 border border-amber-300 animate-pulse">
                                                        Isi Presensi &rarr;
                                                    </a>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-400">
                                                        Tidak Hadir
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if($session->isOpenForAttendance())
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-300 px-2.5 py-0.5 text-[11px] font-mono font-bold text-amber-900 animate-pulse">
                                                    ● {{ $session->passcode }}
                                                </span>
                                            @else
                                                <span class="rounded-lg bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-600">
                                                    Selesai
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 italic max-w-xs truncate">
                                            {{ $session->notes ?: '-' }}
                                        </td>
                                        @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                                            <td class="py-3.5 px-4 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    @if($session->isOpenForAttendance())
                                                        <form method="post" action="{{ route('courses.sessions.close-attendance', [$course, $session]) }}">
                                                            @csrf
                                                            <button class="rounded-lg px-2.5 py-1 text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition shadow-2xs" title="Tutup sesi presensi">
                                                                Tutup
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="post" action="{{ route('courses.sessions.open-attendance', [$course, $session]) }}">
                                                            @csrf
                                                            <input type="hidden" name="duration_minutes" value="60">
                                                            <button class="rounded-lg px-2.5 py-1 text-[11px] font-bold bg-teal-50 text-teal-800 border border-teal-200 hover:bg-teal-100 transition shadow-2xs" title="Buka presensi dengan kode PIN baru">
                                                                Buka Presensi
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form method="post" action="{{ route('courses.sessions.destroy', [$course, $session]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurnal pertemuan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="rounded-lg p-1 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Hapus sesi">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>

                                    {{-- Slide-over / Modal Detail Presensi Sesi --}}
                                    @if(auth()->user()->isStaff())
                                        <div x-show="activeAttendeeModal === {{ $session->id }}" x-cloak
                                            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                                            <div @click.away="activeAttendeeModal = null" class="w-full max-w-xl rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
                                                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                                                    <div>
                                                        <h3 class="font-extrabold text-slate-900 text-sm">Daftar Kehadiran Pertemuan Ke-{{ $session->session_number }}</h3>
                                                        <p class="text-xs text-slate-500">{{ $session->topic }} · {{ $session->date?->format('d/m/Y') }}</p>
                                                    </div>
                                                    <button type="button" @click="activeAttendeeModal = null" class="text-slate-400 hover:text-slate-700 font-bold text-xl">&times;</button>
                                                </div>

                                                <div class="p-6 max-h-96 overflow-y-auto divide-y divide-slate-100 text-xs">
                                                    @forelse($students as $cs)
                                                        @php
                                                            $studentAtt = $session->studentAttendance($cs->student_id);
                                                        @endphp
                                                        <div class="py-3 flex items-center justify-between gap-3">
                                                            <div>
                                                                <div class="font-bold text-slate-800">{{ $cs->student->name }}</div>
                                                                <div class="text-[11px] text-slate-500 font-mono">{{ $cs->nim ?: $cs->student->nim ?: 'NIM -' }} · {{ $cs->student->studyProgram->name ?? 'Prodi' }}</div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                @if($studentAtt)
                                                                    <span class="rounded-full bg-emerald-100 text-emerald-800 px-3 py-0.5 text-[10px] font-bold">
                                                                        Hadir ({{ $studentAtt->checked_in_at?->format('H:i') ?? '-' }})
                                                                    </span>
                                                                    <form method="post" action="{{ route('courses.sessions.attendances.toggle', [$course, $session]) }}">
                                                                        @csrf
                                                                        <input type="hidden" name="student_id" value="{{ $cs->student_id }}">
                                                                        <input type="hidden" name="status" value="absent">
                                                                        <button type="submit" class="text-[11px] text-rose-600 font-bold hover:underline">Ubah ke Alpa</button>
                                                                    </form>
                                                                @else
                                                                    <span class="rounded-full bg-slate-100 text-slate-400 px-3 py-0.5 text-[10px]">
                                                                        Tidak Hadir
                                                                    </span>
                                                                    <form method="post" action="{{ route('courses.sessions.attendances.toggle', [$course, $session]) }}">
                                                                        @csrf
                                                                        <input type="hidden" name="student_id" value="{{ $cs->student_id }}">
                                                                        <input type="hidden" name="status" value="present">
                                                                        <button type="submit" class="text-[11px] text-teal-800 font-bold hover:underline">Tandai Hadir</button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="text-center text-slate-400 py-4">Belum ada mahasiswa terdaftar di kelas ini.</p>
                                                    @endforelse
                                                </div>

                                                <div class="border-t border-slate-100 bg-slate-50/80 px-6 py-3.5 flex justify-end">
                                                    <button type="button" @click="activeAttendeeModal = null" class="rounded-xl bg-slate-200 px-5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-300 transition">
                                                        Tutup
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 3: TUGAS MAHASISWA & PENILAIAN --}}
        {{-- ======================================================== --}}
        <div x-show="tab === 'assignments'" x-cloak class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">
                        {{ auth()->user()->isStaff() ? 'Penugasan Perkuliahan & Evaluasi Asesmen' : 'Tugas Perkuliahan Saya' }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        {{ auth()->user()->isStaff()
                            ? 'Manajemen tugas penulisan akademik WriteWise AI, draf mahasiswa, rubrik penilaian, dan ekspor nilai.'
                            : 'Kerjakan tugas akademik, submit draf via WriteWise AI, dan tinjau evaluasi nilai dari dosen pengampu.' }}
                    </p>
                </div>

                @if(auth()->user()->isStaff())
                    <div class="flex items-center gap-2">
                        <a href="{{ route('courses.grades.export', $course) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-2xs hover:bg-emerald-100 transition">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Ekspor Excel / CSV</span>
                        </a>
                        <a href="{{ route('courses.grades.print', $course) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Nilai A4</span>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Form Terbitkan Tugas Baru (Dosen Pengampu) --}}
            @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                <div class="rounded-3xl border border-teal-200 bg-white p-6 shadow-xs">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-teal-50 text-teal-800 border border-teal-200 flex items-center justify-center font-bold text-lg shrink-0">
                                ✍️
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Terbitkan Penugasan Baru (Multimoda & WriteWise)</h3>
                                <p class="text-xs text-slate-500">Mendukung Esai Akademik, Portofolio Karya, Kuis/Ujian Terstruktur, Presentasi, dan Praktikum.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="openAiModal()"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-teal-700 via-indigo-700 to-purple-700 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:opacity-90 transition">
                                <span class="animate-pulse">✨</span>
                                <span>Rancang dengan AI</span>
                            </button>
                            <button type="button" @click="assignOpen = !assignOpen"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-2xs transition">
                                <span x-text="assignOpen ? 'Tutup Formulir' : '+ Buka Form Tugas Baru'"></span>
                                <svg class="h-4 w-4 transition-transform duration-200" :class="assignOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <form x-show="assignOpen" x-cloak method="post" action="{{ route('courses.assignments.store', $course) }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-slate-100 pt-5">
                        @csrf
                        {{-- Pemilihan Format Model Penugasan --}}
                        <div class="rounded-2xl bg-slate-50/70 p-4 border border-slate-200">
                            <label class="block text-xs font-bold text-slate-800 mb-2">Pilih Format & Model Penugasan *</label>
                            <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-5">
                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'writing' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="writing" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Esai Akademik</p>
                                        <p class="text-[10px] text-slate-500">WriteWise AI Editor</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'stimulus_exam' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="stimulus_exam" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Ujian Stimulus</p>
                                        <p class="text-[10px] text-slate-500">Listening / Passage</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'hybrid' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="hybrid" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Asesmen Hibrida</p>
                                        <p class="text-[10px] text-slate-500">Kombinasi PG & Esai</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'translation' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="translation" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Penerjemahan Teks</p>
                                        <p class="text-[10px] text-slate-500">Source, Target & Review</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'milestone_artifact' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="milestone_artifact" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Proposal & Proyek</p>
                                        <p class="text-[10px] text-slate-500">Milestone Bertahap</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700">Judul Penugasan *</label>
                                <input type="text" name="title" id="assignment_title_input" required placeholder="Contoh: Critical Literature Review & Synthesis Essay"
                                    class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Tenggat Waktu (Deadline) *</label>
                                <input type="datetime-local" name="due_at" required class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700">Tautkan dengan Pertemuan Perkuliahan (Jurnal Sesi)</label>
                            <select name="course_session_id" id="assignment_course_session_id" class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                                <option value="">Tugas Umum Semester (Tanpa Sesi Tertentu)</option>
                                @foreach($sessions as $sess)
                                    <option value="{{ $sess->id }}">Pertemuan Ke-{{ $sess->session_number }}: {{ $sess->topic }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">Mengaitkan tugas dengan sesi perkuliahan memudahkan evaluasi capaian mingguan.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700">Instruksi & Petunjuk Pengerjaan Tugas *</label>
                            <textarea name="instructions" id="assignment_instructions_input" rows="5" required placeholder="Jelaskan kriteria, struktur, format, dan ketentuan pengerjaan tugas secara rinci..."
                                class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700"></textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                Simpan & Terbitkan Tugas
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Daftar Tugas Kelas --}}
            @if($assignments->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-2xs">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700 mb-3">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Tugas Perkuliahan</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                        Dosen pengampu belum menerbitkan tugas atau asesmen untuk mata kuliah ini.
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($assignments as $assignment)
                        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs hover:border-teal-300 transition" x-data="{ showSubmissions: false }">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="space-y-1.5 max-w-3xl">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-lg bg-teal-50 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-teal-800 border border-teal-200">
                                            {{ strtoupper($assignment->assignment_type ?? 'writing') }}
                                        </span>

                                        @if($assignment->due_date)
                                            @php
                                                $isPassed = $assignment->due_date->isPast();
                                                $isNear = !$isPassed && $assignment->due_date->diffInHours(now()) <= 48;
                                            @endphp
                                            <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-0.5 text-[11px] font-bold border
                                                {{ $isPassed ? 'bg-slate-100 text-slate-600 border-slate-200' :
                                                   ($isNear ? 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse' : 'bg-amber-50 text-amber-900 border-amber-200') }}">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Tenggat: {{ $assignment->due_date->format('d/m/Y H:i') }} WIB ({{ $assignment->due_date->diffForHumans() }})
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-base font-extrabold text-slate-900">
                                        {{ $assignment->title }}
                                    </h3>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                                        <a href="{{ route('courses.assignments.edit', [$course, $assignment]) }}"
                                            class="inline-flex items-center gap-1 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span>Sunting</span>
                                        </a>

                                        <form method="post" action="{{ route('courses.assignments.destroy', [$course, $assignment]) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini? Seluruh data submisi mahasiswa dan nilai akan ikut terhapus.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 transition shadow-2xs">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-wrap font-sans">{{ $assignment->instructions }}</div>

                            {{-- SISI DOSEN: REKAP SUBMISI MAHASISWA & PENILAIAN CEPAT --}}
                            @if(auth()->user()->isStaff())
                                <div class="mt-5 border-t border-slate-100 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">
                                            Submisi Mahasiswa ({{ $assignment->submissions->count() }}/{{ $students->count() }})
                                        </h4>
                                        <button type="button" @click="showSubmissions = !showSubmissions" class="text-xs font-bold text-teal-800 hover:underline">
                                            <span x-text="showSubmissions ? 'Tutup Daftar Submisi' : 'Tampilkan Daftar Submisi (' + {{ $assignment->submissions->count() }} + ')'"></span>
                                        </button>
                                    </div>

                                    <div x-show="showSubmissions" x-cloak class="mt-3">
                                        @if($assignment->submissions->isEmpty())
                                            <p class="text-xs text-slate-400 italic py-2">Belum ada mahasiswa yang mengumpulkan tugas ini.</p>
                                        @else
                                            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                                <table class="w-full text-left text-xs text-slate-700">
                                                    <thead class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-slate-600">
                                                        <tr>
                                                            <th class="py-2.5 px-3">Mahasiswa</th>
                                                            <th class="py-2.5 px-3 w-28 text-center">Status</th>
                                                            <th class="py-2.5 px-3 w-24 text-center">Nilai (0-100)</th>
                                                            <th class="py-2.5 px-3 w-20 text-center">Mutu</th>
                                                            @if(auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true))
                                                                <th class="py-2.5 px-3">Evaluasi Nilai & Catatan</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100">
                                                        @foreach($assignment->submissions as $sub)
                                                            <tr class="hover:bg-slate-50/70 transition">
                                                                <td class="py-2.5 px-3">
                                                                    <div class="font-bold text-slate-900">{{ $sub->student->name }}</div>
                                                                    <div class="text-[11px] text-slate-500 font-mono">{{ $sub->student->nim ?: 'NIM -' }} · {{ $sub->student->email }}</div>
                                                                </td>
                                                                <td class="py-2.5 px-3 text-center">
                                                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold
                                                                        {{ $sub->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                                        {{ $sub->status === 'graded' ? 'Dinilai' : 'Draf/Terkirim' }}
                                                                    </span>
                                                                </td>
                                                                <td class="py-2.5 px-3 text-center font-bold text-teal-800 text-sm">
                                                                    {{ $sub->score !== null ? number_format($sub->score, 1) : '-' }}
                                                                </td>
                                                                <td class="py-2.5 px-3 text-center font-bold text-slate-700">
                                                                    {{ $sub->letterGrade() }}
                                                                </td>
                                                                @if(auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true))
                                                                    <td class="py-2.5 px-3">
                                                                        <form method="post" action="{{ route('courses.assignments.grade', [$course, $assignment, $sub]) }}" class="flex items-center gap-2">
                                                                            @csrf
                                                                            <input type="number" step="0.1" min="0" max="100" name="score" required value="{{ $sub->score }}" placeholder="Nilai" class="w-20 rounded-xl border-slate-300 py-1.5 text-xs text-center font-bold">
                                                                            <input type="text" name="feedback_notes" placeholder="Catatan evaluasi singkat..." class="w-48 sm:w-64 rounded-xl border-slate-300 py-1.5 text-xs">
                                                                            <button type="submit" class="rounded-xl bg-teal-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                                                                                Simpan
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                {{-- SISI MAHASISWA: STATUS PENGERJAAN & AKSES WRITEWISE --}}
                                @php
                                    $mySub = $assignment->submissions->firstWhere('student_id', auth()->id());
                                @endphp
                                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status Submisi Saya</span>
                                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                                @if($mySub)
                                                    <span class="inline-flex rounded-full px-3 py-0.5 text-xs font-bold
                                                        {{ $mySub->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                        {{ $mySub->status === 'graded' ? 'Sudah Dinilai Dosen' : 'Draf Terkumpul' }}
                                                    </span>
                                                    @if($mySub->score !== null)
                                                        <span class="font-extrabold text-teal-800 text-sm">
                                                            Nilai: {{ number_format($mySub->score, 1) }} (Mutu: {{ $mySub->letterGrade() }})
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-slate-500 italic">Sedang dalam proses evaluasi</span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex rounded-full bg-slate-200 px-3 py-0.5 text-xs font-bold text-slate-700">
                                                        Belum Mengumpulkan Draf
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <a href="{{ route('assignments.show', $assignment) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-2xs transition">
                                            @if($mySub)
                                                <span>Buka Draf & Evaluasi WriteWise &rarr;</span>
                                            @else
                                                <span>Kerjakan Tugas di WriteWise &rarr;</span>
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 4: PESERTA KELAS (MAHASISWA) --}}
        {{-- ======================================================== --}}
        <div x-show="tab === 'students'" x-cloak class="space-y-6">
            @php
                $canManageStudents = auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true));
                $myEnrollment = $students->firstWhere('student_id', auth()->id());
            @endphp

            {{-- Rekap Capaian Akademik Mahasiswa Pribadi --}}
            @if(!auth()->user()->isStaff() && $myEnrollment)
                <div class="rounded-3xl border border-teal-200 bg-gradient-to-r from-teal-50/80 via-white to-emerald-50/70 p-6 shadow-xs">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-teal-800">Rekap Capaian Akademik Saya</span>
                            <h3 class="text-base font-extrabold text-slate-900 mt-0.5">{{ auth()->user()->name }} ({{ auth()->user()->nim ?: $myEnrollment->nim ?: '-' }})</h3>
                            <p class="text-xs text-slate-500">Nilai akhir dan kehadiran dikalkulasi secara otomatis dari jurnal perkuliahan dan tugas kelas.</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Presensi</span>
                                <span class="text-xl font-black text-slate-900">{{ number_format($myEnrollment->attendance_rate, 0) }}%</span>
                            </div>
                            <div class="text-center border-l border-teal-200 pl-6">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Nilai Akhir</span>
                                <span class="text-xl font-black text-teal-800">{{ $myEnrollment->final_grade ? number_format($myEnrollment->final_grade, 1) : '-' }}</span>
                            </div>
                            <div class="text-center border-l border-teal-200 pl-6">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Huruf Mutu</span>
                                <span class="inline-flex rounded-full bg-teal-100 px-3.5 py-1 text-sm font-black text-teal-950">
                                    {{ $myEnrollment->letter_grade ?: 'Belum Ada' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Form Penambahan / Assign Mahasiswa ke Kelas (Khusus Dosen / Staf) --}}
            @if($canManageStudents)
                <div class="rounded-3xl border border-teal-200 bg-white p-6 shadow-xs" x-data="{ enrollMode: '{{ ($availableStudents ?? collect())->isNotEmpty() ? 'directory' : 'paste' }}', enrollOpen: false }">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-teal-50 text-teal-800 border border-teal-200 flex items-center justify-center font-bold text-lg shrink-0">
                                👥
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Pendaftaran & Assign Mahasiswa ke Kelas</h3>
                                <p class="text-xs text-slate-500">Pilih mahasiswa dari direktori kampus atau tempel daftar NIM dan Nama langsung.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="enrollOpen = !enrollOpen"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-2xs transition">
                                <span x-text="enrollOpen ? 'Tutup Formulir' : '+ Tambah / Assign Mahasiswa'"></span>
                                <svg class="h-4 w-4 transition-transform duration-200" :class="enrollOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div x-show="enrollOpen" x-cloak class="mt-5 border-t border-slate-100 pt-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="enrollMode = 'directory'"
                                :class="enrollMode === 'directory' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200'"
                                class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition">
                                Direktori Mahasiswa ({{ ($availableStudents ?? collect())->count() }})
                            </button>
                            <button type="button" @click="enrollMode = 'paste'"
                                :class="enrollMode === 'paste' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200'"
                                class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition">
                                + Input / Paste Cepat Absen
                            </button>
                            <a href="{{ route('students.index', ['course_id' => $course->id]) }}"
                                class="rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Direktori Lengkap &rarr;
                            </a>
                        </div>

                        <form method="post" action="{{ route('courses.students.enroll', $course) }}" class="space-y-4">
                            @csrf
                            {{-- Mode 1: Direktori Checklist --}}
                            <div x-show="enrollMode === 'directory'" class="space-y-3">
                                @if(($availableStudents ?? collect())->isEmpty())
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center text-xs text-slate-500">
                                        Seluruh mahasiswa di direktori sudah terdaftar di kelas ini. Gunakan tab <strong>+ Input / Paste Cepat Absen</strong> di samping untuk mahasiswa baru.
                                    </div>
                                @else
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-700">Pilih mahasiswa yang memprogram mata kuliah ini:</span>
                                        <button type="button"
                                            onclick="document.querySelectorAll('.dir-student-checkbox').forEach(cb => cb.checked = true)"
                                            class="font-bold text-teal-800 hover:underline">
                                            Pilih Semua
                                        </button>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto grid gap-2 sm:grid-cols-2 lg:grid-cols-3 rounded-2xl border border-slate-200 bg-slate-50/50 p-3">
                                        @foreach($availableStudents as $avail)
                                            <label class="flex items-start gap-2.5 rounded-xl border border-slate-200 bg-white p-2.5 text-xs cursor-pointer hover:bg-teal-50/50 hover:border-teal-300 transition shadow-2xs">
                                                <input type="checkbox" name="student_ids[]" value="{{ $avail->id }}"
                                                    class="dir-student-checkbox mt-0.5 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                                <span class="min-w-0">
                                                    <span class="block font-bold text-slate-900 truncate">{{ $avail->name }}</span>
                                                    <span class="block font-mono text-[11px] text-slate-500">
                                                        NIM: {{ $avail->nim ?: '-' }}
                                                        @if($avail->studyProgram)
                                                            &middot; {{ $avail->studyProgram->code }}
                                                        @endif
                                                        @if($avail->cohort_year)
                                                            ({{ $avail->cohort_year }})
                                                        @endif
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Mode 2: Paste Cepat --}}
                            <div x-show="enrollMode === 'paste'" x-cloak class="space-y-3">
                                <div class="grid gap-3 sm:grid-cols-4">
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold text-slate-800">
                                            Daftar Mahasiswa Tambahan (<code class="rounded bg-slate-100 px-1.5 py-0.5 text-teal-900">NIM ; Nama Lengkap ; Email(opsional)</code>)
                                        </label>
                                        <textarea name="raw_list" rows="4"
                                            placeholder="240105010 ; Andi Saputra&#10;240105011 ; Nadia Putri ; nadia@student.ac.id"
                                            class="mt-1.5 w-full rounded-2xl border-slate-300 bg-white font-mono text-xs focus:border-teal-700 focus:ring-teal-700"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-800">Tahun Angkatan</label>
                                        <input type="number" name="cohort_year" min="2000" max="2099" value="{{ now()->year }}"
                                            class="mt-1.5 w-full rounded-xl border-slate-300 bg-white text-xs font-bold">
                                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                            Mahasiswa otomatis terdaftar ke Prodi <strong>{{ $course->studyProgram->name }}</strong> dan di-assign ke tugas kelas ini.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white shadow-2xs hover:bg-teal-700 transition">
                                    Assign Mahasiswa ke Kelas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Realtime Search Bar & Tabel Peserta Kelas --}}
            <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-800">
                            {{ auth()->user()->isStaff() ? 'Daftar Mahasiswa Terdaftar' : 'Teman Sekelas' }} ({{ $students->count() }} Peserta)
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Realtime Search Input untuk Mahasiswa --}}
                        <div class="relative w-48 sm:w-64">
                            <input type="text" x-model="studentSearch" placeholder="Cari Nama / NIM..."
                                class="w-full rounded-xl border-slate-300 pl-8 pr-3 py-1.5 text-xs focus:border-teal-700 focus:ring-teal-700 bg-white">
                            <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        @if(auth()->user()->isStaff())
                            <span class="text-slate-300">·</span>
                            <a href="{{ route('courses.grades.export', $course) }}" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 hover:text-emerald-950 transition">
                                <span>Ekspor</span>
                            </a>
                            <span class="text-slate-300">·</span>
                            <a href="{{ route('courses.grades.print', $course) }}" target="_blank" class="text-xs font-bold text-teal-800 hover:text-teal-950 transition">
                                Cetak Nilai &rarr;
                            </a>
                        @endif
                    </div>
                </div>

                @if($students->isEmpty())
                    <div class="p-12 text-center text-sm text-slate-500">Belum ada mahasiswa yang terdaftar di kelas ini.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="border-b border-slate-200 bg-slate-50/70 font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="py-3.5 px-4 w-14 text-center">No</th>
                                    <th class="py-3.5 px-4 w-36">NIM</th>
                                    <th class="py-3.5 px-4">Nama & Program Studi</th>
                                    @if(auth()->user()->isStaff())
                                        <th class="py-3.5 px-4 w-44 text-center">Kehadiran (%)</th>
                                        <th class="py-3.5 px-4 w-24 text-center">Nilai Akhir</th>
                                        <th class="py-3.5 px-4 w-20 text-center">Mutu</th>
                                        @if($canManageStudents)
                                            <th class="py-3.5 px-4 w-24 text-right">Aksi</th>
                                        @endif
                                    @else
                                        <th class="py-3.5 px-4 w-28 text-center">Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($students as $idx => $cs)
                                    <tr x-show="studentSearch === '' || '{{ strtolower(addslashes($cs->student->name)) }}'.includes(studentSearch.toLowerCase()) || '{{ strtolower(addslashes($cs->nim ?: $cs->student->nim ?: '')) }}'.includes(studentSearch.toLowerCase())"
                                        class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $cs->nim ?: ($cs->student->nim ?: '-') }}</td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="h-7 w-7 rounded-lg bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($cs->student->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $cs->student->name }}</div>
                                                    <div class="text-[11px] text-slate-500">
                                                        {{ $cs->student->email }}
                                                        @if($cs->student->studyProgram)
                                                            &middot; <span class="font-bold text-teal-800">{{ $cs->student->studyProgram->code }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @if(auth()->user()->isStaff())
                                            <td class="py-3.5 px-4 text-center">
                                                @if($canManageStudents)
                                                    <form method="post" action="{{ route('courses.students.update', [$course, $cs]) }}" class="inline-flex items-center justify-center gap-1.5">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="nim" value="{{ $cs->nim ?: $cs->student->nim }}">
                                                        <input type="number" name="attendance_rate" min="0" max="100" step="1" value="{{ number_format($cs->attendance_rate, 0, '.', '') }}"
                                                            class="w-16 rounded-xl border-slate-300 py-1 px-2 text-center text-xs font-bold focus:border-teal-700 focus:ring-teal-700">
                                                        <button type="submit" class="rounded-xl bg-slate-800 px-2.5 py-1 text-xs font-bold text-white hover:bg-slate-700 transition" title="Simpan perubahan kehadiran">
                                                            OK
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="font-bold text-slate-800">{{ number_format($cs->attendance_rate, 0) }}%</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-4 text-center font-extrabold text-teal-800">{{ $cs->final_grade ? number_format($cs->final_grade, 1) : '-' }}</td>
                                            <td class="py-3.5 px-4 text-center">
                                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-0.5 text-xs font-bold text-slate-800">
                                                    {{ $cs->letter_grade ?: '-' }}
                                                </span>
                                            </td>
                                            @if($canManageStudents)
                                                <td class="py-3.5 px-4 text-right">
                                                    <form method="post" action="{{ route('courses.students.unenroll', [$course, $cs]) }}"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin mengeluarkan {{ $cs->student->name }} dari kelas ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-xl border border-red-200 px-2.5 py-1 text-xs font-bold text-red-600 hover:bg-red-50 transition">
                                                            Keluarkan
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                        @else
                                            <td class="py-3.5 px-4 text-center">
                                                @if($cs->student_id === auth()->id())
                                                    <span class="inline-flex rounded-full bg-teal-100 px-3 py-0.5 text-xs font-black text-teal-950">
                                                        Anda
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs text-slate-500">
                                                        Mahasiswa
                                                    </span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- MODAL PRATINJAU DOKUMEN GOOGLE DOCS (Global Viewer Modal) --}}
        <div x-show="docModalOpen"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs"
            @keydown.escape.window="docModalOpen = false">
            <div class="relative w-full max-w-5xl rounded-3xl border border-slate-200 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                @click.away="docModalOpen = false">
                {{-- Header Modal --}}
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/80 px-6 py-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="rounded-xl bg-blue-100 p-2 text-blue-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-extrabold text-slate-900 truncate" x-text="docModalTitle"></h3>
                            <p class="text-xs text-slate-500">Pratinjau Dokumen Online (Google Docs Viewer)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="docModalUrl" target="_blank"
                            class="hidden sm:inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                            <span>Buka Tab Baru &rarr;</span>
                        </a>
                        <a :href="docDownloadUrl"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition">
                            <span>Unduh Berkas</span>
                        </a>
                        <button type="button" @click="docModalOpen = false"
                            class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body Modal --}}
                <div class="flex-1 overflow-hidden p-3 bg-slate-100 flex flex-col">
                    @if(app()->environment('local') || str_contains(request()->getHost(), '.test') || str_contains(request()->getHost(), 'localhost'))
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 mb-2">
                            <strong>Catatan Pengujian Lokal:</strong> Google Docs Viewer membutuhkan domain yang dapat diakses publik dari internet (seperti pada VPS production). Jika pratinjau Google kosong di localhost, silakan klik tombol <strong>Unduh Berkas</strong> di kanan atas.
                        </div>
                    @endif
                    <iframe :src="docModalUrl" class="w-full flex-1 min-h-[60vh] max-h-[72vh] rounded-2xl border border-slate-200 bg-white shadow-inner" frameborder="0"></iframe>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT BAHAN AJAR / DOKUMEN --}}
        @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
            <div x-show="editMaterialModalOpen"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
                @keydown.escape.window="editMaterialModalOpen = false">
                <div class="relative w-full max-w-2xl rounded-3xl border border-teal-200/90 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="editMaterialModalOpen = false">
                    
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-teal-900 via-slate-900 to-teal-950 text-white px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-teal-800 text-teal-200 border border-teal-700 flex items-center justify-center font-bold text-lg shadow-sm">
                                ✏️
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-extrabold text-white">Edit Bahan Ajar Perkuliahan</h3>
                                <p class="text-xs text-teal-200">Koreksi judul, pertemuan sesi, berkas materi, tautan, atau catatan mahasiswa.</p>
                            </div>
                        </div>
                        <button type="button" @click="editMaterialModalOpen = false"
                            class="rounded-xl p-1.5 text-slate-300 hover:bg-white/10 hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Form Edit --}}
                    <form :action="editMaterialData.update_url" method="post" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4 text-xs sm:text-sm">
                        @csrf
                        @method('PUT')

                        {{-- Judul & Penempatan Sesi --}}
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700">Judul Bahan Ajar *</label>
                                <input type="text" name="title" x-model="editMaterialData.title" required
                                    class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs font-semibold">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Penempatan Pertemuan</label>
                                <select name="session_number" x-model="editMaterialData.session_number"
                                    class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                                    <option value="">Materi Umum / Silabus RPS</option>
                                    @for($i = 1; $i <= 16; $i++)
                                        <option value="{{ $i }}">Pertemuan Ke-{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        {{-- Format Tipe Bahan Ajar --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Format Bahan Ajar *</label>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-6">
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'document' ? 'border-teal-700 bg-teal-50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="document" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>📄 Dokumen</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'audio' ? 'border-purple-600 bg-purple-50 text-purple-900 ring-1 ring-purple-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="audio" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>🎧 Audio</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'video' ? 'border-rose-600 bg-rose-50 text-rose-900 ring-1 ring-rose-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="video" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>🎥 Video</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'link' ? 'border-blue-600 bg-blue-50 text-blue-900 ring-1 ring-blue-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="link" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>🔗 Tautan</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'text' ? 'border-amber-600 bg-amber-50 text-amber-900 ring-1 ring-amber-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="text" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>📝 Teks</span>
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition shadow-2xs"
                                    :class="editMaterialData.material_type === 'archive' ? 'border-teal-700 bg-teal-50 text-teal-900 ring-1 ring-teal-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                                    <input type="radio" name="material_type" value="archive" x-model="editMaterialData.material_type" class="sr-only">
                                    <span>📦 Arsip ZIP</span>
                                </label>
                            </div>
                        </div>

                        {{-- File Upload Section (with Current File Info) --}}
                        <div x-show="['document', 'audio', 'video', 'archive'].includes(editMaterialData.material_type)" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                            <div x-show="editMaterialData.file_name" class="flex items-center justify-between rounded-xl bg-white border border-slate-200 p-2.5 text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="text-base">📁</span>
                                    <span class="font-bold text-slate-800 truncate" x-text="editMaterialData.file_name"></span>
                                    <span class="text-slate-400 font-mono text-[11px]" x-show="editMaterialData.file_size" x-text="'(' + editMaterialData.file_size + ')'"></span>
                                </div>
                                <span class="rounded bg-teal-100 text-teal-800 px-2 py-0.5 text-[10px] font-bold shrink-0">Berkas Eksisting</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-800">
                                    <span x-text="editMaterialData.file_name ? 'Ganti Berkas Materi (Opsional, Kosongkan jika berkas tetap)' : 'Unggah Berkas Materi (Maksimal 100 MB)'"></span>
                                </label>
                                <input type="file" name="file" class="mt-1.5 block w-full text-xs text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-teal-800 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-teal-700 cursor-pointer">
                                <p class="mt-1 text-[11px] text-slate-500">Format didukung: PDF, DOCX, PPTX, XLSX, TXT, MP3, WAV, M4A, OGG, MP4, WebM, ZIP.</p>
                            </div>
                        </div>

                        {{-- External Link Input --}}
                        <div x-show="editMaterialData.material_type === 'link'" x-cloak class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4">
                            <label class="block text-xs font-bold text-blue-950">Tautan / URL Eksternal *</label>
                            <input type="url" name="external_url" x-model="editMaterialData.external_url" placeholder="https://www.youtube.com/watch?v=... atau Google Drive"
                                class="mt-1.5 w-full rounded-xl border-blue-300 text-xs sm:text-sm focus:border-blue-600 focus:ring-blue-600 bg-white">
                        </div>

                        {{-- Direct Text Input --}}
                        <div x-show="editMaterialData.material_type === 'text'" x-cloak class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4">
                            <label class="block text-xs font-bold text-amber-950">Isi Catatan / Bahan Bacaan Kuliah *</label>
                            <textarea name="content_text" x-model="editMaterialData.content_text" rows="5" placeholder="Tuliskan materi, rangkuman teori, atau instruksi perkuliahan langsung..."
                                class="mt-1.5 w-full rounded-xl border-amber-300 text-xs sm:text-sm focus:border-amber-600 focus:ring-amber-600 bg-white"></textarea>
                        </div>

                        {{-- Petunjuk Pembelajaran / Catatan Mahasiswa --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700">Petunjuk Pembelajaran / Catatan untuk Mahasiswa</label>
                            <input type="text" name="description" x-model="editMaterialData.description" placeholder="Misal: Pelajari sebelum pertemuan dimulai untuk diskusi kelompok."
                                class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                        </div>

                        {{-- Footer Modal --}}
                        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                            <button type="button" @click="editMaterialModalOpen = false"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- MODAL AI GENERATOR PENUGASAN (DeepSeek, Gemini, ChatGPT) --}}
        @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
            @php
                $aiServiceInstance = app(\App\Services\Ai\AssignmentAiService::class);
                $detectedDomainKey = $aiServiceInstance->resolveCourseDomain($course);
                $detectedDomainTitle = $aiServiceInstance->domainLabel($detectedDomainKey);
            @endphp
            <div x-show="aiModalOpen"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
                @keydown.escape.window="if(!aiGenerating) aiModalOpen = false">
                <div class="relative w-full max-w-3xl rounded-3xl border border-teal-200/90 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="if(!aiGenerating) aiModalOpen = false">
                    
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-teal-900 via-slate-900 to-teal-950 text-white px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-500 text-slate-950 flex items-center justify-center font-black text-lg shadow-sm animate-pulse">
                                ✨
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-extrabold text-white">Asisten AI Generator Penugasan</h3>
                                <p class="text-xs text-teal-200">Perancang tugas berbasis materi pertemuan aktual, CPMK kurikulum, dan domain keilmuan prodi.</p>
                            </div>
                        </div>
                        <button type="button" @click="if(!aiGenerating) aiModalOpen = false"
                            class="rounded-xl p-1.5 text-slate-300 hover:bg-white/10 hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5 text-xs sm:text-sm">
                        {{-- Pemilihan Provider AI --}}
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-3.5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Pilih AI Provider Engine:</span>
                                <span class="text-[11px] font-semibold text-slate-500">Mendukung multi-provider</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="aiProvider = 'deepseek'"
                                    :class="aiProvider === 'deepseek'
                                        ? 'border-purple-600 bg-purple-50/90 text-purple-950 ring-2 ring-purple-600 font-extrabold shadow-2xs'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold'"
                                    class="rounded-xl border p-2.5 text-center transition flex flex-col items-center gap-1">
                                    <span class="text-sm">🟣 DeepSeek AI</span>
                                    <span class="text-[10px] text-purple-700 font-mono">deepseek-chat (Default)</span>
                                </button>

                                <button type="button" @click="aiProvider = 'gemini'"
                                    :class="aiProvider === 'gemini'
                                        ? 'border-teal-600 bg-teal-50/90 text-teal-950 ring-2 ring-teal-600 font-extrabold shadow-2xs'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold'"
                                    class="rounded-xl border p-2.5 text-center transition flex flex-col items-center gap-1">
                                    <span class="text-sm">🔵 Google Gemini</span>
                                    <span class="text-[10px] text-teal-700 font-mono">gemini-1.5-flash</span>
                                </button>

                                <button type="button" @click="aiProvider = 'openai'"
                                    :class="aiProvider === 'openai'
                                        ? 'border-emerald-600 bg-emerald-50/90 text-emerald-950 ring-2 ring-emerald-600 font-extrabold shadow-2xs'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold'"
                                    class="rounded-xl border p-2.5 text-center transition flex flex-col items-center gap-1">
                                    <span class="text-sm">🟢 ChatGPT</span>
                                    <span class="text-[10px] text-emerald-700 font-mono">gpt-4o-mini</span>
                                </button>
                            </div>
                        </div>

                        {{-- Konteks Mata Kuliah & Domain Terdeteksi --}}
                        <div class="rounded-2xl border border-teal-200/80 bg-teal-50/50 p-4 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2 text-teal-900 font-bold text-xs uppercase tracking-wider">
                                    <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Konteks Akademik Kelas Terdeteksi</span>
                                </div>
                                <span class="rounded-full bg-teal-800 text-white px-2.5 py-0.5 text-[10px] font-bold tracking-wide">
                                    Domain: {{ $detectedDomainTitle }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-700 flex flex-wrap items-center gap-x-4 gap-y-1">
                                <span>Mata Kuliah: <strong class="text-slate-900">{{ $course->name }} ({{ $course->code }})</strong></span>
                                <span>SKS: <strong class="text-slate-900">{{ $course->credit_hours }} SKS</strong></span>
                                <span>CPMK: <strong class="text-teal-900">{{ count($course->cpmkList()) }} Butir Terdaftar</strong></span>
                                <span>Jurnal Pertemuan: <strong class="text-slate-900">{{ $sessions->count() }} Sesi Tercatat</strong></span>
                            </div>
                        </div>

                        {{-- Form Input Parameter Dosen --}}
                        <div class="space-y-4">
                            {{-- 1. Pilihan Sesi Pertemuan (Sumber Pokok Bahasan) --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    Pilih Pertemuan Perkuliahan (Sumber Materi Tugas) *
                                </label>
                                <select x-model="aiSessionId" class="w-full rounded-xl border-slate-300 text-xs sm:text-sm font-semibold focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                                    <option value="">-- Buat Penugasan Mandiri (Ketik Topik Manual) --</option>
                                    <template x-for="sess in sessionsData" :key="sess.id">
                                        <option :value="sess.id" x-text="'Pertemuan Ke-' + sess.session_number + ': ' + sess.topic"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Info Card Sesi Terpilih --}}
                            <div x-show="selectedSessionInfo()" x-cloak class="rounded-2xl border border-indigo-200 bg-indigo-50/70 p-3.5 space-y-1.5 text-xs text-indigo-950">
                                <div class="flex items-center justify-between">
                                    <span class="font-extrabold uppercase tracking-wider text-[11px] text-indigo-800"
                                        x-text="'Pertemuan Ke-' + selectedSessionInfo()?.session_number + ' (' + (selectedSessionInfo()?.date_formatted || 'Sesuai Jurnal') + ')'"></span>
                                    <span class="rounded-md bg-indigo-100 text-indigo-800 px-2 py-0.5 text-[10px] font-bold"
                                        x-text="selectedSessionInfo()?.teaching_method || 'Metode Pembelajaran'"></span>
                                </div>
                                <p class="font-bold text-slate-900 text-xs sm:text-sm" x-text="selectedSessionInfo()?.topic"></p>
                                <p class="text-[11px] text-slate-600 line-clamp-2" x-show="selectedSessionInfo()?.notes" x-text="'Catatan Jurnal: ' + selectedSessionInfo()?.notes"></p>
                            </div>

                            {{-- Input Topik Manual (Jika Tanpa Sesi) --}}
                            <div x-show="!aiSessionId">
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    Topik / Pokok Bahasan Penugasan *
                                </label>
                                <input type="text" x-model="aiTopic" placeholder="Contoh: Analisis Kritis Koherensi Paragraf pada Artikel Jurnal Internasional"
                                    class="w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 font-semibold shadow-2xs">
                            </div>

                            {{-- Fokus Khusus Penugasan (Opsional) --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    Fokus Khusus Penugasan (Opsional)
                                </label>
                                <input type="text" x-model="aiFocus" placeholder="Contoh: Mempersempit analisis pada penggunaan connectors & transitional signals"
                                    class="w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs">
                                <p class="text-[10px] text-slate-500 mt-1">Gunakan untuk mempersempit materi pertemuan ke sub-pokok bahasan tertentu tanpa mengubah esensi topik.</p>
                            </div>

                            {{-- Format Model Tugas & Target Bloom --}}
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">Format Model Penugasan</label>
                                    <select x-model="aiType" class="w-full rounded-xl border-slate-300 text-xs sm:text-sm font-semibold focus:border-teal-700 focus:ring-teal-700">
                                        <option value="writing">Esai Akademik (WriteWise)</option>
                                        <option value="stimulus_exam">Ujian / Asesmen Stimulus (Audio/Passage)</option>
                                        <option value="hybrid">Asesmen Hibrida (PG + Esai)</option>
                                        <option value="translation">Penerjemahan & Review Teks</option>
                                        <option value="milestone_artifact">Proposal & Proyek Bertahap</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">Target Taksonomi Bloom (Level Kognitif)</label>
                                    <select x-model="aiBloom" class="w-full rounded-xl border-slate-300 text-xs sm:text-sm font-semibold focus:border-teal-700 focus:ring-teal-700">
                                        <option value="Mengingat (C1)">C1: Mengingat (Remembering)</option>
                                        <option value="Memahami (C2)">C2: Memahami (Understanding)</option>
                                        <option value="Penerapan (C3)">C3: Penerapan (Application)</option>
                                        <option value="Analisis (C4)" selected>C4: Analisis Kritis (Analysis)</option>
                                        <option value="Evaluasi (C5)">C5: Evaluasi & Sintesis (Evaluation)</option>
                                        <option value="Kreasi (C6)">C6: Kreasi & Desain Karya (Creation)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Rubrik Checkbox Switch --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 flex items-center justify-between">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">Sertakan Rubrik Penilaian Terukur</span>
                                    <span class="block text-[11px] text-slate-500">AI merumuskan kriteria capaian dengan total bobot persentase tepat 100%.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="aiIncludeRubric" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-teal-700"></div>
                                </label>
                            </div>

                            {{-- Catatan Dosen --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    Catatan Tambahan / Arahan Khusus Dosen (Opsional)
                                </label>
                                <textarea x-model="aiNotes" rows="2" placeholder="Masukkan arahan khusus dosen jika ada (misal: batasan jumlah kata, format berkas, atau fokus analisis khusus). Kosongkan jika tidak ada."
                                    class="w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700 shadow-2xs"></textarea>
                            </div>
                        </div>

                        {{-- Tombol Generate --}}
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs text-slate-500">AI menyusun instruksi operasional sesuai parameter & materi kelas.</span>
                            <button type="button" @click="generateAssignmentWithAi()" :disabled="aiGenerating"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-800 via-teal-700 to-emerald-700 px-6 py-2.5 text-xs font-bold text-white hover:opacity-95 shadow-md transition disabled:opacity-50 cursor-pointer">
                                <span x-show="!aiGenerating">⚡ Generate Draf Tugas</span>
                                <span x-show="aiGenerating" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>AI Sedang Merumuskan...</span>
                                </span>
                            </button>
                        </div>

                        {{-- Error Alert --}}
                        <div x-show="aiError" x-cloak class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs text-rose-900 shadow-2xs flex items-start gap-3">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-600 font-bold text-white text-xs">!</span>
                            <div class="flex-1">
                                <p class="font-bold">Gagal Menghasilkan Rancangan Tugas:</p>
                                <p class="mt-0.5 leading-relaxed" x-text="aiError"></p>
                            </div>
                        </div>

                        {{-- Preview Hasil AI Generator --}}
                        <div x-show="aiResult" x-cloak class="space-y-4 border-t border-slate-200 pt-5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-teal-900 flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                                    Pratinjau Rancangan Tugas AI
                                </span>
                                <span class="rounded-full bg-teal-100 px-3 py-0.5 text-[11px] font-bold text-teal-950 font-mono" x-text="'Engine: ' + aiProvider"></span>
                            </div>

                            {{-- Judul Hasil --}}
                            <div class="rounded-2xl bg-teal-50/70 border border-teal-200 p-4">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-teal-800">Judul Tugas yang Dirumuskan:</span>
                                    <span class="rounded-md bg-white border border-teal-200 px-2 py-0.5 text-[10px] font-bold text-teal-900 uppercase" x-text="aiResult?.assignment_type"></span>
                                </div>
                                <h4 class="text-base font-extrabold text-slate-900" x-text="aiResult?.title"></h4>
                                <p class="text-xs text-slate-600 mt-1" x-text="aiResult?.summary"></p>
                            </div>

                            {{-- Instruksi Langkah demi Langkah --}}
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 block mb-2">Instruksi Pengerjaan Langkah demi Langkah:</span>
                                <div class="text-xs leading-relaxed text-slate-800 whitespace-pre-wrap max-h-64 overflow-y-auto font-sans" x-text="aiResult?.instructions"></div>
                            </div>

                            {{-- Rubrik Penilaian Berkriteria --}}
                            <div x-show="aiResult?.rubric && aiResult?.rubric.length > 0" class="rounded-2xl border border-slate-200 overflow-hidden">
                                <div class="bg-slate-100 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Kriteria Rubrik Penilaian:</span>
                                    <span class="text-[11px] font-bold text-teal-800">Total Bobot: 100%</span>
                                </div>
                                <div class="divide-y divide-slate-100 text-xs">
                                    <template x-for="(item, idx) in (aiResult?.rubric || [])" :key="idx">
                                        <div class="p-3 bg-white flex items-start justify-between gap-3">
                                            <div>
                                                <span class="font-bold text-slate-900" x-text="(idx + 1) + '. ' + item.criterion"></span>
                                                <p class="text-slate-500 text-[11px] mt-0.5" x-text="item.description"></p>
                                            </div>
                                            <span class="rounded-lg bg-teal-50 border border-teal-200 px-2.5 py-1 text-xs font-extrabold text-teal-900 font-mono shrink-0"
                                                x-text="item.weight_percentage + '%'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4">
                        <button type="button" @click="if(!aiGenerating) aiModalOpen = false"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                            Tutup
                        </button>

                        <div class="flex items-center gap-2">
                            <button x-show="aiResult" x-cloak type="button" @click="generateAssignmentWithAi()" :disabled="aiGenerating"
                                class="rounded-xl border border-teal-300 bg-white px-4 py-2 text-xs font-bold text-teal-800 hover:bg-teal-50 transition">
                                🔄 Generate Ulang
                            </button>

                            <button x-show="aiResult" x-cloak type="button" @click="applyAiToForm()"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-5 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                <span>✅ Terapkan ke Formulir Tugas</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Toast Notifikasi Sukses Terapkan AI --}}
        <div x-show="aiAppliedToast" x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4"
            class="fixed bottom-6 right-6 z-50 rounded-2xl bg-slate-900 border border-teal-500/50 p-4 text-white shadow-2xl flex items-center gap-3.5 max-w-sm">
            <div class="h-9 w-9 rounded-xl bg-teal-500/20 text-teal-300 border border-teal-400/40 flex items-center justify-center font-black text-base shrink-0">
                ✓
            </div>
            <div>
                <p class="font-extrabold text-white text-xs">Rancangan AI Berhasil Diterapkan!</p>
                <p class="text-slate-300 text-[11px] mt-0.5">Judul, instruksi langkah kerja, dan kriteria rubrik telah terisi ke formulir penugasan.</p>
            </div>
        </div>
    </div>
</div>

<script>
function courseDetailDashboard() {
    return {
        tab: '{{ in_array(request('tab'), ['materials', 'sessions', 'assignments', 'students'], true) ? request('tab') : 'materials' }}',
        docModalOpen: false,
        docModalTitle: '',
        docModalUrl: '',
        docDownloadUrl: '',
        editMaterialModalOpen: false,
        editMaterialData: {
            id: null,
            title: '',
            session_number: '',
            material_type: 'document',
            external_url: '',
            content_text: '',
            description: '',
            file_name: '',
            file_size: '',
            update_url: ''
        },
        openEditMaterialModal(data) {
            this.editMaterialData = { ...data };
            this.editMaterialModalOpen = true;
        },
        materialSearch: '',
        materialFilter: 'all',
        studentSearch: '',
        assignOpen: false,
        assignmentType: 'writing',
        aiModalOpen: false,
        aiGenerating: false,
        aiError: null,
        aiResult: null,
        sessionsData: @js($sessions->map(fn($s) => [
            'id' => $s->id,
            'session_number' => $s->session_number,
            'topic' => $s->topic,
            'teaching_method' => $s->teaching_method,
            'notes' => $s->notes,
            'date_formatted' => $s->date?->format('d/m/Y'),
        ])),
        aiSessionId: '',
        aiTopic: '',
        aiFocus: '',
        aiType: 'writing',
        aiBloom: 'Analisis (C4)',
        aiIncludeRubric: true,
        aiNotes: '',
        aiProvider: '{{ config('ai.assignment_provider', 'deepseek') }}',
        aiAppliedToast: false,
        selectedSessionInfo() {
            if (!this.aiSessionId) return null;
            return this.sessionsData.find(s => s.id == this.aiSessionId) || null;
        },
        openDocPreview(title, viewerUrl, dlUrl) {
            this.docModalTitle = title;
            this.docModalUrl = viewerUrl;
            this.docDownloadUrl = dlUrl;
            this.docModalOpen = true;
        },
        openAiModal() {
            this.aiModalOpen = true;
            this.aiError = null;
            if (!this.aiSessionId && this.sessionsData.length > 0) {
                const latest = this.sessionsData[this.sessionsData.length - 1];
                this.aiSessionId = latest.id;
            }
        },
        async generateAssignmentWithAi() {
            const currentSession = this.selectedSessionInfo();
            if (!this.aiSessionId && (!this.aiTopic || !this.aiTopic.trim())) {
                this.aiError = 'Silakan pilih sesi pertemuan perkuliahan atau masukkan topik penugasan terlebih dahulu.';
                return;
            }
            this.aiGenerating = true;
            this.aiError = null;
            this.aiResult = null;

            try {
                const response = await fetch('{{ route('courses.assignments.ai-generate', $course) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        session_id: this.aiSessionId || null,
                        topic: currentSession ? currentSession.topic : this.aiTopic,
                        assignment_focus: this.aiFocus || null,
                        assignment_type: this.aiType,
                        bloom_level: this.aiBloom,
                        include_rubric: this.aiIncludeRubric,
                        notes: this.aiNotes,
                        provider: this.aiProvider
                    })
                });

                const json = await response.json();
                if (!response.ok || !json.success) {
                    throw new Error(json.message || 'Gagal menghasilkan tugas dengan AI.');
                }

                this.aiResult = json.data;
            } catch (err) {
                this.aiError = err.message || 'Terjadi gangguan saat memproses AI.';
            } finally {
                this.aiGenerating = false;
            }
        },
        applyAiToForm() {
            if (!this.aiResult) return;

            const titleInput = document.getElementById('assignment_title_input');
            const instructionsTextarea = document.getElementById('assignment_instructions_input');
            const sessionSelect = document.getElementById('assignment_course_session_id');

            if (titleInput) {
                titleInput.value = this.aiResult.title;
            }

            let instructions = (this.aiResult.instructions || '').trim();
            instructions = instructions.replace(/(?<!^)(?<!\n)\s+(\d+\.\s+)/g, '\n\n$1').trim();
            instructions = instructions.replace(/(?<!\n)\s+([a-z]\.\s+)/g, '\n   $1').trim();

            let fullText = "PETUNJUK & LANGKAH PENGERJAAN TUGAS:\n\n" + instructions;

            if (this.aiResult.rubric && this.aiResult.rubric.length > 0) {
                fullText += '\n\n' + '='.repeat(55) + '\n';
                fullText += 'RUBRIK PENILAIAN TUGAS (CRITERIA & WEIGHTS)\n';
                fullText += '='.repeat(55) + '\n';
                this.aiResult.rubric.forEach((r, idx) => {
                    fullText += `\n${idx + 1}. ${r.criterion} (Bobot: ${r.weight_percentage}%)\n   Standar Pencapaian: ${r.description}\n`;
                });
            }

            if (instructionsTextarea) {
                instructionsTextarea.value = fullText;
            }

            if (this.aiResult.assignment_type) {
                this.assignmentType = this.aiResult.assignment_type;
            }

            if (this.aiSessionId && sessionSelect) {
                sessionSelect.value = this.aiSessionId;
            }

            this.assignOpen = true;
            this.aiModalOpen = false;
            this.aiAppliedToast = true;
            setTimeout(() => { this.aiAppliedToast = false; }, 5000);

            this.$nextTick(() => {
                if (titleInput) {
                    titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    titleInput.focus();
                }
            });
        }
    };
}
</script>
@endsection
