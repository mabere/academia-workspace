@extends('layouts.hub')
@section('title', 'Buat materi · GuruBahasa')
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('gurubahasa.index', $project) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← GuruBahasa</a>
        <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Buat draf materi</h1>
        <p class="mt-1 text-sm text-slate-600">Proyek: {{ $project->name }}</p>
    </div>
    <div>@include('gurubahasa._form')</div>
</div>
@endsection
