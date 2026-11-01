@extends('layouts.hub')
@section('title', ($source->title ?: $source->original_name).' · LitSynth')
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('litsynth.index', $project) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke LitSynth</a>
        <div class="mt-3 flex flex-wrap items-start justify-between gap-4 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div>
                <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-teal-800">Sumber literatur · {{ $project->name }}</p>
                <h1 class="mt-1.5 break-words text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">{{ $source->title ?: $source->original_name }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $source->original_name }} · {{ number_format($source->file_size / 1024, 0) }} KB · {{ $source->pages->count() }} halaman diekstrak</p>
            </div>
            <a href="{{ route('litsynth.sources.download', [$project, $source]) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Unduh PDF privat</a>
        </div>
    </div>

    @if($source->status === 'processing')
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 animate-spin text-sky-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span><strong>Ekstraksi teks sedang diproses di antrean latar belakang.</strong> Muat ulang halaman beberapa saat lagi untuk meninjau hasil halaman.</span>
            </div>
            <a href="{{ url()->current() }}" class="rounded-xl bg-sky-800 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-sky-700">Muat Ulang</a>
        </div>
    @elseif($source->extraction_message)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><strong>Ekstraksi otomatis belum tersedia.</strong> {{ $source->extraction_message }} Upload tetap aman tersimpan. Lengkapi metadata dan matriks secara manual.</div>
                @if($project->archived_at === null)
                    <form method="post" action="{{ route('litsynth.sources.extract', [$project, $source]) }}">
                        @csrf
                        <button class="rounded-xl bg-amber-800 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Jalankan Ulang Ekstraksi</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="grid gap-7 xl:grid-cols-[minmax(0,1fr)_minmax(22rem,.9fr)]">
        <section class="space-y-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Teks hasil ekstraksi</h2>
                <p class="mt-1 text-sm text-slate-600">Hanya teks digital PDF. Pastikan setiap kutipan dan ringkasan sesuai dokumen.</p>
            </div>
            @if($source->status === 'processing')
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">Dokumen sedang diekstrak oleh worker antrean. Teks per halaman akan muncul di sini setelah proses selesai.</div>
            @elseif($source->pages->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">Belum ada teks halaman. Setelah pdftotext tersedia, jalankan ulang ekstraksi otomatis; untuk saat ini gunakan PDF privat sebagai referensi manual.</div>
            @else
                <div class="space-y-3">
                    @foreach($source->pages as $page)
                        <details class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-2xs" @if($loop->first) open @endif>
                            <summary class="cursor-pointer px-5 py-3.5 text-sm font-bold text-slate-900 hover:bg-slate-50">Halaman {{ $page->page_number }}</summary>
                            <pre class="max-h-[32rem] overflow-auto border-t border-slate-100 bg-slate-50/70 p-5 font-serif text-sm sm:text-base leading-relaxed text-slate-800 whitespace-pre-wrap">{{ $page->extracted_text }}</pre>
                        </details>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Metadata dan matriks</h2>
                <p class="mt-1 text-sm text-slate-600">Catat temuan dan nomor halaman bukti agar dapat ditelusuri.</p>
            </div>

            <form method="post" action="{{ route('litsynth.sources.update', [$project, $source]) }}" class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                <label class="block text-sm font-semibold text-slate-700">
                    Judul
                    <input name="title" required maxlength="500" value="{{ old('title', $source->title) }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                    @error('title')<span class="text-red-700">{{ $message }}</span>@enderror
                </label>
                <div class="grid gap-3 sm:grid-cols-[1fr_8rem]">
                    <label class="block text-sm font-semibold text-slate-700">
                        Penulis
                        <input name="authors" value="{{ old('authors', $source->authors) }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Tahun
                        <input type="number" min="1000" max="{{ now()->year + 1 }}" name="publication_year" value="{{ old('publication_year', $source->publication_year) }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                    </label>
                </div>
                <label class="block text-sm font-semibold text-slate-700">
                    Abstrak
                    <textarea name="abstract" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('abstract', $source->abstract) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Pertanyaan/tujuan penelitian
                    <textarea name="research_question" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('research_question', $source->matrix?->research_question) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Metodologi
                    <textarea name="methodology" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('methodology', $source->matrix?->methodology) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Partisipan dan konteks
                    <textarea name="participants_context" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('participants_context', $source->matrix?->participants_context) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Temuan utama
                    <textarea name="key_findings" rows="4" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('key_findings', $source->matrix?->key_findings) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Keterbatasan
                    <textarea name="limitations" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('limitations', $source->matrix?->limitations) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    Relevansi terhadap proyek
                    <textarea name="relevance_notes" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('relevance_notes', $source->matrix?->relevance_notes) }}</textarea>
                </label>
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-700">Halaman bukti <span class="font-normal text-slate-500">(isi nomor halaman hasil ekstraksi)</span></legend>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        @foreach(['research_question'=>'Tujuan','methodology'=>'Metode','participants_context'=>'Partisipan','key_findings'=>'Temuan','limitations'=>'Keterbatasan'] as $field=>$label)
                            <label class="text-xs font-medium text-slate-600">
                                {{ $label }}
                                <input type="number" min="1" max="{{ max(1, $source->pages->count()) }}" name="evidence_pages[{{ $field }}]" value="{{ old('evidence_pages.'.$field, $source->matrix?->evidence_pages[$field] ?? '') }}" @disabled($source->pages->isEmpty()) class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            </label>
                        @endforeach
                    </div>
                    @error('evidence_pages.*')
                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </fieldset>
                <label class="block text-sm font-semibold text-slate-700">
                    Catatan tambahan
                    <textarea name="notes" rows="2" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed">{{ old('notes', $source->notes) }}</textarea>
                </label>
                <button class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white hover:bg-slate-800 transition">Simpan metadata & matriks</button>
            </form>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm shadow-2xs">
                <p><strong>Status:</strong> {{ ['verified'=>'Terverifikasi','needs_review'=>'Perlu review','extraction_unavailable'=>'Ekstraksi manual','uploaded'=>'Diunggah','processing'=>'Diproses'][$source->status] ?? $source->status }}</p>
                @if($source->verified_at)
                    <p class="mt-1 text-slate-600">Diverifikasi {{ $source->verified_at->format('d M Y H:i') }}</p>
                @endif
                @if($project->archived_at === null)
                    <form method="post" action="{{ route('litsynth.sources.verify', [$project, $source]) }}" class="mt-3">
                        @csrf
                        <button class="rounded-xl bg-teal-800 px-4 py-2 text-sm font-bold text-white hover:bg-teal-700 transition">Tandai terverifikasi</button>
                    </form>
                @endif
            </div>

            @if($project->archived_at === null)
                <form method="post" action="{{ route('litsynth.sources.destroy', [$project, $source]) }}" onsubmit="return confirm('Hapus PDF privat dan seluruh data matriks sumber ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-sm font-semibold text-red-700 hover:underline">Hapus sumber PDF</button>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
