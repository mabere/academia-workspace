@extends('layouts.hub')
@section('title', 'Review Protokol Korpus · ELT Academic Hub')
@section('content')
<div class="space-y-6">
    <header class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
        <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-violet-800">Superadmin · gerbang internal aplikasi</p>
        <h1 class="mt-1.5 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Review protokol CorpusLab</h1>
        <p class="mt-2 max-w-3xl text-sm sm:text-base text-slate-600 leading-relaxed">Periksa kelengkapan metadata dan rujukan etik. Keputusan di halaman ini tidak menggantikan keputusan komite etik institusi.</p>
    </header>

    @if($corpora->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-600">Tidak ada protokol menunggu review.</div>
    @else
        <div class="space-y-3">
            @foreach($corpora as $corpus)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs sm:text-sm text-slate-500">{{ $corpus->project->name }} · diajukan pemilik {{ $corpus->creator->name }}</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $corpus->title }}</h2>
                            <p class="mt-1 text-sm text-slate-600">{{ $corpus->language }} · {{ $corpus->ethics_authority }} · {{ $corpus->ethics_reference }} · retensi sampai {{ $corpus->retention_until->format('d M Y') }}</p>
                        </div>
                        <a href="{{ route('corpuslab.index', $corpus->project) }}" class="rounded-xl bg-violet-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-700 transition">Periksa protokol</a>
                    </div>
                </article>
            @endforeach
        </div>
        <div>{{ $corpora->links() }}</div>
    @endif
</div>
@endsection
