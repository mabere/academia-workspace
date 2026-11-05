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

