@extends('layouts.hub')
@section('title', 'GuruBahasa · '.$project->name)
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke proyek {{ $project->name }}</a>
        <div class="relative mt-3 overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-violet-700 via-violet-500 to-teal-500"></div>
            <div class="flex flex-wrap items-end justify-between gap-4 pt-1">
                <div>
                    <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-teal-800">GuruBahasa · Pengabdian</p>
                    <h1 class="mt-1.5 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Studio materi bahasa</h1>
                    <p class="mt-2 max-w-3xl text-sm sm:text-base text-slate-600 leading-relaxed">Rancang teks, glosarium, soal, worksheet, aktivitas, dan rubrik untuk peserta komunitas.</p>
                </div>
                @if($project->archived_at === null)
                    <a href="{{ route('gurubahasa.create', $project) }}" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">Buat materi</a>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
        <strong>AI belum aktif.</strong> Studio menyimpan draf dan kurasi manual. Konten tidak dikirim ke provider; target CEFR ditampilkan sebagai perkiraan, bukan sertifikasi.
    </div>

    <section class="space-y-4">
        <h2 class="text-xl font-bold text-slate-900">Materi dalam proyek</h2>
        @if($materials->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-600">Belum ada materi GuruBahasa pada proyek ini.</div>
        @else
            <div class="space-y-3">
                @foreach($materials as $material)
                    <a href="{{ route('gurubahasa.show', [$project, $material]) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs transition hover:border-teal-500 hover:shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $material->title }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $material->topic }} · target {{ $material->cefr_level }} (perkiraan) · {{ $material->creator->name }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $material->status === 'published' ? 'bg-emerald-100 text-emerald-800' : ($material->status === 'curated' ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ ['draft'=>'Draf','curated'=>'Terkurasi','published'=>'Siap dibagikan'][$material->status] ?? $material->status }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
            <div>{{ $materials->links() }}</div>
        @endif
    </section>
</div>
@endsection
