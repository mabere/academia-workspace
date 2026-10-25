@extends('layouts.hub')
@section('title', 'Review Protokol Korpus · ELT Academic Hub')
@section('content')
<header><p class="text-sm font-medium text-violet-800">Superadmin · gerbang internal aplikasi</p><h1 class="mt-1 text-3xl font-bold">Review protokol CorpusLab</h1><p class="mt-2 max-w-3xl text-slate-600">Periksa kelengkapan metadata dan rujukan etik. Keputusan di halaman ini tidak menggantikan keputusan komite etik institusi.</p></header>
@if($corpora->isEmpty())
    <div class="mt-6 rounded-xl border border-dashed bg-white p-8 text-slate-600">Tidak ada protokol menunggu review.</div>
@else
    <div class="mt-6 space-y-3">@foreach($corpora as $corpus)<article class="rounded-xl border bg-white p-5"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-sm text-slate-500">{{ $corpus->project->name }} · diajukan pemilik {{ $corpus->creator->name }}</p><h2 class="mt-1 text-lg font-bold">{{ $corpus->title }}</h2><p class="mt-1 text-sm text-slate-600">{{ $corpus->language }} · {{ $corpus->ethics_authority }} · {{ $corpus->ethics_reference }} · retensi sampai {{ $corpus->retention_until->format('d M Y') }}</p></div><a href="{{ route('corpuslab.index', $corpus->project) }}" class="rounded-lg bg-violet-800 px-4 py-2 text-sm font-semibold text-white">Periksa protokol</a></div></article>@endforeach</div>
    <div class="mt-5">{{ $corpora->links() }}</div>
@endif
@endsection
