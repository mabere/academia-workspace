@extends('layouts.hub')
@section('title', $artifact['title'].' · Output & Evidence')
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('outputs.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Output & Evidence</a>
        <div class="mt-3 flex flex-wrap items-start justify-between gap-4 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div>
                <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-teal-800">{{ $artifact['type_label'] }} · {{ $artifact['project']->name }}</p>
                <h1 class="mt-1.5 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">{{ $artifact['title'] }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="rounded-full bg-slate-100 px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-slate-700">{{ $artifact['status_label'] }}</span>
                <a href="{{ route('outputs.download', [$artifact['type'], $artifact['id']]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs sm:text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
                    Unduh
                </a>
            </div>
        </div>
    </div>

    <article class="output-preview rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
        @include('outputs._content')
    </article>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('projects.show', $artifact['project']) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Buka proyek</a>
        @if($artifact['type'] === 'literature_source')
            <a href="{{ route('litsynth.sources.show', [$artifact['project'], $artifact['record']]) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Buka LitSynth</a>
        @elseif($artifact['type'] === 'gurubahasa_material')
            <a href="{{ route('gurubahasa.show', [$artifact['project'], $artifact['record']]) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Buka GuruBahasa</a>
        @elseif($artifact['type'] === 'expert_validation')
            <a href="{{ route('litsynth.validation.manage', [$artifact['project'], $artifact['record']->blueprint, 'version' => $artifact['record']->version_number]) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Buka validasi pakar</a>
        @elseif($artifact['type'] === 'instrument_blueprint')
            <a href="{{ route('litsynth.validation.manage', [$artifact['project'], $artifact['record']]) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Buka blueprint</a>
        @elseif(in_array($artifact['type'], ['writewise_assignment','writewise_feedback']))
            <a href="{{ route('assignments.show', $artifact['type'] === 'writewise_feedback' ? $artifact['record']->revision->submission->assignment : $artifact['record']) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Buka WriteWise</a>
        @endif
    </div>
</div>
@endsection
