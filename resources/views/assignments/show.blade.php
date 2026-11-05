@extends('layouts.hub')
@section('title', $assignment->title)
@section('content')
<a href="{{ $assignment->course ? route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']) : (auth()->user()->isStaff() ? route('projects.show', $assignment->project) : route('dashboard')) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke {{ $assignment->course ? $assignment->course->name : 'Proyek' }}</a>
<section class="relative mt-3 overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-teal-700 via-teal-500 to-emerald-500"></div>
    <div class="flex flex-wrap items-start justify-between gap-3 pt-1">
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
    <div class="mt-4 whitespace-pre-wrap text-sm sm:text-base leading-relaxed text-slate-700 font-sans">{{ $assignment->instructions }}</div>
    @if($assignment->course && !empty($assignment->config_json['cpmk_outcomes']))
        <div class="mt-4 rounded-2xl border border-indigo-200 bg-indigo-50/60 p-4">
            <h2 class="text-xs font-bold uppercase tracking-wide text-indigo-950">CPMK</h2>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-xs sm:text-sm text-indigo-950">
                @foreach($assignment->config_json['cpmk_outcomes'] as $outcome)<li>{{ preg_replace('/^(?:(?:\d+|cpmk\s*\d*)[.:)\s]+|[-*•]\s*)/iu', '', $outcome) }}</li>@endforeach
            </ul>
        </div>
    @endif
    <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 border-t border-slate-100 pt-4 text-xs sm:text-sm text-slate-500">
        @if($assignment->due_at)
            <span class="font-medium text-amber-800">Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }} WIB</span>
        @endif
        @if($assignment->currentRubric)
            <span>Rubrik: {{ $assignment->currentRubric->name }} (v{{ $assignment->currentRubric->version }})</span>
        @endif
    </div>
</section>

@if($assignment->currentRubric)
    <details class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
        <summary class="cursor-pointer text-sm sm:text-base font-bold text-slate-900">Rubrik {{ $assignment->currentRubric->name }} · versi {{ $assignment->currentRubric->version }}</summary>
        <p class="mt-2 text-xs sm:text-sm text-slate-600">Skala {{ $assignment->currentRubric->scale_min }}–{{ $assignment->currentRubric->scale_max }}</p>
        <ul class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach($assignment->currentRubric->criteria as $criterion)
                <li class="rounded-xl border border-slate-200/80 bg-slate-50 p-3.5 text-sm">
                    <strong class="text-slate-900">{{ $criterion['name'] }}</strong>
                    <span class="ml-1 text-xs font-semibold text-teal-800">· bobot {{ $criterion['weight'] }}</span>
                    @if(!empty($criterion['description']))
                        <p class="mt-1 text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $criterion['description'] }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </details>
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
                    @php($rubric = $assignment->currentRubric)
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
@endsection
