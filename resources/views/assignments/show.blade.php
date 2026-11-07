@extends('layouts.hub')
@section('title', $assignment->title)
@section('content')
<div x-data="{ rubricModalOpen: false }" class="space-y-6">
    {{-- Breadcrumb Navigasi Berjenjang --}}
    <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
        @if($assignment->course)
            <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-1.5 hover:text-teal-800 transition">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Mata Kuliah</span>
            </a>
            <svg class="h-3 w-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']) }}" class="font-mono font-bold text-teal-800 uppercase tracking-wide hover:underline">
                {{ $assignment->course->code }}
            </a>
            <svg class="h-3 w-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']) }}" class="text-slate-600 hover:text-teal-800 transition truncate max-w-[180px] sm:max-w-xs" title="{{ $assignment->course->name }}">
                {{ $assignment->course->name }}
            </a>
            <svg class="h-3 w-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-900 font-semibold truncate">{{ $assignment->title }}</span>
        @else
            <a href="{{ auth()->user()->isStaff() ? route('projects.show', $assignment->project) : route('dashboard') }}" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-teal-800 transition">
                <span>{{ $assignment->project->name }}</span>
            </a>
            <svg class="h-3 w-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-900 font-semibold truncate">{{ $assignment->title }}</span>
        @endif
    </nav>

    {{-- Executive Header Banner & Cockpit --}}
    <section class="relative overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xs">
        {{-- Brand Accent Top Strip --}}
        <div class="h-2 w-full bg-gradient-to-r from-teal-800 via-teal-600 to-emerald-500"></div>

        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div class="space-y-3">
                    {{-- Badges Strip --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @if($assignment->course)
                            <a href="{{ route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-50 px-3 py-1 font-mono text-xs font-extrabold uppercase tracking-wider text-teal-900 border border-teal-200/80 shadow-2xs hover:bg-teal-100 transition"
                                title="Kembali ke halaman mata kuliah">
                                <span class="h-2 w-2 rounded-full bg-teal-700"></span>
                                {{ $assignment->course->code }} · {{ $assignment->course->class_group ?? 'Kelas' }}
                            </a>
                        @else
                            <span class="text-xs font-bold uppercase tracking-wider text-teal-800">{{ $assignment->project->name }}</span>
                        @endif

                        @if(!empty($assignment->config_json['session_number']))
                            <span class="inline-flex items-center gap-1 rounded-xl bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 border border-slate-200/60">
                                Pertemuan {{ $assignment->config_json['session_number'] }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 rounded-xl bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200/70">
                            {{ $assignment->typeLabel() }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-xl {{ $assignment->status === 'published' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-amber-50 text-amber-800 border border-amber-200' }} px-3 py-1 text-xs font-bold">
                            <span class="h-1.5 w-1.5 rounded-full {{ $assignment->status === 'published' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }}
                        </span>
                    </div>

                    {{-- Judul Tugas --}}
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 leading-snug">
                        {{ $assignment->title }}
                    </h1>

                    {{-- Strip Metadata Terpadu: Tenggat, Rubrik, & Topik Sesi --}}
                    <div class="flex flex-wrap items-center gap-2.5 pt-0.5 text-xs">
                        @if($assignment->due_at)
                            <span class="inline-flex items-center gap-1.5 font-bold text-amber-900 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200/80 shadow-2xs">
                                <svg class="h-3.5 w-3.5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }} WIB</span>
                            </span>
                        @endif

                        @if($assignment->currentRubric)
                            <button type="button" @click="rubricModalOpen = true"
                                class="inline-flex items-center gap-1.5 font-bold text-teal-900 bg-teal-50 hover:bg-teal-100 border border-teal-200/80 px-3 py-1.5 rounded-xl transition cursor-pointer shadow-2xs">
                                <svg class="h-3.5 w-3.5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <span>Rubrik: {{ $assignment->currentRubric->name }} (v{{ $assignment->currentRubric->version }})</span>
                            </button>
                        @endif

                        @if(!empty($assignment->config_json['session_topic']))
                            <span class="inline-flex items-center gap-1.5 text-slate-600 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs">
                                <svg class="h-3.5 w-3.5 text-teal-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>Topik Sesi: <strong class="text-slate-800">{{ $assignment->config_json['session_topic'] }}</strong></span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Action Cluster Kanan --}}
                <div class="flex flex-wrap items-center gap-2">
                    @if(auth()->user()->isStaff() && ($assignment->course_id ? (auth()->id() === $assignment->course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)) : ($assignment->project->isOwnedBy(auth()->user()) || $assignment->project->hasEditor(auth()->user()))))
                        @if($assignment->isStimulusExam() || $assignment->isHybrid())
                            <a href="{{ route('assignments.questions.index', $assignment) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-900 hover:bg-amber-100 transition shadow-2xs">
                                <svg class="h-4 w-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Kelola Soal ({{ $assignment->questions->count() }})</span>
                            </a>
                        @endif
                        @if($assignment->course_id)
                            <a href="{{ route('courses.assignments.edit', [$assignment->course, $assignment]) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-teal-900 transition shadow-2xs">
                                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Sunting</span>
                            </a>
                        @endif
                        <form method="post" action="{{ $assignment->course_id ? route('courses.assignments.destroy', [$assignment->course, $assignment]) : route('assignments.destroy', $assignment) }}" onsubmit="return confirm('Hapus tugas ini secara permanen?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition shadow-2xs">
                                <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @php
                $hasCpmk = (bool) ($assignment->course && !empty($assignment->config_json['cpmk_outcomes']));
                $sections = $assignment->config_json['sections'] ?? [];
            @endphp

            {{-- Grid Konten Tugas: Seimbang 2 Kolom (Kiri: Deskripsi & Sub-bagian | Kanan: CPMK) --}}
            <div class="grid gap-6 {{ $hasCpmk ? 'lg:grid-cols-12' : '' }} items-stretch">
                {{-- Kolom Kiri: Deskripsi Tugas & Sub-Bagian Pengerjaan --}}
                <div class="{{ $hasCpmk ? 'lg:col-span-6' : 'w-full' }} flex flex-col">
                    <div class="h-full rounded-2xl border border-slate-200/90 bg-slate-50/50 p-5 shadow-2xs flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-slate-200/70 pb-3">
                                <div class="flex items-center gap-2 text-slate-800 font-bold text-xs uppercase tracking-wider">
                                    <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Deskripsi &amp; Arahan Tugas</span>
                                </div>
                                @if(!empty($sections))
                                    <span class="rounded-full bg-slate-200/80 text-slate-700 px-2.5 py-0.5 text-[10px] font-bold border border-slate-300/60">
                                        {{ count($sections) }} Bagian
                                    </span>
                                @endif
                            </div>

                            @if($assignment->instructions)
                                <div class="whitespace-pre-line text-xs sm:text-sm leading-relaxed text-slate-700 font-sans">
                                    {{ $assignment->instructions }}
                                </div>
                            @endif
                        </div>

                        {{-- Sub-bagian Pengerjaan (Clean List, Bebas Box-ception) --}}
                        @if(!empty($sections))
                            <div class="pt-3 border-t border-slate-200/70 space-y-2">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Rincian Sub-Bagian Tugas</span>
                                <div class="space-y-2">
                                    @foreach($sections as $sec)
                                        <div class="flex items-center justify-between gap-3 rounded-xl bg-white p-3 border border-slate-200/80 shadow-2xs">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-bold text-xs text-slate-900">{{ $sec['title'] ?? 'Bagian' }}</span>
                                                    @if(!empty($sec['prompt']))
                                                        <span class="text-xs text-slate-300">·</span>
                                                        <span class="text-xs text-slate-600 truncate max-w-xs">{{ $sec['prompt'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if(!empty($sec['weight']))
                                                <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-bold text-teal-800 border border-teal-200/80 shrink-0">
                                                    Bobot {{ $sec['weight'] }}%
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Kolom Kanan: Capaian Pembelajaran CPMK --}}
                @if($hasCpmk)
                    <div class="lg:col-span-6 flex flex-col">
                        <div class="h-full rounded-2xl border border-teal-200/80 bg-teal-50/40 p-5 shadow-2xs flex flex-col justify-between space-y-3">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 border-b border-teal-200/60 pb-3">
                                    <div class="flex items-center gap-2 text-teal-950 font-bold text-xs uppercase tracking-wider">
                                        <svg class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Capaian Pembelajaran (CPMK)</span>
                                    </div>
                                    <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900 border border-teal-200">
                                        {{ count($assignment->config_json['cpmk_outcomes']) }} Capaian
                                    </span>
                                </div>
                                <ol class="space-y-2.5 text-xs text-slate-700">
                                    @foreach($assignment->config_json['cpmk_outcomes'] as $idx => $outcome)
                                        <li class="flex items-start gap-2.5 leading-relaxed">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-teal-800 text-[10px] font-black text-white mt-0.5 shadow-2xs">
                                                {{ $idx + 1 }}
                                            </span>
                                            <span class="flex-1">{{ preg_replace('/^(?:(?:\d+|cpmk\s*\d*)[.:)\s]+|[-*•]\s*)/iu', '', $outcome) }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

{{-- Modal Pop-up Rubrik Penilaian --}}
@if($assignment->currentRubric)
    <div x-show="rubricModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="rubric-modal-title" role="dialog" aria-modal="true"
        @keydown.escape.window="rubricModalOpen = false">

        <div x-show="rubricModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click="rubricModalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            <div x-show="rubricModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white p-6 sm:p-7 text-left shadow-2xl transition-all border border-slate-200">

                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal-800 text-white font-bold text-lg shadow-xs">
                            📋
                        </span>
                        <div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-teal-100 text-teal-800 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                                Rubrik Penilaian
                            </span>
                            <h3 id="rubric-modal-title" class="text-lg sm:text-xl font-extrabold text-slate-900 mt-0.5">
                                {{ $assignment->currentRubric->name }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Versi {{ $assignment->currentRubric->version }} · Skala Penilaian: <strong>{{ $assignment->currentRubric->scale_min }} – {{ $assignment->currentRubric->scale_max }}</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="rubricModalOpen = false"
                        class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition focus:outline-none cursor-pointer">
                        <span class="sr-only">Tutup</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 max-h-[60vh] overflow-y-auto space-y-3 pr-1">
                    @foreach($assignment->currentRubric->criteria as $criterion)
                        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 transition hover:border-slate-300">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <span>{{ $criterion['name'] }}</span>
                                </h4>
                                <span class="inline-flex items-center rounded-lg bg-teal-100 text-teal-900 px-2.5 py-0.5 text-xs font-bold border border-teal-200">
                                    Bobot {{ $criterion['weight'] }}
                                </span>
                            </div>
                            @if(!empty($criterion['description']))
                                <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed font-sans">
                                    {{ $criterion['description'] }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">
                    <button type="button" @click="rubricModalOpen = false"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs sm:text-sm font-bold text-white hover:bg-slate-800 transition shadow cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@if(auth()->user()->isStaff() && $assignment->project->archived_at === null)
    @if(!$assignment->course_id)
        <details class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs">
            <summary class="cursor-pointer text-sm sm:text-base font-bold text-slate-900">Sunting tugas dan penugasan mahasiswa</summary>
            <p class="mt-2 text-sm text-slate-600">Setiap penyimpanan membuat versi tugas baru. Submission yang sudah ada tetap tertaut ke versi tugas saat mahasiswa mengirimnya.</p>
            <form method="post" action="{{ route('assignments.update', $assignment) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-semibold text-slate-700 md:col-span-2">Judul<input name="title" required maxlength="180" value="{{ old('title', $assignment->title) }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm"></label>
                <label class="block text-sm font-semibold text-slate-700 md:col-span-2">Instruksi<textarea name="instructions" required maxlength="10000" rows="4" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('instructions', $assignment->instructions) }}</textarea></label>
                <label class="block text-sm font-semibold text-slate-700">Bahasa feedback<select name="feedback_language" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm"><option value="id" @selected($assignment->feedback_language === 'id')>Indonesia</option><option value="en" @selected($assignment->feedback_language === 'en')>English</option></select></label>
                <label class="block text-sm font-semibold text-slate-700">Gaya feedback<select name="feedback_level" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm"><option value="indirect" @selected($assignment->feedback_level === 'indirect')>Indirect</option><option value="direct" @selected($assignment->feedback_level === 'direct')>Direct</option><option value="socratic" @selected($assignment->feedback_level === 'socratic')>Socratic</option></select></label>
                <label class="block text-sm font-semibold text-slate-700">Tenggat (opsional)<input type="datetime-local" name="due_at" value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\\TH:i')) }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm"></label>
                <label class="block text-sm font-semibold text-slate-700">Status<select name="status" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm"><option value="draft" @selected($assignment->status === 'draft')>Draf</option><option value="published" @selected($assignment->status === 'published')>Terbit</option></select></label>
                <label class="block text-sm font-semibold text-slate-700 md:col-span-2">Email mahasiswa yang ditugaskan<textarea name="student_emails" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">{{ old('student_emails', $assignment->students->pluck('email')->implode(', ')) }}</textarea><span class="mt-1 block text-xs font-normal text-slate-500">Akun mahasiswa yang dihapus dari daftar tidak lagi dapat membuka tugas ini.</span></label>
                <fieldset class="md:col-span-2">
                    <legend class="font-bold text-slate-900">Rubrik versi baru</legend>
                    <p class="mt-1 text-xs text-slate-500">Versi rubrik baru dibuat hanya jika nama, skala, atau kriteria berubah.</p>
                    @php
                        $rubric = $assignment->currentRubric;
                    @endphp
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <label class="text-sm font-medium sm:col-span-1">Nama rubrik<input name="rubric_name" required maxlength="120" value="{{ old('rubric_name', $rubric?->name ?? 'Rubrik menulis') }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm"></label>
                        <label class="text-sm font-medium">Skala minimum<input type="number" name="scale_min" required min="0" max="100" value="{{ old('scale_min', $rubric?->scale_min ?? 1) }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm"></label>
                        <label class="text-sm font-medium">Skala maksimum<input type="number" name="scale_max" required min="1" max="100" value="{{ old('scale_max', $rubric?->scale_max ?? 5) }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm"></label>
                    </div>
                    @foreach(($rubric?->criteria ?? []) as $index => $criterion)
                        <div class="mt-3 grid gap-3 rounded-xl bg-slate-50 p-3.5 sm:grid-cols-12">
                            <label class="text-sm font-medium sm:col-span-3">Kriteria<input name="criteria[{{ $index }}][name]" required maxlength="120" value="{{ old("criteria.$index.name", $criterion['name'] ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                            <label class="text-sm font-medium sm:col-span-7">Deskripsi<textarea name="criteria[{{ $index }}][description]" maxlength="1000" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old("criteria.$index.description", $criterion['description'] ?? '') }}</textarea></label>
                            <label class="text-sm font-medium sm:col-span-2">Bobot<input type="number" step="0.1" min="0.1" max="100" name="criteria[{{ $index }}][weight]" required value="{{ old("criteria.$index.weight", $criterion['weight'] ?? 1) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                        </div>
                    @endforeach
                </fieldset>
                <div class="md:col-span-2">
                    <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition">Simpan versi tugas</button>
                </div>
            </form>
        </details>
        <details class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <summary class="cursor-pointer text-sm sm:text-base font-bold text-slate-900">Riwayat versi tugas</summary>
            <ol class="mt-3 space-y-2">
                @foreach($assignment->versions as $version)
                    <li class="rounded-xl bg-slate-50 p-3 text-sm"><span class="font-semibold">Versi {{ $version->version }}</span> · {{ $version->title }} · {{ $version->status === 'published' ? 'Terbit' : 'Draf' }} · {{ $version->created_at->format('d M Y H:i') }}</li>
                @endforeach
            </ol>
        </details>
        @if($assignment->rubric->count() > 1)
            <details class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
                <summary class="cursor-pointer text-sm sm:text-base font-bold text-slate-900">Riwayat versi rubrik ({{ $assignment->rubric->count() }})</summary>
                <ol class="mt-3 space-y-2">
                    @foreach($assignment->rubric as $rubric)
                        <li class="rounded-xl bg-slate-50 p-3 text-sm"><span class="font-semibold">v{{ $rubric->version }} · {{ $rubric->name }}</span> · skala {{ $rubric->scale_min }}–{{ $rubric->scale_max }} · {{ $rubric->created_at->format('d M Y H:i') }}</li>
                    @endforeach
                </ol>
            </details>
        @endif
    @endif
@endif

@if(auth()->user()->role === 'student')
    @include('assignments.show-student')
@else
    @include('assignments.show-staff')
@endif
</div>
@endsection
