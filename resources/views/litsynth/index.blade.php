@extends('layouts.hub')
@section('title', 'LitSynth · '.$project->name)
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke proyek {{ $project->name }}</a>
        <div class="relative mt-3 overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-teal-700 via-teal-500 to-emerald-500"></div>
            <div class="flex flex-wrap items-end justify-between gap-4 pt-1">
                <div>
                    <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-teal-800">LitSynth ELT · Kajian Literatur</p>
                    <h1 class="mt-1.5 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Sumber dan blueprint</h1>
                    <p class="mt-2 max-w-3xl text-sm sm:text-base text-slate-600 leading-relaxed">PDF disimpan pada disk privat dan tidak dikirim ke layanan AI. Teks yang diekstrak perlu diperiksa sebelum matriks ditandai terverifikasi.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-slate-700">{{ $project->name }}</span>
            </div>
        </div>
    </div>

    @if($project->archived_at === null)
        <section class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Tambah sumber PDF</h2>
            <p class="mt-1 text-sm text-slate-600">Maksimum {{ number_format(config('litsynth.max_pdf_kilobytes') / 1024, 0) }} MB. Dokumen pindaian belum didukung OCR.</p>
            <form method="post" enctype="multipart/form-data" action="{{ route('litsynth.sources.store', $project) }}" class="mt-4 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                @csrf
                <label class="block text-sm font-semibold text-slate-700">
                    Berkas PDF
                    <input type="file" name="pdf" accept="application/pdf,.pdf" required class="mt-1.5 block w-full rounded-xl border border-slate-300 p-2 text-sm">
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Judul (opsional)
                    <input name="title" maxlength="500" value="{{ old('title') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                </label>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">Simpan & ekstrak</button>
            </form>
            @error('pdf')
                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </section>
    @else
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Proyek diarsipkan. LitSynth hanya tersedia untuk dibaca.</div>
    @endif

    <section class="space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Matriks kajian literatur</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $sources->total() }} sumber · cari metadata, catatan matriks, dan teks halaman hasil ekstraksi.</p>
            </div>
            <form method="get" action="{{ route('litsynth.index', $project) }}" class="flex gap-2">
                <label class="sr-only" for="source-search">Cari sumber</label>
                <input id="source-search" type="search" name="q" value="{{ $query }}" maxlength="120" placeholder="Cari literatur…" class="w-56 rounded-xl border-slate-300 text-sm">
                <button class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cari</button>
                @if($query)
                    <a href="{{ route('litsynth.index', $project) }}" class="px-2 py-2 text-sm font-semibold text-teal-800 hover:underline">Hapus</a>
                @endif
            </form>
        </div>

        @if($sources->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-600">
                {{ $query ? 'Tidak ada sumber yang cocok dengan pencarian.' : 'Belum ada sumber literatur untuk proyek ini.' }}
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="divide-y divide-slate-100">
                    @foreach($sources as $source)
                        <a href="{{ route('litsynth.sources.show', [$project, $source]) }}" class="block p-5 sm:p-6 hover:bg-slate-50 transition">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="break-words text-base font-bold text-slate-900">{{ $source->title ?: $source->original_name }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $source->authors ?: 'Penulis belum dicatat' }}{{ $source->publication_year ? ' · '.$source->publication_year : '' }} · {{ $source->pages_count }} halaman diekstrak</p>
                                </div>
                                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $source->status === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($source->status === 'extraction_unavailable' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ ['verified'=>'Terverifikasi','needs_review'=>'Perlu review','extraction_unavailable'=>'Ekstraksi manual','uploaded'=>'Diunggah','processing'=>'Diproses'][$source->status] ?? $source->status }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">PDF privat · {{ number_format($source->file_size / 1024, 0) }} KB · diunggah oleh {{ $source->uploader->name }} · {{ $source->original_name }}</p>
                        </a>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 p-4">{{ $sources->links() }}</div>
            </div>
        @endif
    </section>

    <section class="space-y-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Blueprint instrumen</h2>
            <p class="mt-1 text-sm text-slate-600">Draf disusun dan ditinjau manual dengan menautkan sumber kajian proyek.</p>
        </div>

        @if($project->archived_at === null)
            <form method="post" action="{{ route('litsynth.blueprints.store', $project) }}" class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Nama instrumen
                        <input name="title" required maxlength="180" value="{{ old('title') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Tujuan
                        <textarea name="purpose" required rows="2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('purpose') }}</textarea>
                    </label>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Konstruk sasaran
                        <textarea name="target_constructs" rows="2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed"></textarea>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Konteks/responden
                        <textarea name="respondent_context" rows="2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed"></textarea>
                    </label>
                </div>
                <label class="text-sm font-semibold text-slate-700">
                    Butir awal <span class="font-normal text-slate-500">(satu butir per baris)</span>
                    <textarea name="items_text" rows="4" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed"></textarea>
                </label>
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-700">Sumber kajian pendukung</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @forelse($allSources as $source)
                            <label class="flex items-start gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="literature_source_ids[]" value="{{ $source->id }}" class="mt-1 rounded border-slate-300 text-teal-700">
                                <span>{{ $source->title ?: $source->original_name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">Tambahkan sumber literatur terlebih dahulu.</p>
                        @endforelse
                    </div>
                </fieldset>
                <div>
                    <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">Simpan draf blueprint</button>
                </div>
            </form>
        @endif

        @if($blueprints->isNotEmpty())
            <div class="space-y-4">
                @foreach($blueprints as $blueprint)
                    <details class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs" @if($blueprint->status === 'draft') open @endif>
                        <summary class="cursor-pointer font-bold text-slate-900">
                            {{ $blueprint->title }}
                            <span class="ml-2 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">{{ $blueprint->status === 'reviewed' ? 'Ditinjau' : 'Draf' }}</span>
                        </summary>
                        <form method="post" action="{{ route('litsynth.blueprints.update', [$project, $blueprint]) }}" class="mt-4 grid gap-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="text-sm font-semibold text-slate-700">
                                    Nama instrumen
                                    <input name="title" required maxlength="180" value="{{ $blueprint->title }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                                </label>
                                <label class="text-sm font-semibold text-slate-700">
                                    Tujuan
                                    <textarea name="purpose" required rows="2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ $blueprint->purpose }}</textarea>
                                </label>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="text-sm font-semibold text-slate-700">
                                    Konstruk sasaran
                                    <textarea name="target_constructs" rows="2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ $blueprint->target_constructs }}</textarea>
                                </label>
                                <label class="text-sm font-semibold text-slate-700">
                                    Konteks/responden
                                    <textarea name="respondent_context" rows="2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ $blueprint->respondent_context }}</textarea>
                                </label>
                            </div>
                            <label class="text-sm font-semibold text-slate-700">
                                Butir awal
                                <textarea name="items_text" rows="4" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ implode("\n", $blueprint->items ?? []) }}</textarea>
                            </label>
                            <fieldset>
                                <legend class="text-sm font-semibold text-slate-700">Sumber pendukung</legend>
                                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                    @foreach($allSources as $source)
                                        <label class="flex items-start gap-2 text-sm text-slate-700">
                                            <input type="checkbox" name="literature_source_ids[]" value="{{ $source->id }}" @checked(in_array($source->id, $blueprint->literature_source_ids ?? [])) class="mt-1 rounded border-slate-300 text-teal-700">
                                            <span>{{ $source->title ?: $source->original_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <div class="flex flex-wrap items-center gap-3">
                                <label class="text-sm font-semibold text-slate-700">
                                    Status
                                    <select name="status" class="ml-1 rounded-xl border-slate-300 text-sm">
                                        <option value="draft" @selected($blueprint->status === 'draft')>Draf</option>
                                        <option value="reviewed" @selected($blueprint->status === 'reviewed')>Ditinjau</option>
                                    </select>
                                </label>
                                <button class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Perbarui blueprint</button>
                            </div>
                        </form>
                    </details>
                @endforeach
            </div>
        @endif
    </section>

    @if($blueprints->isNotEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Portal validasi pakar</h2>
            <div class="mt-3 flex flex-wrap gap-3">
                @foreach($blueprints as $blueprint)
                    <a href="{{ route('litsynth.validation.manage', [$project, $blueprint]) }}" class="rounded-xl border border-teal-200 bg-teal-50/50 px-4 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-50 transition">
                        {{ $blueprint->title }} · versi {{ $blueprint->current_version }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
