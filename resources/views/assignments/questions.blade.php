@extends('layouts.hub')

@section('title', 'Kelola Soal: ' . $assignment->title)

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    {{-- Breadcrumb & Navigasi Kembali --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500">
        <a href="{{ route('courses.index') }}" class="hover:text-slate-800">Mata Kuliah</a>
        <span>&rsaquo;</span>
        @if($assignment->course)
            <a href="{{ route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']) }}" class="hover:text-slate-800">
                {{ $assignment->course->code }} - {{ $assignment->course->name }}
            </a>
            <span>&rsaquo;</span>
        @endif
        <span class="font-semibold text-slate-700">Kelola Butir Soal</span>
    </nav>

    {{-- Header Kartu Tugas --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $assignment->typeBadgeColor() }}">
                        {{ $assignment->typeLabel() }}
                    </span>
                    @if($assignment->time_limit_minutes)
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Durasi: {{ $assignment->time_limit_minutes }} Menit
                        </span>
                    @endif
                </div>
                <h1 class="mt-2 text-xl font-bold text-slate-900">{{ $assignment->title }}</h1>
                <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $assignment->instructions }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('assignments.exam.take', $assignment) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-xs transition">
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Pratinjau Ujian Mahasiswa &rarr;
                </a>
                @if($assignment->course)
                    <a href="{{ route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition shadow-xs">
                        &larr; Kembali ke Kelas
                    </a>
                @endif
            </div>
        </div>

        {{-- Panel Pratinjau Audio Stimulus --}}
        @if(!empty($assignment->config_json['audio_file_path']))
            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50/60 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-700 text-white font-bold text-xs">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-bold text-amber-950">Berkas Audio Stimulus Perkuliahan Terpasang</p>
                            <p class="text-[11px] text-amber-800">
                                Batas pemutaran mahasiswa: <strong>{{ ($assignment->config_json['audio_max_plays'] ?? 2) > 0 ? ($assignment->config_json['audio_max_plays'] ?? 2) . ' kali' : 'Bebas' }}</strong>
                            </p>
                        </div>
                    </div>
                    <audio controls class="h-8 max-w-xs" preload="metadata">
                        <source src="{{ route('assignments.audio-stream', $assignment) }}">
                        Browser Anda tidak mendukung elemen pemutar audio.
                    </audio>
                </div>
            </div>
        @endif

        {{-- Panel Pratinjau Naskah Bacaan --}}
        @if(!empty($assignment->config_json['reading_passage']))
            <details class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <summary class="cursor-pointer text-xs font-bold text-slate-800 hover:text-teal-800">
                    Lihat Naskah Stimulus Bacaan / Teks Materi &darr;
                </summary>
                <div class="mt-3 max-h-60 overflow-y-auto whitespace-pre-line rounded-lg bg-white p-3 font-serif text-xs leading-relaxed text-slate-700 border border-slate-200">
                    {{ $assignment->config_json['reading_passage'] }}
                </div>
            </details>
        @endif
    </div>

    {{-- Form Tambah Butir Soal Baru --}}
    <div class="rounded-2xl border border-teal-200 bg-white p-6 shadow-sm" x-data="{ questionType: 'multiple_choice' }">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">+ Tambah Butir Soal Baru</h2>
                <p class="text-xs text-slate-500">Susun pertanyaan pilihan ganda, isian singkat, atau esai analitik.</p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" @click="questionType = 'multiple_choice'"
                    :class="questionType === 'multiple_choice' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                    Pilihan Ganda
                </button>
                <button type="button" @click="questionType = 'short_answer'"
                    :class="questionType === 'short_answer' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                    Isian Singkat
                </button>
                <button type="button" @click="questionType = 'essay'"
                    :class="questionType === 'essay' ? 'bg-teal-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                    Esai Analitis
                </button>
            </div>
        </div>

        <form method="post" action="{{ route('assignments.questions.store', $assignment) }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="question_type" :value="questionType">

            <div class="grid gap-4 sm:grid-cols-4">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700">Teks Pertanyaan / Butir Soal *</label>
                    <textarea name="prompt" required rows="3" placeholder="Tuliskan pertanyaan ujian di sini..."
                        class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700 shadow-xs"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Poin Soal *</label>
                    <input type="number" name="points" value="10" min="1" max="100" required
                        class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700 shadow-xs">
                    <p class="mt-1 text-[11px] text-slate-500">Bobot butir relatif terhadap total poin.</p>
                </div>
            </div>

            {{-- Kolom Opsi Pilihan Ganda (A, B, C, D) --}}
            <div x-show="questionType === 'multiple_choice'" class="space-y-3 rounded-xl bg-slate-50 p-4 border border-slate-200">
                <p class="text-xs font-bold text-slate-800">Pilihan Jawaban & Kunci Jawaban Benar:</p>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <input type="radio" name="answer_key" value="A" required class="text-teal-700 focus:ring-teal-700">
                            A:
                        </label>
                        <input type="text" name="options[A]" placeholder="Opsi jawaban A" required
                            class="w-full rounded-lg border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <input type="radio" name="answer_key" value="B" required class="text-teal-700 focus:ring-teal-700">
                            B:
                        </label>
                        <input type="text" name="options[B]" placeholder="Opsi jawaban B" required
                            class="w-full rounded-lg border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <input type="radio" name="answer_key" value="C" required class="text-teal-700 focus:ring-teal-700">
                            C:
                        </label>
                        <input type="text" name="options[C]" placeholder="Opsi jawaban C" required
                            class="w-full rounded-lg border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <input type="radio" name="answer_key" value="D" required class="text-teal-700 focus:ring-teal-700">
                            D:
                        </label>
                        <input type="text" name="options[D]" placeholder="Opsi jawaban D" required
                            class="w-full rounded-lg border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700">
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 italic">* Pilih salah satu tombol radio di atas sebagai kunci jawaban benar yang dinilai otomatis.</p>
            </div>

            {{-- Kolom Kunci Isian Singkat --}}
            <div x-show="questionType === 'short_answer'" class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                <label class="block text-xs font-semibold text-slate-700">Kata Kunci / Jawaban Benar (Short Answer)</label>
                <input type="text" name="answer_key" placeholder="Contoh: LAD atau Language Acquisition Device"
                    class="mt-1 w-full rounded-lg border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700">
                <p class="mt-1 text-[11px] text-slate-500">Sistem akan mencocokkan kata kunci ini (tidak sensitif huruf besar/kecil).</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Penjelasan / Pembahasan Soal (Opsional)</label>
                <input type="text" name="explanation" placeholder="Penjelasan rasional atau referensi letak jawaban pada rekaman audio..."
                    class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-teal-700 focus:ring-teal-700 shadow-xs">
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2.5 text-xs font-bold text-white hover:bg-teal-700 shadow transition">
                    + Simpan Butir Soal
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar Soal yang Telah Disusun --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">
                    Daftar Butir Pertanyaan ({{ $assignment->questions->count() }} Soal)
                </h3>
                <p class="text-xs text-slate-500">
                    Total Bobot Poin: {{ $assignment->questions->sum('points') }} Poin
                </p>
            </div>
        </div>

        @if($assignment->questions->isEmpty())
            <div class="py-10 text-center">
                <p class="text-xs font-semibold text-slate-600">Belum ada butir pertanyaan yang ditambahkan.</p>
                <p class="mt-1 text-[11px] text-slate-400">Gunakan formulir di atas untuk mulai menyusun butir soal ujian.</p>
            </div>
        @else
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($assignment->questions as $index => $q)
                    <div class="py-4 first:pt-0 last:pb-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1.5 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-900 text-[11px] font-bold text-white">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700 uppercase">
                                        {{ $q->question_type }}
                                    </span>
                                    <span class="text-xs font-bold text-teal-800">
                                        {{ $q->points }} Poin
                                    </span>
                                </div>
                                <p class="text-xs font-medium text-slate-900 leading-relaxed">{{ $q->prompt }}</p>

                                @if($q->isMultipleChoice() && !empty($q->options_json))
                                    <div class="grid gap-2 sm:grid-cols-2 pt-1 text-xs">
                                        @foreach($q->options_json as $opt)
                                            @php($isKey = ($q->answer_key !== null && strtoupper($opt['key']) === strtoupper($q->answer_key)))
                                            <div class="rounded-lg p-2 {{ $isKey ? 'bg-emerald-50 text-emerald-900 font-semibold border border-emerald-300' : 'bg-slate-50 text-slate-700 border border-slate-200' }}">
                                                <span class="font-bold">{{ $opt['key'] }}.</span> {{ $opt['text'] }}
                                                @if($isKey)
                                                    <span class="ml-1 text-[10px] text-emerald-700 font-bold">&#10003; Kunci</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($q->isShortAnswer())
                                    <div class="rounded-lg bg-emerald-50 p-2 text-xs text-emerald-900 border border-emerald-300">
                                        <strong>Kunci Jawaban:</strong> {{ $q->answer_key }}
                                    </div>
                                @endif

                                @if($q->explanation)
                                    <p class="text-[11px] text-slate-500 italic pt-1">
                                        <span class="font-semibold text-slate-600">Pembahasan:</span> {{ $q->explanation }}
                                    </p>
                                @endif
                            </div>

                            <form method="post" action="{{ route('assignments.questions.destroy', ['assignment' => $assignment, 'question' => $q]) }}"
                                onsubmit="return confirm('Hapus butir pertanyaan nomor {{ $index + 1 }} ini?');">
                                @csrf
                                @method('delete')
                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 hover:underline">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
