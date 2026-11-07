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

        <div class="p-6 sm:p-8">
            <div class="grid gap-6 lg:grid-cols-12 items-start">
                {{-- Kolom Kiri: Metadata, Judul, Info Pengampu, & CPMK (Span 6) --}}
                <div class="lg:col-span-6 space-y-4">
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

                    {{-- Detail Pengampu, Jadwal, & Program Studi --}}
                    <div class="grid gap-2.5 sm:grid-cols-2 pt-1 text-xs text-slate-600">
                        {{-- Dosen Pengampu --}}
                        <div class="flex items-center gap-2.5 rounded-2xl bg-slate-50/80 border border-slate-100 p-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-700 to-teal-900 font-bold text-xs text-white shadow-2xs">
                                {{ strtoupper(substr($course->lecturer->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Dosen Pengampu</span>
                                <span class="block font-bold text-slate-900 truncate" title="{{ $course->lecturer->name }}">{{ $course->lecturer->name }}</span>
                            </div>
                        </div>

                        {{-- Jadwal & Ruang Kuliah --}}
                        <div class="flex items-center gap-2.5 rounded-2xl bg-slate-50/80 border border-slate-100 p-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-800 border border-teal-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Jadwal & Ruang</span>
                                <span class="block font-bold text-slate-800 truncate" title="{{ $course->scheduleLabel() }}">{{ $course->scheduleLabel() }}</span>
                            </div>
                        </div>

                        {{-- Program Studi & Fakultas --}}
                        <div class="flex items-center gap-2.5 rounded-2xl bg-slate-50/80 border border-slate-100 p-2.5 sm:col-span-2">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 border border-slate-200">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Program Studi</span>
                                <span class="block font-bold text-slate-800 truncate" title="{{ $course->studyProgram->name }} ({{ $course->studyProgram->faculty->name ?? 'Fakultas' }})">
                                    {{ $course->studyProgram->name }} &middot; {{ $course->studyProgram->faculty->name ?? 'Fakultas' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Kartu Capaian Pembelajaran (CPMK) --}}
                    @php
                        $cpmkItems = $course->cpmkList();
                    @endphp
                    @if(count($cpmkItems) > 0 || $course->syllabus)
                        <div class="rounded-2xl border border-teal-200/80 bg-teal-50/40 p-4 sm:p-5 shadow-2xs">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2 text-teal-950 font-bold text-xs uppercase tracking-wider">
                                    <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Capaian Pembelajaran (CPMK)</span>
                                </div>
                                @if(count($cpmkItems) > 0)
                                    <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900 border border-teal-200">
                                        {{ count($cpmkItems) }} Capaian
                                    </span>
                                @endif
                            </div>

                            @if(count($cpmkItems) > 0)
                                <ol class="space-y-2 text-xs text-slate-700">
                                    @foreach($cpmkItems as $idx => $item)
                                        <li class="flex items-start gap-2.5 leading-relaxed">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-teal-800 text-[10px] font-black text-white mt-0.5 shadow-2xs">
                                                {{ $idx + 1 }}
                                            </span>
                                            <span class="flex-1">{{ preg_replace('/^(?:(?:\d+|cpmk\s*\d*)[.:\)]\s*|[-*•]\s*)/iu', '', $item) }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            @else
                                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ $course->syllabus }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Kolom Kanan: Actions & Deskripsi Mata Kuliah (Span 6) --}}
                <div class="lg:col-span-6 space-y-4">
                    {{-- Action Cluster Kanan --}}
                    @if(auth()->user()->isStaff())
                        <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2">
                            <a href="{{ route('courses.sessions.print', $course) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-teal-900 transition">
                                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                <span>Cetak Jurnal</span>
                            </a>

                            @if(auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true))
                                <a href="{{ route('courses.edit', $course) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-teal-900 transition">
                                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span>Edit MK</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    {{-- Deskripsi Mata Kuliah Card --}}
                    @if($course->description)
                        <div class="rounded-2xl border border-slate-200/90 bg-slate-50/60 p-4 sm:p-5 shadow-2xs">
                            <div class="flex items-center gap-2 mb-2.5 text-slate-900 font-bold text-xs uppercase tracking-wider">
                                <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                <span>Deskripsi Mata Kuliah</span>
                            </div>
                            <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                                {{ $course->description }}
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/40 p-5 text-center text-xs text-slate-400">
                            Belum ada deskripsi mata kuliah.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Tabbed Interactive Command Center --}}
    <div x-data="courseDetailDashboard(@js(['initialTab' => in_array(request('tab'), ['materials', 'sessions', 'assignments', 'students'], true) ? request('tab') : 'materials', 'sessionsData' => $sessions->map(fn($s) => ['id' => $s->id, 'session_number' => $s->session_number, 'topic' => $s->topic, 'teaching_method' => $s->teaching_method, 'notes' => $s->notes, 'date_formatted' => $s->date?->format('d/m/Y')])->values(), 'aiProvider' => config('ai.assignment_provider', 'deepseek'), 'aiGenerateUrl' => route('courses.assignments.ai-generate', $course), 'csrfToken' => csrf_token()]))" class="space-y-6">

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
        @include('courses.show.materials')
        @include('courses.show.sessions')
        @include('courses.show.assignments')
        @include('courses.show.students')
        @include('courses.show.modals')
    </div>
</div>

@endsection
