@extends('layouts.hub')
@section('title', 'Sunting Tugas: ' . $assignment->title . ' — ' . $course->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'assignments']) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                &larr; Kembali ke Daftar Tugas Perkuliahan
            </a>
            <div class="mt-2 flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-slate-900">Sunting Tugas Perkuliahan</h1>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold border {{ $assignment->typeBadgeColor() }}">
                    {{ $assignment->typeLabel() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ $course->code }} — {{ $course->name }} (Kelas {{ $course->class_group ?: 'A' }})
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($assignment->isStimulusExam() || $assignment->isHybrid())
                <a href="{{ route('assignments.questions.index', $assignment) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 transition">
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Kelola Butir Soal ({{ $assignment->questions->count() }})
                </a>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-800 space-y-1">
            <p class="font-bold">Terjadi kesalahan pada formulir:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('courses.assignments.update', [$course, $assignment]) }}" enctype="multipart/form-data"
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6"
        x-data="{
            assignmentType: '{{ old('assignment_type', $assignment->assignment_type ?? 'writing') }}'
        }">
        @csrf
        @method('PUT')

        {{-- Pemilihan Format Tugas --}}
        <div>
            <label class="block text-xs font-bold text-slate-800 mb-2">Format & Model Penugasan *</label>
            <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-5">
                <label class="flex items-start gap-2 rounded-lg border p-3 cursor-pointer transition"
                    :class="assignmentType === 'writing' ? 'border-teal-700 bg-teal-50/60 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="writing" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                    <div>
                        <p class="text-xs font-bold text-slate-900">Esai Akademik</p>
                        <p class="text-[11px] text-slate-500">WriteWise AI & rubrik analitik.</p>
                    </div>
                </label>
                <label class="flex items-start gap-2 rounded-lg border p-3 cursor-pointer transition"
                    :class="assignmentType === 'stimulus_exam' ? 'border-amber-600 bg-amber-50/60 ring-1 ring-amber-600' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="stimulus_exam" x-model="assignmentType" class="mt-0.5 text-amber-600 focus:ring-amber-600">
                    <div>
                        <p class="text-xs font-bold text-slate-900">Ujian Stimulus</p>
                        <p class="text-[11px] text-slate-500">Audio listening & PG otomatis.</p>
                    </div>
                </label>
                <label class="flex items-start gap-2 rounded-lg border p-3 cursor-pointer transition"
                    :class="assignmentType === 'hybrid' ? 'border-indigo-600 bg-indigo-50/60 ring-1 ring-indigo-600' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="hybrid" x-model="assignmentType" class="mt-0.5 text-indigo-600 focus:ring-indigo-600">
                    <div>
                        <p class="text-xs font-bold text-slate-900">Campuran MKDU</p>
                        <p class="text-[11px] text-slate-500">PG otomatis + Esai dosen.</p>
                    </div>
                </label>
                <label class="flex items-start gap-2 rounded-lg border p-3 cursor-pointer transition"
                    :class="assignmentType === 'translation' ? 'border-purple-600 bg-purple-50/60 ring-1 ring-purple-600' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="translation" x-model="assignmentType" class="mt-0.5 text-purple-600 focus:ring-purple-600">
                    <div>
                        <p class="text-xs font-bold text-slate-900">Penerjemahan Teks</p>
                        <p class="text-[11px] text-slate-500">Panel ganda ST vs TT & TQA.</p>
                    </div>
                </label>
                <label class="flex items-start gap-2 rounded-lg border p-3 cursor-pointer transition"
                    :class="assignmentType === 'milestone_artifact' ? 'border-rose-600 bg-rose-50/60 ring-1 ring-rose-600' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="assignment_type" value="milestone_artifact" x-model="assignmentType" class="mt-0.5 text-rose-600 focus:ring-rose-600">
                    <div>
                        <p class="text-xs font-bold text-slate-900">Proposal Bertahap</p>
                        <p class="text-[11px] text-slate-500">Bab 1-3 + slide/video seminar.</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Judul, Status & Batas Waktu --}}
        <div class="grid gap-4 sm:grid-cols-12">
            <div class="sm:col-span-6">
                <label class="block text-xs font-semibold text-slate-700">Judul Tugas Perkuliahan *</label>
                <input type="text" name="title" required value="{{ old('title', $assignment->title) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-slate-700">Status Publikasi *</label>
                <select name="status" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    <option value="published" @selected(old('status', $assignment->status) === 'published')>Terbit (Bisa Diakses Mahasiswa)</option>
                    <option value="draft" @selected(old('status', $assignment->status) === 'draft')>Draf Dosen (Terkunci)</option>
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-slate-700">Batas Waktu (Deadline)</label>
                <input type="datetime-local" name="due_at" value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
        </div>

        {{-- Konfigurasi Khusus Ujian Stimulus / Listening --}}
        <div x-show="assignmentType === 'stimulus_exam'" class="rounded-xl bg-amber-50/80 p-4 border border-amber-200 space-y-4">
            <p class="text-xs font-bold text-amber-950">Konfigurasi Ujian Stimulus / Listening:</p>

            @if(!empty($assignment->config_json['audio_file_path']))
                <div class="rounded-lg bg-white p-3.5 border border-amber-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <span class="text-[11px] font-bold text-amber-900">Berkas Audio Saat Ini:</span>
                        <div class="flex items-center gap-2">
                            <audio controls src="{{ route('assignments.audio-stream', $assignment) }}" class="h-8 max-w-xs"></audio>
                        </div>
                    </div>
                    <label class="flex items-center gap-1.5 text-xs text-red-600 font-medium cursor-pointer">
                        <input type="checkbox" name="delete_audio" value="1" class="rounded text-red-600 focus:ring-red-500">
                        Hapus audio saat ini
                    </label>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-semibold text-amber-900">Ganti / Unggah Berkas Audio</label>
                    <input type="file" name="audio_file" accept="audio/*" class="mt-1 w-full text-xs text-slate-700 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-700 file:text-white hover:file:bg-amber-800">
                    <p class="mt-1 text-[10px] text-amber-700">MP3/WAV/M4A maks 50 MB (biarkan kosong jika tidak mengubah).</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-amber-900">Batas Pemutaran Audio</label>
                    <select name="audio_max_plays" class="mt-1 w-full rounded-lg border-amber-300 text-xs focus:border-amber-700 focus:ring-amber-700">
                        <option value="1" @selected((int) old('audio_max_plays', $assignment->config_json['audio_max_plays'] ?? 2) === 1)>1 kali (Standar Ujian TOEFL Ketat)</option>
                        <option value="2" @selected((int) old('audio_max_plays', $assignment->config_json['audio_max_plays'] ?? 2) === 2)>2 kali (Standar Kuliah Reguler)</option>
                        <option value="3" @selected((int) old('audio_max_plays', $assignment->config_json['audio_max_plays'] ?? 2) === 3)>3 kali</option>
                        <option value="0" @selected((int) old('audio_max_plays', $assignment->config_json['audio_max_plays'] ?? 2) === 0)>Bebas (Latihan Mandiri Tanpa Batas)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-amber-900">Batas Waktu Pengerjaan (Menit)</label>
                    <input type="number" name="time_limit_minutes" min="5" max="360" value="{{ old('time_limit_minutes', $assignment->time_limit_minutes ?? 45) }}" class="mt-1 w-full rounded-lg border-amber-300 text-xs focus:border-amber-700 focus:ring-amber-700">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-amber-900">Teks Stimulus Bacaan / Transkrip Materi (Opsional)</label>
                <textarea name="reading_passage" rows="3" placeholder="Jika ada naskah teks stimulus..." class="mt-1 w-full rounded-lg border-amber-300 text-xs focus:border-amber-700 focus:ring-amber-700">{{ old('reading_passage', $assignment->config_json['reading_passage'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Konfigurasi Khusus Format Hybrid --}}
        <div x-show="assignmentType === 'hybrid'" class="rounded-xl bg-indigo-50/80 p-4 border border-indigo-200 space-y-3">
            <p class="text-xs font-bold text-indigo-950">Konfigurasi Penilaian Campuran (Hybrid Assessment):</p>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-semibold text-indigo-900">Bobot Pilihan Ganda (%)</label>
                    <input type="number" name="mcq_weight_percentage" min="0" max="100" value="{{ old('mcq_weight_percentage', $assignment->mcq_weight_percentage ?? 30) }}" class="mt-1 w-full rounded-lg border-indigo-300 text-xs focus:border-indigo-700 focus:ring-indigo-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-indigo-900">Bobot Esai / Kasus (%)</label>
                    <input type="number" name="essay_weight_percentage" min="0" max="100" value="{{ old('essay_weight_percentage', $assignment->essay_weight_percentage ?? 70) }}" class="mt-1 w-full rounded-lg border-indigo-300 text-xs focus:border-indigo-700 focus:ring-indigo-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-indigo-900">Durasi Pengerjaan (Menit)</label>
                    <input type="number" name="time_limit_minutes" min="5" max="360" value="{{ old('time_limit_minutes', $assignment->time_limit_minutes ?? 60) }}" class="mt-1 w-full rounded-lg border-indigo-300 text-xs focus:border-indigo-700 focus:ring-indigo-700">
                </div>
            </div>
        </div>

        {{-- Konfigurasi Khusus Penerjemahan Teks --}}
        <div x-show="assignmentType === 'translation'" class="rounded-xl bg-purple-50/80 p-4 border border-purple-200 space-y-3">
            <p class="text-xs font-bold text-purple-950">Konfigurasi Tugas Penerjemahan Teks:</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-purple-900">Bahasa Sumber (Source Language) *</label>
                    <input type="text" name="source_language" value="{{ old('source_language', $assignment->config_json['source_language'] ?? 'Bahasa Indonesia') }}" class="mt-1 w-full rounded-lg border-purple-300 text-xs focus:border-purple-700 focus:ring-purple-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-purple-900">Bahasa Sasaran (Target Language) *</label>
                    <input type="text" name="target_language" value="{{ old('target_language', $assignment->config_json['target_language'] ?? 'English') }}" class="mt-1 w-full rounded-lg border-purple-300 text-xs focus:border-purple-700 focus:ring-purple-700">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-purple-900">Naskah Sumber (Source Text) yang Harus Diterjemahkan *</label>
                <textarea name="source_text" rows="5" class="mt-1 w-full rounded-lg border-purple-300 font-serif text-xs leading-relaxed focus:border-purple-700 focus:ring-purple-700">{{ old('source_text', $assignment->config_json['source_text'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Konfigurasi Khusus Proposal Bertahap --}}
        <div x-show="assignmentType === 'milestone_artifact'" class="rounded-xl bg-rose-50/80 p-4 border border-rose-200 space-y-3">
            <p class="text-xs font-bold text-rose-950">Tahapan Milestone Proposal & Proyek:</p>
            <div class="space-y-2">
                @php
                    $existingMilestones = $assignment->config_json['milestones'] ?? [
                        ['id' => 1, 'title' => 'Bab 1: Pendahuluan & Research Questions'],
                        ['id' => 2, 'title' => 'Bab 2: Kajian Pustaka & Kerangka Teori (LitSynth)'],
                        ['id' => 3, 'title' => 'Bab 3: Desain Metodologi & Instrumen Penelitian'],
                        ['id' => 4, 'title' => 'Naskah Final Proposal & Tautan Slide/Video Seminar'],
                    ];
                @endphp
                @foreach($existingMilestones as $idx => $m)
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-rose-700 text-white font-bold text-xs shrink-0">
                            {{ $idx + 1 }}
                        </span>
                        <input type="text" name="milestones[{{ $idx }}][title]" value="{{ old("milestones.{$idx}.title", $m['title'] ?? '') }}" class="w-full rounded-lg border-rose-300 text-xs focus:border-rose-700 focus:ring-rose-700">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Rubrik & Feedback --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Rubrik Penilaian</label>
                <input type="text" name="rubric_name" value="{{ old('rubric_name', $currentRubric?->name ?? 'Rubrik Analitik Penulisan Akademik') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700">Bahasa Umpan Balik AI</label>
                <select name="feedback_language" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    <option value="id" @selected(old('feedback_language', $assignment->feedback_language) === 'id')>Bahasa Indonesia</option>
                    <option value="en" @selected(old('feedback_language', $assignment->feedback_language) === 'en')>English (Academic)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700">Tingkat Intervensi Koreksi</label>
                <select name="feedback_level" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                    <option value="indirect" @selected(old('feedback_level', $assignment->feedback_level) === 'indirect')>Indirect (Koreksi Panduan/Pertanyaan Kritis)</option>
                    <option value="balanced" @selected(old('feedback_level', $assignment->feedback_level) === 'balanced')>Balanced (Seimbang & Rekomendasi)</option>
                    <option value="direct" @selected(old('feedback_level', $assignment->feedback_level) === 'direct')>Direct (Perbaikan Langsung)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700">Petunjuk Pengerjaan & Kriteria Tugas *</label>
            <textarea name="instructions" rows="5" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">{{ old('instructions', $assignment->instructions) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-slate-200">
            <a href="{{ route('courses.show', ['course' => $course, 'tab' => 'assignments']) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                Batal
            </a>
            <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white hover:bg-teal-700 transition shadow">
                Simpan Perubahan Tugas
            </button>
        </div>
    </form>

    {{-- Zona Bahaya: Hapus Tugas --}}
    <div class="rounded-2xl border border-red-200 bg-red-50/50 p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-red-900">Hapus Tugas Perkuliahan</h3>
                <p class="text-xs text-red-700 mt-0.5">
                    Tindakan ini akan menghapus tugas "{{ $assignment->title }}" beserta seluruh draf submisi mahasiswa, butir soal ujian, dan nilai terkait secara permanen.
                </p>
            </div>
            <form method="post" action="{{ route('courses.assignments.destroy', [$course, $assignment]) }}"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas perkuliahan \'{{ addslashes($assignment->title) }}\'? Seluruh data submisi mahasiswa dan nilai akan ikut terhapus secara permanen.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-red-300 bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition">
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
