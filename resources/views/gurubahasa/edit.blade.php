@extends('layouts.hub')
@section('title', 'Sunting materi · GuruBahasa')
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('gurubahasa.show', [$project, $material]) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke materi</a>
        <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Sunting draf materi</h1>
    </div>
    <div>@include('gurubahasa._form')</div>
</div>
@endsection
