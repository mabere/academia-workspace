@extends('layouts.hub')

@section('title', 'Pengerjaan Ujian: ' . $assignment->title)

@section('content')
<div class="mx-auto max-w-4xl space-y-6"
    x-data="{
        remainingSeconds: {{ $remainingSeconds ?? 0 }},
        timeLimit: {{ $assignment->time_limit_minutes ? 'true' : 'false' }},
        timerActive: {{ $submission?->started_at && !in_array($submission->status, ['submitted', 'graded']) ? 'true' : 'false' }},
        timerString: '',
        maxPlays: {{ (int) ($assignment->config_json['audio_max_plays'] ?? 2) }},
        playsLeft: {{ (int) ($playsLeft ?? ($assignment->config_json['audio_max_plays'] ?? 2)) }},
        hasCountedCurrentPlay: false,
        submitting: false,
        storageKey: 'exam_draft_{{ $assignment->id }}_{{ auth()->id() }}',
        init() {
            this.restoreDraft();
            if (this.maxPlays > 0 && this.playsLeft <= 0) {
                const audio = document.getElementById('stimulusAudio');
                if (audio) {
                    audio.controls = false;
                }
            }
            if (this.timeLimit && this.timerActive && this.remainingSeconds <= 0) {
                this.clearDraft();
                document.getElementById('examForm')?.submit();
            } else if (this.timeLimit && this.timerActive) {
                this.updateTimerString();
                const interval = setInterval(() => {
                    this.remainingSeconds--;
                    this.updateTimerString();
                    if (this.remainingSeconds <= 0) {
                        clearInterval(interval);
                        alert('Waktu ujian telah berakhir! Sistem akan mengumpulkan jawaban Anda secara otomatis.');
                        this.clearDraft();
                        document.getElementById('examForm')?.submit();
                    }
                }, 1000);
            }
        },
        updateTimerString() {
            const m = Math.floor(this.remainingSeconds / 60);
            const s = this.remainingSeconds % 60;
            this.timerString = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        },
        saveDraft() {
            const form = document.getElementById('examForm');
            if (!form) return;
            const data = {};
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                if (key !== '_token') {
                    data[key] = value;
                }
            }
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(data));
            } catch (e) {}
        },
        restoreDraft() {
            const form = document.getElementById('examForm');
            if (!form) return;
            try {
                const raw = localStorage.getItem(this.storageKey);
                if (!raw) return;
                const data = JSON.parse(raw);
                Object.entries(data).forEach(([name, val]) => {
                    const el = form.elements[name];
                    if (el) {
                        el.value = val;
                    }
                });
            } catch (e) {}
        },
        clearDraft() {
            try {
                localStorage.removeItem(this.storageKey);
            } catch (e) {}
        },
        async onAudioPlay() {
            const audio = document.getElementById('stimulusAudio');
            if (this.maxPlays > 0 && this.playsLeft <= 0 && !this.hasCountedCurrentPlay) {
                if (audio) {
                    audio.pause();
                    audio.controls = false;
                }
                return;
            }
            if (this.maxPlays > 0 && !this.hasCountedCurrentPlay) {
                this.hasCountedCurrentPlay = true;
                this.playsLeft = Math.max(0, this.playsLeft - 1);
                @if(!$isPreview)
                try {
                    const res = await fetch('{{ route('assignments.audio-play', $assignment) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok || res.status === 429) {
                        const payload = await res.json();
                        if (typeof payload.plays_left === 'number') {
                            this.playsLeft = payload.plays_left;
                        }
                        if (res.status === 429 && audio) {
                            audio.pause();
                            audio.controls = false;
                        }
                    }
                } catch (e) {}
                @endif
            }
        },
        onAudioEnded() {
            this.hasCountedCurrentPlay = false;
            if (this.maxPlays > 0 && this.playsLeft <= 0) {
                const audio = document.getElementById('stimulusAudio');
                if (audio) {
                    audio.pause();
                    audio.controls = false;
                }
            }
        }
    }">

    {{-- Header Banner & Status --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-0.5 text-xs font-semibold border {{ $assignment->typeBadgeColor() }}">
                        {{ $assignment->typeLabel() }}
                    </span>
                    @if($isPreview)
                        <span class="rounded-full bg-indigo-100 px-3 py-0.5 text-xs font-bold text-indigo-800">
                            Mode Pratinjau Dosen
                        </span>
                    @endif
                </div>
                <h1 class="mt-2.5 text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">{{ $assignment->title }}</h1>
                @if($assignment->course)
                    <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                        {{ $assignment->course->code }} · {{ $assignment->course->name }} (Dosen: {{ $assignment->course->lecturer?->name }})
                    </p>
                @endif
            </div>

            {{-- Widget Countdown Timer --}}
            @if($assignment->time_limit_minutes && $submission?->started_at && !in_array($submission->status, ['submitted', 'graded']))
                <div class="rounded-2xl bg-slate-900 px-5 py-3 text-center text-white shadow-md border border-slate-800">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Sisa Waktu</span>
                    <span class="font-mono text-2xl font-extrabold text-amber-400" x-text="timerString">--:--</span>
                </div>
            @endif
        </div>

        {{-- Petunjuk Tugas --}}
        <div class="mt-5 rounded-2xl bg-slate-50 p-4 sm:p-5 border border-slate-200 text-xs sm:text-sm text-slate-700 leading-relaxed">
            <strong class="text-slate-900 block mb-1">Petunjuk Ujian:</strong>
            <div class="whitespace-pre-wrap font-sans">{{ $assignment->instructions }}</div>
        </div>

        {{-- Sticky Audio Stimulus Player untuk Listening --}}
        @if(!empty($assignment->config_json['audio_file_path']))
            <div class="mt-5 rounded-2xl border border-amber-300 bg-amber-50/90 p-4 sm:p-5 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-700 text-white font-bold shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs sm:text-sm font-bold text-amber-950">Audio Stimulus Listening for Academic Purposes</p>
                            <p class="text-xs text-amber-900 mt-0.5">
                                Sisa kesempatan mendengarkan:
                                <strong class="text-amber-950 underline" x-text="maxPlays > 0 ? playsLeft + ' dari ' + maxPlays + ' kali' : 'Bebas'"></strong>
                            </p>
                        </div>
                    </div>

                    <div>
                        <audio id="stimulusAudio" controls controlsList="nodownload" class="h-10 w-64 sm:w-72 max-w-full"
                            preload="metadata"
                            @contextmenu.prevent
                            @play="onAudioPlay"
                            @ended="onAudioEnded">
                            <source src="{{ route('assignments.audio-stream', $assignment) }}">
                            Peramban Anda tidak mendukung elemen pemutar audio.
                        </audio>
                        <p x-show="maxPlays > 0 && playsLeft === 0" x-cloak class="mt-1 text-xs font-bold text-rose-700 text-right">
                            Batas pemutaran audio telah habis.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Stimulus Bacaan (Jika Ada) --}}
        @if(!empty($assignment->config_json['reading_passage']))
            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2.5">Teks Stimulus Bacaan / Naskah:</p>
                <div class="max-h-96 overflow-y-auto whitespace-pre-line rounded-xl bg-white p-5 sm:p-6 font-serif text-base leading-relaxed text-slate-800 border border-slate-200 shadow-2xs">
                    {{ $assignment->config_json['reading_passage'] }}
                </div>
            </div>
        @endif
    </div>

    {{-- KONDISI 1: MAHASISWA TELAH SELESAI MENGERJAKAN & SUDAH SUBMIT --}}
    @if($submission && in_array($submission->status, ['submitted', 'graded']))
        <div class="rounded-3xl border border-emerald-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 font-bold text-lg">
                    &#10003;
                </span>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Ujian Telah Berhasil Dikumpulkan</h2>
                    <p class="text-xs sm:text-sm text-slate-500">
                        Diserahkan pada {{ $submission->submitted_at?->translatedFormat('d F Y, H:i') ?? 'Baru saja' }}
                    </p>
                </div>
            </div>

            {{-- Kartu Hasil Nilai --}}
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200 text-center">
                    <span class="text-xs font-bold uppercase text-slate-500 block">Skor Pilihan Ganda</span>
                    <span class="mt-1 text-2xl font-black text-slate-900 block">
                        {{ $submission->mcq_score !== null ? number_format($submission->mcq_score, 1) : '-' }}
                    </span>
                    <span class="text-xs text-slate-400 block">Skala 0 - 100</span>
                </div>

                @if($assignment->isHybrid())
                    <div class="rounded-2xl bg-indigo-50 p-4 border border-indigo-200 text-center">
                        <span class="text-xs font-bold uppercase text-indigo-700 block">Nilai Esai (70%)</span>
                        <span class="mt-1 text-2xl font-black text-indigo-900 block">
                            {{ $submission->essay_score !== null ? number_format($submission->essay_score, 1) : 'Menunggu' }}
                        </span>
                        <span class="text-xs text-indigo-600 block">Koreksi Dosen</span>
                    </div>
                @endif

                <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-200 text-center">
                    <span class="text-xs font-bold uppercase text-emerald-800 block">Nilai Akhir Ujian</span>
                    <span class="mt-1 text-2xl font-black text-emerald-950 block">
                        {{ $submission->score !== null ? number_format($submission->score, 1) : 'Proses' }}
                    </span>
                    <span class="text-xs font-bold text-emerald-800 block">
                        Mutu: {{ $submission->letterGrade() }}
                    </span>
                </div>
            </div>

            {{-- Rincian Jawaban --}}
            <div class="mt-6 border-t border-slate-100 pt-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                    Rincian Jawaban Butir Soal Anda
                </h3>
                <div class="space-y-3">
                    @foreach($assignment->questions as $idx => $q)
                        @php($ans = $submission->answers->firstWhere('question_id', $q->id))
                        <div class="rounded-2xl border p-4 text-xs sm:text-sm {{ $ans && $ans->is_correct ? 'border-emerald-200 bg-emerald-50/40' : ($ans && $ans->is_correct === false ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200 bg-slate-50') }}">
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-semibold text-slate-900 leading-relaxed">
                                    <span class="font-bold">{{ $idx + 1 }}.</span> {{ $q->prompt }}
                                </p>
                                @if($ans && $ans->is_correct === true)
                                    <span class="rounded-lg bg-emerald-600 px-2.5 py-0.5 text-xs font-bold text-white whitespace-nowrap">
                                        Benar (+{{ $ans->score_awarded }} Poin)
                                    </span>
                                @elseif($ans && $ans->is_correct === false)
                                    <span class="rounded-lg bg-rose-600 px-2.5 py-0.5 text-xs font-bold text-white whitespace-nowrap">
                                        Salah (0 Poin)
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1.5 text-slate-700">
                                Jawaban Anda: <strong>{{ $ans->student_answer ?? 'Tidak dijawab' }}</strong>
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <div class="flex flex-wrap gap-2">
                    @if($assignment->allow_revisions && (!$assignment->due_at || $assignment->due_at->isFuture()) && !($submission?->status === 'graded' && $submission?->hasMaxGrade()))
                        <form method="post" action="{{ route('assignments.exam.start', $assignment) }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2.5 text-xs sm:text-sm font-bold text-white hover:bg-teal-700 transition">Ajukan Revisi</button>
                        </form>
                    @endif
                    <a href="{{ route('courses.show', ['course' => $assignment->course, 'tab' => 'assignments']) }}"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs sm:text-sm font-bold text-white hover:bg-slate-800 transition">
                        &larr; Kembali ke Kelas
                    </a>
                </div>
            </div>
        </div>

    {{-- KONDISI 2: MULAI UJIAN SECARA EKSPLISIT --}}
    @if((!$submission || !$submission->started_at) && !$isPreview)
        <form method="post" action="{{ route('assignments.exam.start', $assignment) }}" class="rounded-3xl border border-amber-200 bg-white p-7 text-center shadow-sm">
            @csrf
            <h2 class="text-lg font-bold text-slate-900">Siap memulai pengerjaan?</h2>
            <p class="mt-2 text-sm text-slate-600">Waktu ujian mulai berjalan setelah Anda memilih Mulai. Anda dapat menyimpan jawaban sebagai draf selama waktu pengerjaan.</p>
            <button type="submit" class="mt-5 rounded-xl bg-teal-800 px-6 py-3 text-sm font-bold text-white hover:bg-teal-700">Mulai Ujian</button>
        </form>
    {{-- KONDISI 3: FORM PENGERJAAN SOAL AKTIF (MAHASISWA ATAU MODE PRATINJAU) --}}
    @else
        <form id="examForm" method="post" action="{{ route('assignments.exam.submit', $assignment) }}" class="space-y-6"
            @input="saveDraft()" @change="saveDraft()"
            @submit="if ($event.submitter?.name === 'save_draft') { submitting = true; return; } if (!confirm('Apakah Anda yakin ingin menyelesaikan dan mengumpulkan ujian ini?')) { $event.preventDefault(); return false; } submitting = true; clearDraft();">
            @csrf

            {{-- Bagian 1: Butir Soal Objektif / Pilihan Ganda --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-base font-bold text-slate-900">
                        {{ $assignment->isHybrid() ? 'Bagian I: Pilihan Ganda (' . ($assignment->mcq_weight_percentage ?? 30) . '%)' : 'Daftar Butir Pertanyaan' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pilih salah satu jawaban yang paling tepat.</p>
                </div>

                @if($assignment->questions->isEmpty())
                    <p class="text-sm text-slate-400 italic text-center py-6">
                        Belum ada butir pertanyaan yang disusun oleh dosen pengampu.
                    </p>
                @else
                    <div class="space-y-6">
                        @foreach($assignment->questions as $idx => $q)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5 space-y-4">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white shrink-0 mt-0.5">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div class="flex-1">
                                        <p class="text-sm sm:text-base font-semibold text-slate-900 leading-relaxed">{{ $q->prompt }}</p>
                                        <span class="inline-block mt-1 text-xs text-teal-800 font-bold uppercase">({{ $q->points }} Poin)</span>
                                    </div>
                                </div>

                                {{-- Opsi Pilihan Ganda --}}
                                @if($q->isMultipleChoice() && !empty($q->options_json))
                                    <div class="space-y-2.5 pt-1">
                                        @foreach($q->options_json as $opt)
                                            <label class="flex items-center gap-3.5 rounded-xl border border-slate-200 bg-white p-3.5 text-sm text-slate-800 hover:bg-teal-50/40 hover:border-teal-300 has-[:checked]:border-teal-600 has-[:checked]:bg-teal-50/70 has-[:checked]:ring-1 has-[:checked]:ring-teal-600 transition cursor-pointer">
                                                @php($savedAnswer = $submission?->answers?->firstWhere('question_id', $q->id))
                                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt['key'] }}" @checked($savedAnswer?->student_answer === $opt['key'])
                                                    class="h-4 w-4 text-teal-700 focus:ring-teal-700">
                                                <span class="leading-relaxed">
                                                    <strong class="font-bold text-slate-900">{{ $opt['key'] }}.</strong> {{ $opt['text'] }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif($q->isShortAnswer())
                                    <div class="pt-1">
                                        @php($savedAnswer = $submission?->answers?->firstWhere('question_id', $q->id))
                                        <input type="text" name="answers[{{ $q->id }}]" value="{{ old('answers.'.$q->id, $savedAnswer?->student_answer) }}" placeholder="Ketik jawaban singkat di sini..."
                                            class="w-full rounded-xl border-slate-300 text-sm py-2.5 px-3.5 focus:border-teal-700 focus:ring-teal-700">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Bagian 2: Soal Esai Analitis Khusus Format Hybrid --}}
            @if($assignment->isHybrid())
                <div class="rounded-3xl border border-indigo-200 bg-white p-6 sm:p-8 shadow-sm space-y-4">
                    <div class="border-b border-indigo-100 pb-3">
                        <h2 class="text-base font-bold text-indigo-950">
                            Bagian II: Studi Kasus & Analisis Kritis ({{ $assignment->essay_weight_percentage ?? 70 }}%)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            Tuliskan analisis komprehensif Anda berdasarkan skenario kasus di bawah.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Teks Jawaban Analitis Mahasiswa *</label>
                        <textarea name="essay_answer" rows="10" required
                            placeholder="Ketik analisis argumentatif Anda di sini (minimal 100 kata)..."
                            class="mt-1.5 w-full rounded-2xl border-slate-300 p-4 text-base font-serif leading-relaxed text-slate-800 focus:border-indigo-700 focus:ring-indigo-700 shadow-xs">{{ old('essay_answer', $submission?->draft_body_text ?? '') }}</textarea>
                    </div>
                </div>
            @endif

            {{-- Tombol Pengiriman Jawaban --}}
            @if(!$isPreview)
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs sm:text-sm text-slate-500">
                        Pastikan seluruh butir soal telah dijawab sebelum mengumpulkan. Draf jawaban tersimpan otomatis di peramban.
                    </p>
                    <div class="flex flex-wrap gap-2">
                    <button type="submit" formaction="{{ route('assignments.exam.draft', $assignment) }}" formnovalidate name="save_draft" value="1" :disabled="submitting"
                        class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 shadow transition disabled:opacity-60 disabled:cursor-not-allowed">
                        Simpan Draf
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="rounded-xl bg-teal-800 px-6 py-3 text-xs sm:text-sm font-bold text-white hover:bg-teal-700 shadow transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <span x-text="submitting ? 'Mengirim Jawaban...' : 'Kirim Jawaban Ujian →'">Kirim Jawaban Ujian &rarr;</span>
                    </button>
                    </div>
                </div>
            @else
                <div class="rounded-2xl bg-amber-50 p-4 border border-amber-200 text-xs sm:text-sm text-amber-800 text-center">
                    Anda berada dalam <strong>Mode Pratinjau Dosen</strong>. Tombol pengiriman dinonaktifkan.
                </div>
            @endif
        </form>
    @endif
    @endif
</div>
@endsection
