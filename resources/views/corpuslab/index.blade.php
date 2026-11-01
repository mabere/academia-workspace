@extends('layouts.hub')
@section('title', 'IndoEFL CorpusLab · '.$project->name)
@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-teal-800 hover:underline">← Kembali ke proyek</a>
        <header class="relative mt-3 overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-cyan-700 via-cyan-500 to-teal-500"></div>
            <div class="flex flex-wrap items-end justify-between gap-4 pt-1">
                <div>
                    <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-cyan-800">Penelitian · {{ $project->name }}</p>
                    <h1 class="mt-1.5 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">IndoEFL CorpusLab</h1>
                    <p class="mt-2 max-w-3xl text-sm sm:text-base text-slate-600 leading-relaxed">Kelola protokol, entri teks yang telah dianonimkan, anotasi, pencarian konteks (KWIC), dan ekspor yang berizin.</p>
                </div>
                @if($corpus && $corpus->isCollecting() && ! $project->archived_at)
                    <a href="{{ route('corpuslab.export', [$project, $corpus]) }}" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">Ekspor CSV</a>
                @endif
            </div>
        </header>
    </div>

    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-relaxed text-amber-950">
        <strong>Privasi dan batas modul:</strong> gunakan hanya data yang tercakup dalam persetujuan etik dan consent partisipan. Hilangkan nama, institusi, lokasi rinci, dan petunjuk identitas sebelum dikirim. Sistem membantu menyamarkan pola email, URL, dan nomor yang menyerupai telepon; penyamaran ini bukan jaminan anonimisasi. Teks tidak dikirim ke AI atau layanan eksternal.
    </section>

    @if(! $corpus)
        @if($canManageProtocol)
            <section class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900">Daftarkan protokol korpus</h2>
                <p class="mt-1 mb-5 text-sm text-slate-600">Hanya pemilik proyek dapat mengatur protokol. Setelah diajukan, superadmin memeriksa metadata etik sebelum pengumpulan dibuka.</p>
                @include('corpuslab._protocol-form', ['update' => false, 'corpus' => null])
            </section>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-600">Belum ada protokol korpus. Pemilik proyek dapat menyiapkannya setelah persetujuan etik tersedia.</div>
        @endif
    @else
        @php($statusLabels = ['draft' => 'Draf protokol', 'pending_review' => 'Menunggu review', 'approved' => 'Disetujui', 'rejected' => 'Perlu perbaikan', 'expired' => 'Retensi berakhir', 'closed' => 'Ditutup'])
        <section class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <span class="inline-flex rounded-full bg-cyan-50 border border-cyan-200 px-3 py-1 text-xs font-semibold text-cyan-900">{{ $statusLabels[$corpus->status] ?? $corpus->status }}</span>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">{{ $corpus->title }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $corpus->language }} · rujukan etik {{ $corpus->ethics_reference }} · retensi sampai {{ $corpus->retention_until->format('d M Y') }}</p>
                </div>
                @if($corpus->status === 'approved')
                    <p class="text-sm text-slate-500">Disetujui {{ $corpus->reviewed_at?->format('d M Y') }} oleh {{ $corpus->reviewer?->name ?? 'superadmin' }}</p>
                @endif
            </div>

            <dl class="mt-5 grid gap-x-6 gap-y-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Tujuan penelitian</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $corpus->research_purpose }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Komite / otoritas etik</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $corpus->ethics_authority }} · {{ $corpus->ethics_approved_on->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Populasi dan metode</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $corpus->participant_population }}<br>{{ $corpus->sampling_method }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Consent dan anonimisasi</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $corpus->consent_process }}<br><span class="font-semibold">Anonimisasi:</span> {{ $corpus->anonymization_plan }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Perlindungan dan penggunaan</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $corpus->privacy_plan }}</dd>
                </div>
            </dl>

            @if($corpus->review_notes)
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
                    <strong>Catatan review:</strong>
                    <p class="mt-1 whitespace-pre-line leading-relaxed">{{ $corpus->review_notes }}</p>
                </div>
            @endif

            @if($canManageProtocol && in_array($corpus->status, ['draft', 'rejected'], true))
                <details class="mt-5 border-t border-slate-100 pt-4">
                    <summary class="cursor-pointer font-bold text-slate-900">Ubah protokol</summary>
                    <div class="mt-4">@include('corpuslab._protocol-form', ['update' => true, 'corpus' => $corpus])</div>
                </details>
                <form method="post" action="{{ route('corpuslab.protocol.submit', [$project, $corpus]) }}" class="mt-4">
                    @csrf
                    <button class="rounded-xl bg-cyan-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-cyan-700 transition">Ajukan review etik</button>
                </form>
            @endif

            @if($canReview)
                <form method="post" action="{{ route('corpuslab.protocol.decision', [$project, $corpus]) }}" class="mt-5 space-y-3 rounded-2xl border border-violet-200 bg-violet-50 p-5">
                    @csrf
                    <label for="review_notes" class="block text-sm font-semibold text-slate-800">
                        Catatan reviewer <span class="font-normal text-slate-500">(wajib jika ditolak)</span>
                        <textarea id="review_notes" name="review_notes" maxlength="4000" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm leading-relaxed"></textarea>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="ethics_confirmed" value="1" required class="mt-1 rounded border-slate-300">
                        <span>Saya mengonfirmasi rujukan persetujuan komite etik yang tercantum telah diperiksa. Review ini mencatat gerbang aplikasi dan tidak menggantikan tanggung jawab institusi.</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button name="decision" value="approved" class="rounded-xl bg-emerald-800 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 transition">Setujui protokol</button>
                        <button name="decision" value="rejected" formnovalidate class="rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-bold text-red-800 hover:bg-red-50 transition">Kembalikan untuk perbaikan</button>
                    </div>
                    @error('review_notes')<span class="block text-sm text-red-700">{{ $message }}</span>@enderror
                </form>
            @endif
        </section>

        @if($corpus->status === 'approved' && ! $corpus->isCollecting())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-900">
                <h2 class="font-bold">Masa retensi berakhir</h2>
                <p class="mt-1 text-sm">Pengumpulan dan ekspor terkunci. Pembersihan terjadwal menghapus teks, kode partisipan, dan anotasi setelah tanggal retensi.</p>
            </div>
        @elseif($corpus->isCollecting())
            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @foreach([['Entri', $stats['entries']], ['Menunggu kurasi', $stats['pending']], ['Disertakan', $stats['included']], ['Dikecualikan', $stats['excluded']], ['Jumlah kata', number_format($stats['words'])]] as [$label, $value])
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $value }}</p>
                    </div>
                @endforeach
            </section>

            @if($canEditEntries)
                <section class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                    <h2 class="text-xl font-bold text-slate-900">Catat teks yang sudah dianonimkan</h2>
                    <p class="mt-1 text-sm text-slate-600">Jangan tempel teks yang masih memuat identitas. Teks mentah tidak disimpan sebagai versi terpisah. Jika consent ditarik, hapus entri dan anotasinya.</p>
                    <form method="post" action="{{ route('corpuslab.entries.store', [$project, $corpus]) }}" class="mt-5 space-y-4">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-3">
                            <label for="participant_code" class="block text-sm font-semibold text-slate-700">
                                Kode partisipan samaran
                                <input id="participant_code" name="participant_code" required maxlength="64" pattern="[A-Za-z0-9_-]+" value="{{ old('participant_code') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                                <span class="mt-1 block text-xs font-normal text-slate-500">Tanpa nama, email, atau nomor identitas.</span>
                                @error('participant_code')<span class="text-red-700">{{ $message }}</span>@enderror
                            </label>
                            <label for="consent_reference" class="block text-sm font-semibold text-slate-700">
                                Kode catatan consent (opsional)
                                <input id="consent_reference" name="consent_reference" maxlength="120" value="{{ old('consent_reference') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                                <span class="mt-1 block text-xs font-normal text-slate-500">Jangan unggah formulir bertanda tangan.</span>
                            </label>
                            <label for="collected_on" class="block text-sm font-semibold text-slate-700">
                                Tanggal pengumpulan
                                <input id="collected_on" name="collected_on" type="date" required min="{{ $corpus->ethics_approved_on->toDateString() }}" max="{{ today()->toDateString() }}" value="{{ old('collected_on') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                                @error('collected_on')<span class="text-red-700">{{ $message }}</span>@enderror
                            </label>
                        </div>
                        <label for="corpus-text" class="block text-sm font-semibold text-slate-700">
                            Teks terdeidentifikasi
                            <textarea id="corpus-text" name="text" required maxlength="30000" rows="8" class="mt-1.5 w-full rounded-xl border-slate-300 font-serif text-sm sm:text-base leading-relaxed" autocomplete="off" spellcheck="false"></textarea>
                            <span class="mt-1 block text-xs font-normal text-slate-500">Batas 30.000 karakter. Sistem hanya membantu menghapus pola tertentu, bukan mengidentifikasi semua nama atau konteks sensitif.</span>
                            @error('text')<span class="text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <label class="flex items-start gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="consent_confirmed" value="1" required class="mt-1 rounded border-slate-300 text-cyan-800">
                            <span>Saya mengonfirmasi consent partisipan telah diperoleh dan dicatat sesuai protokol yang disetujui.</span>
                        </label>
                        @error('consent_confirmed')<span class="block text-sm text-red-700">{{ $message }}</span>@enderror
                        <button class="rounded-xl bg-cyan-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-cyan-700 transition">Simpan versi bersih</button>
                    </form>
                </section>
            @endif

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Entri korpus dan anotasi</h2>
                        <p class="mt-1 text-sm text-slate-600">Pencarian teks menampilkan KWIC pada hasil yang cocok.</p>
                    </div>
                    <form method="get" action="{{ route('corpuslab.index', $project) }}" class="flex gap-2">
                        <label class="sr-only" for="corpus-search">Cari teks</label>
                        <input id="corpus-search" type="search" name="q" maxlength="100" value="{{ $query }}" placeholder="Cari istilah…" class="rounded-xl border-slate-300 text-sm">
                        <button class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cari</button>
                    </form>
                </div>

                @if($entries->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-7 text-sm text-slate-600">Belum ada entri yang sesuai.</div>
                @else
                    <div class="space-y-4">
                        @foreach($entries as $entry)
                            <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 font-mono text-xs font-semibold text-slate-800">{{ $entry->participant_code }}</span>
                                        <span class="rounded-full bg-cyan-50 border border-cyan-200 px-3 py-1 text-xs font-semibold text-cyan-900">{{ $entry->status }}</span>
                                        <span class="text-xs text-slate-500">{{ $entry->collected_on->format('d M Y') }} · {{ number_format($entry->word_count) }} kata · {{ $entry->annotations_count }} anotasi</span>
                                    </div>
                                    @if($canEditEntries)
                                        <div class="flex flex-wrap gap-2">
                                            <form method="post" action="{{ route('corpuslab.entries.status', [$project, $corpus, $entry]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $entry->status === 'included' ? 'excluded' : 'included' }}">
                                                <button class="rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">{{ $entry->status === 'included' ? 'Kecualikan' : 'Sertakan' }}</button>
                                            </form>
                                            <form method="post" action="{{ route('corpuslab.entries.destroy', [$project, $corpus, $entry]) }}" class="flex gap-1.5">
                                                @csrf
                                                @method('DELETE')
                                                <select name="reason" aria-label="Alasan hapus entri" class="rounded-xl border-slate-300 py-1 text-xs">
                                                    <option value="participant_withdrew">Consent ditarik</option>
                                                    <option value="entered_in_error">Salah input</option>
                                                </select>
                                                <button class="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50 transition" onclick="return confirm('Hapus teks dan seluruh anotasi entri ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>

                                <p class="mt-4 whitespace-pre-wrap break-words font-serif text-sm sm:text-base leading-relaxed text-slate-800">{{ $entry->kwic ?? $entry->anonymized_text }}</p>

                                @if($entry->consent_reference)
                                    <p class="mt-2 text-xs text-slate-500">Rujukan consent: {{ $entry->consent_reference }}</p>
                                @endif

                                @if($entry->annotations->isNotEmpty())
                                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-3">
                                        @foreach($entry->annotations as $annotation)
                                            <div class="rounded-xl bg-slate-50 p-3 text-sm">
                                                <strong>{{ $annotation->label }}</strong>
                                                @if($annotation->span_text)
                                                    <span class="ml-1 italic">“{{ $annotation->span_text }}”</span>
                                                @endif
                                                @if($annotation->comment)
                                                    <p class="mt-1 text-slate-600">{{ $annotation->comment }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($canEditEntries)
                                    <details class="mt-4 border-t border-slate-100 pt-3">
                                        <summary class="cursor-pointer text-sm font-semibold text-teal-900">Tambah anotasi</summary>
                                        <form method="post" action="{{ route('corpuslab.annotations.store', [$project, $corpus, $entry]) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                                            @csrf
                                            <label class="text-sm font-medium">
                                                Label / kategori
                                                <input name="label" required maxlength="100" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                                            </label>
                                            <label class="text-sm font-medium">
                                                Rentang teks bersih (opsional)
                                                <input name="span_text" maxlength="2000" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                                            </label>
                                            <label class="text-sm font-medium sm:col-span-2">
                                                Catatan analisis
                                                <textarea name="comment" maxlength="4000" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-sm"></textarea>
                                            </label>
                                            <button class="w-fit rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Simpan anotasi</button>
                                        </form>
                                    </details>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-5">{{ $entries->links() }}</div>
                @endif
            </section>
        @endif
    @endif
</div>
@endsection
