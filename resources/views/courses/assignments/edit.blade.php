@extends('layouts.hub')
@section('title', 'Sunting Tugas: ' . $assignment->title . ' — ' . $course->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'assignments']) }}" class="text-sm font-semibold text-teal-800 hover:text-teal-950 flex items-center gap-1">
                &larr; Kembali ke Daftar Tugas Perkuliahan
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Sunting Tugas Perkuliahan</h1>
                <span class="rounded-full px-3 py-1 text-xs font-bold border {{ $assignment->typeBadgeColor() }}">
                    {{ $assignment->typeLabel() }}
                </span>
            </div>
            <p class="text-sm text-slate-600 mt-1">
                {{ $course->code }} — {{ $course->name }} (Kelas {{ $course->class_group ?: 'A' }})
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($assignment->isStimulusExam() || $assignment->isHybrid())
                <a href="{{ route('assignments.questions.index', $assignment) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Kelola Butir Soal ({{ $assignment->questions->count() }})
                </a>
            @endif
        </div>
    </div>

    <form method="post" action="{{ route('courses.assignments.update', [$course, $assignment]) }}" enctype="multipart/form-data"
        class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-6"
        x-data="{
            assignmentType: '{{ old('assignment_type', $assignment->assignment_type ?? 'writing') }}'
        }">
        @csrf
        @method('PUT')

        {{-- Pemilihan Format Tugas --}}
        <div>
            <label class="block text-sm font-bold text-slate-800 mb-2.5">Format &amp; Model Penugasan *</label>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <label class="flex items-start gap-2.5 rounded-xl border p-3.5 cursor-pointer transition"
                    :class="assignmentType === 'writing' ? 'border-teal-700 bg-teal-50/60 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="writing" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Esai Akademik</p>
                        <p class="text-xs text-slate-500 mt-0.5">WriteWise AI &amp; rubrik analitik.</p>
                    </div>
                </label>
                <label class="flex items-start gap-2.5 rounded-xl border p-3.5 cursor-pointer transition"
                    :class="assignmentType === 'stimulus_exam' ? 'border-amber-600 bg-amber-50/60 ring-1 ring-amber-600' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="stimulus_exam" x-model="assignmentType" class="mt-0.5 text-amber-600 focus:ring-amber-600">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Ujian Stimulus</p>
                        <p class="text-xs text-slate-500 mt-0.5">Audio listening &amp; PG otomatis.</p>
                    </div>
                </label>
                <label class="flex items-start gap-2.5 rounded-xl border p-3.5 cursor-pointer transition"
                    :class="assignmentType === 'hybrid' ? 'border-indigo-600 bg-indigo-50/60 ring-1 ring-indigo-600' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="hybrid" x-model="assignmentType" class="mt-0.5 text-indigo-600 focus:ring-indigo-600">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Campuran MKDU</p>
                        <p class="text-xs text-slate-500 mt-0.5">PG otomatis + Esai dosen.</p>
                    </div>
                </label>
                <label class="flex items-start gap-2.5 rounded-xl border p-3.5 cursor-pointer transition"
                    :class="assignmentType === 'translation' ? 'border-purple-600 bg-purple-50/60 ring-1 ring-purple-600' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="translation" x-model="assignmentType" class="mt-0.5 text-purple-600 focus:ring-purple-600">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Penerjemahan Teks</p>
                        <p class="text-xs text-slate-500 mt-0.5">Panel ganda ST vs TT &amp; TQA.</p>
                    </div>
                </label>
                <label class="flex items-start gap-2.5 rounded-xl border p-3.5 cursor-pointer transition"
                    :class="assignmentType === 'milestone_artifact' ? 'border-rose-600 bg-rose-50/60 ring-1 ring-rose-600' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="milestone_artifact" x-model="assignmentType" class="mt-0.5 text-rose-600 focus:ring-rose-600">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-slate-900">Proposal Bertahap</p>
                        <p class="text-xs text-slate-500 mt-0.5">Bab 1-3 + slide/video seminar.</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Judul, Status & Batas Waktu --}}
        <div class="grid gap-5 sm:grid-cols-12">
            <div class="sm:col-span-6">
                <label class="block text-sm font-semibold text-slate-700">Judul Tugas Perkuliahan *</label>
                <input type="text" name="title" required value="{{ old('title', $assignment->title) }}" placeholder="Judul tugas perkuliahan..." class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-sm font-semibold text-slate-700">Status Publikasi *</label>
                <select name="status" required class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    <option value="published" @selected(old('status', $assignment->status) === 'published')>Terbit (Bisa Diakses Mahasiswa)</option>
                    <option value="draft" @selected(old('status', $assignment->status) === 'draft')>Draf Dosen (Terkunci)</option>
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="block text-sm font-semibold text-slate-700">Batas Waktu (Deadline)</label>
                <input type="datetime-local" name="due_at" value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\TH:i')) }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
        </div>

        {{-- Konfigurasi Khusus Ujian Stimulus / Listening --}}
        <div x-show="assignmentType === 'stimulus_exam'" x-cloak class="rounded-2xl bg-amber-50/80 p-5 border border-amber-200 space-y-4">
            <p class="text-sm font-bold text-amber-950">Konfigurasi Ujian Stimulus / Listening:</p>

            @if(!empty($assignment->config_json['audio_file_path']))
                <div class="rounded-xl bg-white p-4 border border-amber-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-amber-900">Berkas Audio Saat Ini:</span>
                        <div class="flex items-center gap-2">
                            <audio controls src="{{ route('assignments.audio-stream', $assignment) }}" class="h-9 max-w-xs"></audio>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-rose-600 font-semibold cursor-pointer">
                        <input type="checkbox" name="delete_audio" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                        Hapus audio saat ini
                    </label>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-amber-900">Ganti / Unggah Berkas Audio</label>
                    <input type="file" name="audio_file" accept="audio/*" class="mt-1.5 w-full text-xs text-slate-700 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-700 file:text-white hover:file:bg-amber-800">
                    <p class="mt-1 text-xs text-amber-700">MP3/WAV/M4A maks 50 MB (biarkan kosong jika tidak mengubah).</p>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-amber-900">Batas Pemutaran Audio</label>
                    <select name="audio_max_plays" class="mt-1.5 w-full rounded-xl border-amber-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                        <option value="1" @selected((int) old('audio_max_plays', $assignment->config_json['audio_max_plays'] ?? 2) === 1)>1 kali (Standar Ujian TOEFL Ketat)</option>
                        <option value="2" @selected((int) old('audio_max_plays', $assignment->config_json['audio_max_plays'] ?? 2) === 2)>2 kali (Standar Kuliah Reguler)</option>
                        <option value="3" @selected((int) old('audio_max_plays', $assignment->config_json['audio_max_plays'] ?? 2) === 3)>3 kali</option>
                        <option value="0" @selected((int) old('audio_max_plays', $assignment->config_json['audio_max_plays'] ?? 2) === 0)>Bebas (Latihan Mandiri Tanpa Batas)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-amber-900">Batas Waktu Pengerjaan (Menit)</label>
                    <input type="number" name="time_limit_minutes" min="5" max="360" value="{{ old('time_limit_minutes', $assignment->time_limit_minutes ?? 45) }}" class="mt-1.5 w-full rounded-xl border-amber-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                </div>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-semibold text-amber-900">Teks Stimulus Bacaan / Transkrip Materi (Opsional)</label>
                <textarea name="reading_passage" rows="4" placeholder="Teks stimulus bacaan, transkrip rekaman, atau studi kasus pendamping..." class="mt-1.5 w-full rounded-xl border-amber-300 font-serif text-base leading-relaxed focus:border-amber-700 focus:ring-amber-700">{{ old('reading_passage', $assignment->config_json['reading_passage'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Konfigurasi Khusus Format Hybrid --}}
        <div x-show="assignmentType === 'hybrid'" x-cloak class="rounded-2xl bg-indigo-50/80 p-5 border border-indigo-200 space-y-3">
            <p class="text-sm font-bold text-indigo-950">Konfigurasi Penilaian Campuran (Hybrid Assessment):</p>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-indigo-900">Bobot Pilihan Ganda (%)</label>
                    <input type="number" name="mcq_weight_percentage" min="0" max="100" value="{{ old('mcq_weight_percentage', $assignment->mcq_weight_percentage ?? 30) }}" class="mt-1.5 w-full rounded-xl border-indigo-300 text-sm focus:border-indigo-700 focus:ring-indigo-700">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-indigo-900">Bobot Esai / Kasus (%)</label>
                    <input type="number" name="essay_weight_percentage" min="0" max="100" value="{{ old('essay_weight_percentage', $assignment->essay_weight_percentage ?? 70) }}" class="mt-1.5 w-full rounded-xl border-indigo-300 text-sm focus:border-indigo-700 focus:ring-indigo-700">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-indigo-900">Durasi Pengerjaan (Menit)</label>
                    <input type="number" name="time_limit_minutes" min="5" max="360" value="{{ old('time_limit_minutes', $assignment->time_limit_minutes ?? 60) }}" class="mt-1.5 w-full rounded-xl border-indigo-300 text-sm focus:border-indigo-700 focus:ring-indigo-700">
                </div>
            </div>
        </div>

        {{-- Konfigurasi Khusus Penerjemahan Teks --}}
        <div x-show="assignmentType === 'translation'" x-cloak class="rounded-2xl bg-purple-50/80 p-5 border border-purple-200 space-y-3">
            <p class="text-sm font-bold text-purple-950">Konfigurasi Tugas Penerjemahan Teks:</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-purple-900">Bahasa Sumber (Source Language) *</label>
                    <input type="text" name="source_language" value="{{ old('source_language', $assignment->config_json['source_language'] ?? 'Bahasa Indonesia') }}" class="mt-1.5 w-full rounded-xl border-purple-300 text-sm focus:border-purple-700 focus:ring-purple-700">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-purple-900">Bahasa Sasaran (Target Language) *</label>
                    <input type="text" name="target_language" value="{{ old('target_language', $assignment->config_json['target_language'] ?? 'English') }}" class="mt-1.5 w-full rounded-xl border-purple-300 text-sm focus:border-purple-700 focus:ring-purple-700">
                </div>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-purple-900">Naskah Sumber (Source Text) yang Harus Diterjemahkan *</label>
                <textarea name="source_text" rows="5" placeholder="Tempelkan naskah teks sumber (ST) yang wajib diterjemahkan mahasiswa..." class="mt-1.5 w-full rounded-xl border-purple-300 font-serif text-base leading-relaxed focus:border-purple-700 focus:ring-purple-700">{{ old('source_text', $assignment->config_json['source_text'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Konfigurasi Khusus Proposal Bertahap --}}
        <div x-show="assignmentType === 'milestone_artifact'" x-cloak class="rounded-2xl bg-rose-50/80 p-5 border border-rose-200 space-y-3">
            <p class="text-sm font-bold text-rose-950">Tahapan Milestone Proposal &amp; Proyek:</p>
            <div class="space-y-2.5">
                @php
                    $existingMilestones = $assignment->config_json['milestones'] ?? [
                        ['id' => 1, 'title' => 'Bab 1: Pendahuluan & Research Questions'],
                        ['id' => 2, 'title' => 'Bab 2: Kajian Pustaka & Kerangka Teori (LitSynth)'],
                        ['id' => 3, 'title' => 'Bab 3: Desain Metodologi & Instrumen Penelitian'],
                        ['id' => 4, 'title' => 'Naskah Final Proposal & Tautan Slide/Video Seminar'],
                    ];
                @endphp
                @foreach($existingMilestones as $idx => $m)
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-rose-700 text-white font-bold text-xs shrink-0">
                            {{ $idx + 1 }}
                        </span>
                        <input type="text" name="milestones[{{ $idx }}][title]" value="{{ old("milestones.{$idx}.title", $m['title'] ?? '') }}" class="w-full rounded-xl border-rose-300 text-sm focus:border-rose-700 focus:ring-rose-700">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Rubrik & Feedback --}}
        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-semibold text-slate-700">Nama Rubrik Penilaian</label>
                <input type="text" name="rubric_name" value="{{ old('rubric_name', $currentRubric?->name ?? 'Rubrik Analitik Penulisan Akademik') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Bahasa Umpan Balik AI</label>
                <select name="feedback_language" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    <option value="id" @selected(old('feedback_language', $assignment->feedback_language) === 'id')>Bahasa Indonesia</option>
                    <option value="en" @selected(old('feedback_language', $assignment->feedback_language) === 'en')>English (Academic)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Tingkat Intervensi Koreksi</label>
                <select name="feedback_level" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    <option value="indirect" @selected(old('feedback_level', $assignment->feedback_level) === 'indirect')>Indirect (Koreksi Panduan/Pertanyaan Kritis)</option>
                    <option value="balanced" @selected(old('feedback_level', $assignment->feedback_level) === 'balanced')>Balanced (Seimbang &amp; Rekomendasi)</option>
                    <option value="direct" @selected(old('feedback_level', $assignment->feedback_level) === 'direct')>Direct (Perbaikan Langsung)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700">Petunjuk Pengerjaan &amp; Kriteria Tugas *</label>
            <textarea name="instructions" rows="5" required class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">{{ old('instructions', $assignment->instructions) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'assignments']) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                Batal
            </a>
            <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-sm font-bold text-white hover:bg-teal-700 transition shadow-xs">
                Simpan Perubahan Tugas
            </button>
        </div>
    </form>

    {{-- Zona Bahaya: Hapus Tugas --}}
    <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-6 shadow-2xs">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-rose-950">Hapus Tugas Perkuliahan</h3>
                <p class="text-sm text-rose-800 mt-0.5">
                    Tindakan ini akan menghapus tugas "{{ $assignment->title }}" beserta seluruh draf submisi mahasiswa, butir soal ujian, dan nilai terkait secara permanen.
                </p>
            </div>
            <form method="post" action="{{ route('courses.assignments.destroy', [$course, $assignment]) }}"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas perkuliahan \'{{ addslashes($assignment->title) }}\'? Seluruh data submisi mahasiswa dan nilai akan ikut terhapus secara permanen.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-rose-600 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-xs hover:bg-rose-700 transition">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Tugas Permanen
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
