@extends('layouts.hub')
@section('title', $assignment->title)
@section('content')
<div x-data="{ rubricModalOpen: false }">
<a href="{{ $assignment->course ? route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']) : (auth()->user()->isStaff() ? route('projects.show', $assignment->project) : route('dashboard')) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke {{ $assignment->course ? $assignment->course->name : 'Proyek' }}</a>
<section class="relative mt-3 overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-teal-700 via-teal-500 to-emerald-500"></div>
    <div class="flex flex-wrap items-start justify-between gap-3 pt-1 border-b border-slate-100 pb-5">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-teal-800">{{ $assignment->course ? $assignment->course->code.' · '.$assignment->course->name : $assignment->project->name }}</span>
                @if(!empty($assignment->config_json['session_number']))
                    <span class="rounded-lg bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 text-[11px] font-bold text-indigo-800">
                        Pertemuan {{ $assignment->config_json['session_number'] }}
                    </span>
                @endif
                <span class="rounded-lg bg-teal-50 border border-teal-200 px-2.5 py-0.5 text-[11px] font-bold text-teal-800">
                    {{ $assignment->typeLabel() }}
                </span>
                <span class="rounded-lg {{ $assignment->status === 'published' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }} px-2.5 py-0.5 text-[11px] font-bold">
                    {{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }}
                </span>
            </div>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">{{ $assignment->title }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isStaff() && ($assignment->course_id ? (auth()->id() === $assignment->course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)) : ($assignment->project->isOwnedBy(auth()->user()) || $assignment->project->hasEditor(auth()->user()))))
                @if($assignment->isStimulusExam() || $assignment->isHybrid())
                    <a href="{{ route('assignments.questions.index', $assignment) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-900 hover:bg-amber-100 transition">
                        <span>Kelola Soal ({{ $assignment->questions->count() }})</span>
                    </a>
                @endif
                @if($assignment->course_id)
                    <a href="{{ route('courses.assignments.edit', [$assignment->course, $assignment]) }}" class="inline-flex items-center gap-1 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Sunting
                    </a>
                @endif
                <form method="post" action="{{ $assignment->course_id ? route('courses.assignments.destroy', [$assignment->course, $assignment]) : route('assignments.destroy', $assignment) }}" onsubmit="return confirm('Hapus tugas ini secara permanen?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1 rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>

    @php
        $hasCpmk = (bool) ($assignment->course && !empty($assignment->config_json['cpmk_outcomes']));
    @endphp

    {{-- Layout 2 Kolom: Kiri (Instruksi & Tenggat/Rubrik) & Kanan (CPMK) --}}
    <div class="mt-5 grid gap-6 {{ $hasCpmk ? 'lg:grid-cols-12' : '' }}">
        <div class="{{ $hasCpmk ? 'lg:col-span-7' : 'w-full' }} flex flex-col justify-between space-y-4">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Instruksi &amp; Uraian Tugas</h2>
                <div class="whitespace-pre-wrap text-sm sm:text-base leading-relaxed text-slate-700 font-sans">
                    {{ $assignment->instructions }}
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 pt-3 border-t border-slate-100 text-xs sm:text-sm">
                @if($assignment->due_at)
                    <span class="inline-flex items-center gap-1.5 font-medium text-amber-800 bg-amber-50/80 px-2.5 py-1 rounded-lg border border-amber-200/70">
                        <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }} WIB</span>
                    </span>
                @endif
                @if($assignment->currentRubric)
                    <button type="button" @click="rubricModalOpen = true"
                        class="inline-flex items-center gap-1.5 font-semibold text-teal-800 bg-teal-50/80 hover:bg-teal-100 border border-teal-200 px-3 py-1 rounded-lg transition cursor-pointer shadow-2xs">
                        <svg class="h-3.5 w-3.5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Rubrik: {{ $assignment->currentRubric->name }} (v{{ $assignment->currentRubric->version }})</span>
                    </button>
                @endif
            </div>
        </div>

        @if($hasCpmk)
            <div class="lg:col-span-5">
                <div class="h-full rounded-2xl border border-indigo-200 bg-indigo-50/50 p-4 sm:p-5 flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-md bg-indigo-700 text-[10px] font-bold text-white shadow-2xs">✓</span>
                            <h2 class="text-xs font-bold uppercase tracking-wide text-indigo-950">Capaian Pembelajaran (CPMK)</h2>
                        </div>
                        <ul class="mt-2 list-disc space-y-1.5 pl-5 text-xs sm:text-sm text-indigo-950 leading-relaxed">
                            @foreach($assignment->config_json['cpmk_outcomes'] as $outcome)
                                <li>{{ preg_replace('/^(?:(?:\d+|cpmk\s*\d*)[.:)\s]+|[-*•]\s*)/iu', '', $outcome) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
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
    @endif
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

@if(auth()->user()->role === 'student')
    @include('assignments.show-student')
@else
    @include('assignments.show-staff')
@endif
</div>
@endsection
