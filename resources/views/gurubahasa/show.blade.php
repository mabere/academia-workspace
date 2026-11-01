@extends('layouts.hub')
@section('title', $material->title.' · GuruBahasa')
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('gurubahasa.index', $project) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke GuruBahasa</a>
        <div class="relative mt-3 overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-violet-700 via-violet-500 to-teal-500"></div>
            <div class="flex flex-wrap items-start justify-between gap-4 pt-1">
                <div>
                    <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-teal-800">{{ $project->name }} · target CEFR {{ $material->cefr_level }} (perkiraan)</p>
                    <h1 class="mt-1.5 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">{{ $material->title }}</h1>
                    <p class="mt-2 text-sm sm:text-base text-slate-600">Topik: {{ $material->topic }}</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-slate-700">
                    {{ ['draft'=>'Draf','curated'=>'Terkurasi','published'=>'Siap dibagikan'][$material->status] ?? $material->status }}
                </span>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
        Materi ini disusun dan dikurasi secara manual. Tingkat CEFR adalah target perkiraan pengajar dan tidak menyatakan sertifikasi atau tingkat aktual peserta. AI tetap nonaktif.
    </div>

    <section class="grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Konteks peserta</h2>
            <p class="mt-2 whitespace-pre-wrap text-sm sm:text-base leading-relaxed text-slate-700">{{ $material->participant_context }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Tujuan pembelajaran</h2>
            <p class="mt-2 whitespace-pre-wrap text-sm sm:text-base leading-relaxed text-slate-700">{{ $material->learning_objectives ?: 'Belum diisi.' }}</p>
        </div>
    </section>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Teks/topik sumber</h2>
            <p class="mt-2 whitespace-pre-wrap font-serif text-sm sm:text-base leading-relaxed text-slate-700">{{ $material->source_text ?: 'Belum diisi.' }}</p>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Draf teks pembelajaran</h2>
            <p class="mt-2 whitespace-pre-wrap font-serif text-sm sm:text-base leading-relaxed text-slate-700">{{ $material->draft_text ?: 'Belum diisi.' }}</p>
        </section>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Glosarium</h2>
            @if($material->glossary)
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-2 pr-3">Istilah</th>
                                <th class="py-2 pr-3">Arti</th>
                                <th class="py-2">Contoh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($material->glossary as $row)
                                <tr>
                                    <td class="py-2.5 pr-3 font-semibold text-slate-900">{{ $row[0] }}</td>
                                    <td class="py-2.5 pr-3 text-slate-700">{{ $row[1] }}</td>
                                    <td class="py-2.5 text-slate-600">{{ $row[2] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-2 text-sm text-slate-500">Belum diisi.</p>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Soal dan rambu jawaban</h2>
            @if($material->questions)
                <ol class="mt-3 list-decimal space-y-3 pl-5 text-sm leading-relaxed text-slate-800">
                    @foreach($material->questions as $row)
                        <li>
                            {{ $row[0] }}
                            <p class="mt-1 text-slate-600">Rambu: {{ $row[1] }}</p>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="mt-2 text-sm text-slate-500">Belum diisi.</p>
            @endif
        </section>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Worksheet</h2>
            <p class="mt-2 whitespace-pre-wrap text-sm sm:text-base leading-relaxed text-slate-700">{{ $material->worksheet ?: 'Belum diisi.' }}</p>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Aktivitas</h2>
            @if($material->activities)
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-slate-800">
                    @foreach($material->activities as $activity)
                        <li>{{ $activity }}</li>
                    @endforeach
                </ol>
            @else
                <p class="mt-2 text-sm text-slate-500">Belum diisi.</p>
            @endif
        </section>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
        <h2 class="text-base font-bold text-slate-900">Rubrik</h2>
        @if($material->rubric)
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="py-2 pr-3">Kriteria</th>
                            <th class="py-2 pr-3">Awal</th>
                            <th class="py-2 pr-3">Berkembang</th>
                            <th class="py-2">Mahir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($material->rubric as $row)
                            <tr>
                                <td class="py-2.5 pr-3 font-semibold text-slate-900">{{ $row[0] }}</td>
                                <td class="py-2.5 pr-3 text-slate-700">{{ $row[1] }}</td>
                                <td class="py-2.5 pr-3 text-slate-700">{{ $row[2] }}</td>
                                <td class="py-2.5 text-slate-700">{{ $row[3] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mt-2 text-sm text-slate-500">Belum diisi.</p>
        @endif
    </section>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('gurubahasa.export', [$project, $material]) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Ekspor HTML</a>
        @if($project->archived_at === null)
            <a href="{{ route('gurubahasa.edit', [$project, $material]) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Sunting</a>
            @if($material->status === 'draft')
                <form method="post" action="{{ route('gurubahasa.curate', [$project, $material]) }}" onsubmit="return confirm('Tandai materi ini sudah ditinjau dan dikurasi?')">
                    @csrf
                    <button class="rounded-xl bg-teal-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-700 transition">Tandai terkurasi</button>
                </form>
            @endif
            @if($material->status === 'curated')
                <form method="post" action="{{ route('gurubahasa.publish', [$project, $material]) }}" onsubmit="return confirm('Tandai materi siap dibagikan kepada peserta?')">
                    @csrf
                    <button class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-800 transition">Siap dibagikan</button>
                </form>
            @endif
            <form method="post" action="{{ route('gurubahasa.destroy', [$project, $material]) }}" onsubmit="return confirm('Hapus materi ini?')">
                @csrf
                @method('DELETE')
                <button class="rounded-xl px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50 transition">Hapus</button>
            </form>
        @endif
    </div>

    @if($material->curated_at)
        <p class="text-xs text-slate-500">Dikurasi oleh pengguna proyek pada {{ $material->curated_at->format('d M Y H:i') }}.</p>
    @endif
</div>
@endsection
