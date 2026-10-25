@extends('layouts.hub')
@section('title', 'Buat materi · GuruBahasa')
@section('content')
<a href="{{ route('gurubahasa.index', $project) }}" class="text-sm font-medium text-teal-800 hover:underline">← GuruBahasa</a><h1 class="mt-3 text-3xl font-bold">Buat draf materi</h1><p class="mt-2 text-slate-600">Proyek: {{ $project->name }}</p><div class="mt-6">@include('gurubahasa._form')</div>
@endsection
