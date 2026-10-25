@extends('layouts.hub')
@section('title', 'Sunting materi · GuruBahasa')
@section('content')
<a href="{{ route('gurubahasa.show', [$project, $material]) }}" class="text-sm font-medium text-teal-800 hover:underline">← Kembali ke materi</a><h1 class="mt-3 text-3xl font-bold">Sunting draf materi</h1><div class="mt-6">@include('gurubahasa._form')</div>
@endsection
