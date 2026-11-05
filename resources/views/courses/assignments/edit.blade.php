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

        <label class="flex items-start gap-2 text-sm text-slate-700">
            <input type="hidden" name="allow_revisions" value="0">
            <input type="checkbox" name="allow_revisions" value="1" @checked(old('allow_revisions', $assignment->allow_revisions)) class="mt-0.5 rounded border-slate-300 text-teal-700 focus:ring-teal-700">
            <span><strong>Izinkan revisi setelah pengiriman</strong><span class="block text-xs font-normal text-slate-500">Submission yang direvisi kembali ke antrean penilaian.</span></span>
        </label>

        @php
            $courseCpmks = $course->cpmkList();
            $linkedCpmks = $assignment->config_json['cpmk_outcomes'] ?? [];
            $configuredSections = $assignment->config_json['sections'] ?? [];
        @endphp
        <fieldset class="rounded-2xl border border-indigo-200 bg-indigo-50/50 p-4">
            <legend class="px-1 text-sm font-bold text-indigo-950">CPMK yang dinilai</legend>
            @if(count($courseCpmks))
                <div class="mt-1 grid gap-2 sm:grid-cols-2">
                    @foreach($courseCpmks as $i => $cpmk)
                        <label class="flex items-start gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="cpmk_indices[]" value="{{ $i }}" @checked(in_array($cpmk, $linkedCpmks, true)) class="mt-0.5 rounded border-slate-300 text-indigo-700">
                            <span>{{ preg_replace('/^(?:(?:\d+|cpmk\s*\d*)[.:)\s]+|[-*•]\s*)/iu', '', $cpmk) }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="mt-1 text-sm text-amber-800">CPMK matakuliah belum tersedia. Tugas ini saat ini tidak tertaut ke CPMK.</p>
            @endif
        </fieldset>

        <fieldset class="rounded-2xl border border-teal-200 bg-white p-4">
            <legend class="px-1 text-sm font-bold text-teal-950">Bagian tugas dan bentuk jawaban</legend>
            <p class="mb-3 text-xs text-slate-500">Rangkai hingga 8 bagian. Bobot dicatat sebagai acuan; nilai akhir tugas tetap ditetapkan dosen secara keseluruhan. Jika digunakan, total bobot harus 100%.</p>
            <div class="space-y-2">
                @for($part = 0; $part < 8; $part++)
                    @php($section = $configuredSections[$part] ?? [])
                    <div class="grid gap-2 rounded-xl bg-slate-50 p-2 sm:grid-cols-12">
                        <input name="sections[{{ $part }}][title]" value="{{ old("sections.{$part}.title", $section['title'] ?? '') }}" placeholder="Nama bagian {{ $part + 1 }}" class="sm:col-span-3 rounded-lg border-slate-300 text-xs">
                        <input name="sections[{{ $part }}][prompt]" value="{{ old("sections.{$part}.prompt", $section['prompt'] ?? '') }}" placeholder="Instruksi bagian" class="sm:col-span-4 rounded-lg border-slate-300 text-xs">
                        <select name="sections[{{ $part }}][response_type]" class="sm:col-span-2 rounded-lg border-slate-300 text-xs">
                            @foreach(['long_text' => 'Teks/jawaban', 'url' => 'Tautan', 'audio_video' => 'Audio/video', 'numeric' => 'Angka'] as $value => $label)
                                <option value="{{ $value }}" @selected(old("sections.{$part}.response_type", $section['response_type'] ?? 'long_text') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="sections[{{ $part }}][weight]" min="0" max="100" step="0.1" value="{{ old("sections.{$part}.weight", $section['weight'] ?? '') }}" placeholder="Bobot %" class="sm:col-span-1 rounded-lg border-slate-300 text-xs">
                        <label class="sm:col-span-2 flex items-center gap-1 text-[10px]"><input type="checkbox" name="sections[{{ $part }}][required]" value="1" @checked(old("sections.{$part}.required", $section['required'] ?? false)) class="rounded"> Wajib</label>
                        @if(count($courseCpmks))
                            @php($sectionCpmks = $section['cpmk_outcomes'] ?? [])
                            <select name="sections[{{ $part }}][cpmk_indices][]" multiple class="sm:col-span-12 rounded-lg border-slate-300 text-xs" aria-label="CPMK untuk bagian {{ $part + 1 }}">
                                @foreach($courseCpmks as $cpmkIndex => $cpmk)<option value="{{ $cpmkIndex }}" @selected(in_array($cpmk, $sectionCpmks, true))>{{ \Illuminate\Support\Str::limit(strip_tags($cpmk), 100) }}</option>@endforeach
                            </select>
                        @endif
                    </div>
                @endfor
            </div>
        </fieldset>

        @include('courses.assignments.edit.format-configurations')
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
