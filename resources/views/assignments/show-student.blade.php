    {{-- Banner Khusus Tugas Penerjemahan Teks (Source Text Panel) --}}
    @if($assignment->isTranslation() && !empty($assignment->config_json['source_text']))
        <div class="mt-6 rounded-3xl border border-purple-200 bg-purple-50/50 p-6 sm:p-7 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-purple-100 pb-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-700 text-white font-bold text-xs">
                        ST
                    </span>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-purple-950">
                            Naskah Sumber (Source Text) — {{ $assignment->config_json['source_language'] ?? 'Bahasa Indonesia' }}
                        </h3>
                        <p class="text-xs text-purple-700">
                            Terjemahkan teks di bawah ke dalam <strong>{{ $assignment->config_json['target_language'] ?? 'English' }}</strong> pada editor di bawah.
                        </p>
                    </div>
                </div>
                <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-mono font-semibold text-purple-800">
                    {{ str_word_count($assignment->config_json['source_text']) }} kata
                </span>
            </div>
            <div class="mt-4 max-h-96 overflow-y-auto whitespace-pre-line rounded-2xl bg-white p-5 sm:p-6 font-serif text-base leading-relaxed text-slate-800 border border-purple-100 shadow-2xs">
                {{ $assignment->config_json['source_text'] }}
            </div>
        </div>
    @endif

    {{-- Banner Khusus Tugas Milestone Proposal (Progress Tracker) --}}
    @if($assignment->isMilestone() && !empty($assignment->config_json['milestones']))
        <div class="mt-6 rounded-3xl border border-rose-200 bg-rose-50/40 p-6 sm:p-7 shadow-sm">
            <div class="flex items-center justify-between border-b border-rose-100 pb-3.5">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-rose-950">Alur Pengumpulan Proposal Bertahap (Milestone Roadmap)</h3>
                    <p class="text-xs text-rose-700">Selesaikan setiap bab secara terstruktur sesuai tahapan bimbingan akademik.</p>
                </div>
                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800">
                    {{ count($assignment->config_json['milestones']) }} Tahapan
                </span>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($assignment->config_json['milestones'] as $m)
                    <div class="rounded-2xl border border-rose-200 bg-white p-4 shadow-2xs">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-rose-700 text-white font-bold text-xs">
                            {{ $m['id'] }}
                        </span>
                        <h4 class="mt-2.5 text-xs sm:text-sm font-bold text-slate-900">{{ $m['title'] }}</h4>
                        @if(!empty($m['description']))
                            <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ $m['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @php($mySubmission = $assignment->submissions->first())

    {{-- Kartu Status Submisi & Nilai Mahasiswa --}}
    @if($mySubmission)
        <div class="mt-6 rounded-3xl border {{ $mySubmission->status === 'graded' ? 'border-emerald-300 bg-gradient-to-r from-emerald-50/70 via-teal-50/40 to-white' : ($mySubmission->status === 'submitted' ? 'border-teal-300 bg-gradient-to-r from-teal-50/70 via-slate-50/40 to-white' : 'border-amber-300 bg-gradient-to-r from-amber-50/70 via-orange-50/40 to-white') }} p-6 sm:p-7 shadow-xs">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="h-12 w-12 rounded-2xl flex items-center justify-center font-bold text-xl shadow-xs shrink-0
                        {{ $mySubmission->status === 'graded' ? 'bg-emerald-600 text-white' : ($mySubmission->status === 'submitted' ? 'bg-teal-700 text-white' : 'bg-amber-600 text-white') }}">
                        @if($mySubmission->status === 'graded')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        @elseif($mySubmission->status === 'submitted')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        @else
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-xs font-bold uppercase tracking-wider border
                                {{ $mySubmission->status === 'graded' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($mySubmission->status === 'submitted' ? 'bg-teal-100 text-teal-800 border-teal-300' : 'bg-amber-100 text-amber-800 border-amber-300') }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $mySubmission->status === 'graded' ? 'bg-emerald-600' : ($mySubmission->status === 'submitted' ? 'bg-teal-600 animate-pulse' : 'bg-amber-600') }}"></span>
                                {{ $mySubmission->status === 'graded' ? 'Sudah Dinilai Dosen' : ($mySubmission->status === 'submitted' ? 'Sudah Dikumpulkan' : 'Draf Tersimpan') }}
                            </span>
                            @if($mySubmission->submitted_at)
                                <span class="text-xs text-slate-500 font-medium">
                                    Waktu Kumpul: {{ $mySubmission->submitted_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </span>
                            @endif
                        </div>
                        <h2 class="mt-1 text-base sm:text-lg font-extrabold text-slate-900">
                            @if($mySubmission->status === 'graded')
                                Evaluasi &amp; Nilai Hasil Tugas
                            @elseif($mySubmission->status === 'submitted')
                                Jawaban Tugas Berhasil Dikumpulkan
                            @else
                                Draf Jawaban Tersimpan
                            @endif
                        </h2>
                        @if($mySubmission->status === 'graded' && $mySubmission->graded_at)
                            <p class="text-xs text-slate-600 mt-0.5">
                                Dinilai oleh <strong class="text-slate-800">{{ $mySubmission->grader?->name ?? ($assignment->course?->lecturer?->name ?? 'Dosen Pengampu') }}</strong> pada {{ $mySubmission->graded_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Kotak Nilai Utama --}}
                @if($mySubmission->score !== null)
                    <div class="flex items-center gap-4 rounded-2xl bg-white border border-emerald-300/80 px-5 py-3 shadow-2xs">
                        <div class="text-right">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Nilai Akhir</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-black text-teal-900">{{ number_format($mySubmission->score, 1) }}</span>
                                <span class="text-xs font-semibold text-slate-400">/ 100</span>
                            </div>
                        </div>
                        <div class="h-10 w-px bg-slate-200"></div>
                        <div class="text-center">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Mutu</span>
                            <span class="text-2xl font-black text-emerald-700">{{ $mySubmission->letterGrade() }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-xs font-semibold text-slate-600 bg-white/90 rounded-xl px-4 py-2.5 border border-slate-200 shadow-2xs">
                        {{ $mySubmission->status === 'submitted' ? '⏳ Menunggu evaluasi & nilai dari dosen pengampu.' : '✍️ Draf belum difinalisasi dan belum dikirim ke dosen.' }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    @php
        $configuredSections = $assignment->config_json['sections'] ?? [];
        $savedDraftSections = old('section_responses', $mySubmission?->draft_section_answers ?? []);
        $initialSectionResponses = [];
        foreach ($configuredSections as $secItem) {
            $sKey = $secItem['key'] ?? '';
            if ($sKey !== '') {
                $initialSectionResponses[$sKey] = (string) ($savedDraftSections[$sKey] ?? '');
            }
        }
    @endphp

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-7 shadow-sm" x-data="{
            text: @js(old('body_text', $mySubmission?->draft_body_text ?? '')),
            sectionResponses: @js((object) $initialSectionResponses),
            submitLabel: @js($mySubmission?->submitted_at ? 'Kirim Revisi →' : 'Kirim Tugas →'),
            extracting: false,
            submitting: false,
            extractSuccess: false,
            extractMessage: '',
            selectedFileName: '',
            get wordCount() {
                return this.text.trim() ? this.text.trim().split(/\s+/).length : 0;
            },
            get charCount() {
                return this.text.length;
            },
            sectionWordCount(key) {
                const val = (this.sectionResponses && this.sectionResponses[key]) ? String(this.sectionResponses[key]).trim() : '';
                return val ? val.split(/\s+/).length : 0;
            },
            async handleFileChange(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.selectedFileName = file.name;
                this.extracting = true;
                this.extractSuccess = false;
                this.extractMessage = '';

                const formData = new FormData();
                formData.append('document_file', file);
                formData.append('assignment_id', '{{ $assignment->id }}');
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route('assignments.extract-document') }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        if (data.section_texts && typeof data.section_texts === 'object') {
                            Object.entries(data.section_texts).forEach(([key, val]) => {
                                this.sectionResponses[key] = val || '';
                            });
                        }
                        this.text = data.text || '';
                        this.extractSuccess = true;
                        this.extractMessage = data.message || `Ekstraksi berhasil: ${data.word_count} kata (${data.char_count} karakter) diekstrak dari ${data.filename}.`;
                    } else {
                        alert(data.message || 'Gagal mengekstrak teks dari berkas.');
                    }
                } catch (e) {
                    alert('Terjadi kendala saat membaca dokumen. Anda juga dapat menyalin-tempel teks secara langsung.');
                } finally {
                    this.extracting = false;
                    event.target.value = '';
                }
            }
        }">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $assignment->isTranslation() ? 'Kirim Terjemahan (Target Text)' : ($assignment->isMilestone() ? 'Kirim Draf Milestone' : ($mySubmission?->submitted_at ? 'Kirim Revisi Tugas' : 'Kirim Jawaban Tugas')) }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Unggah berkas Word / PDF atau ketik langsung di editor WriteWise.</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold border {{ $assignment->typeBadgeColor() }}">
                    {{ $assignment->typeLabel() }}
                </span>
            </div>

            @if($assignment->due_at && $assignment->due_at->isPast())
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <div class="flex items-center gap-2 font-bold">
                        <span>⚠️ Tenggat Tugas Telah Berakhir</span>
                    </div>
                    <p class="mt-1 text-xs sm:text-sm text-amber-800">
                        Batas akhir pengumpulan draf tugas adalah {{ $assignment->due_at->format('d M Y, H:i') }} WIB. Pengiriman revisi telah ditutup oleh sistem.
                    </p>
                </div>
            @elseif($mySubmission?->submitted_at && ! $assignment->allow_revisions)
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    Tugas sudah dikirim dan dosen menutup revisi tambahan. Anda tetap dapat melihat riwayat submission dan feedback di samping.
                </div>
@else
                {{-- Kotak Ekstraksi Berkas Word/PDF --}}
                <div class="mt-4 rounded-2xl border border-teal-200 bg-teal-50/40 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-800 text-white font-bold text-xs shadow-xs">
                                📄
                            </div>
                            <div>
                                <span class="text-xs sm:text-sm font-bold text-slate-900 block">Ekstraksi Otomatis Dokumen</span>
                                <span class="text-xs text-slate-500">Mendukung berkas Microsoft Word (.docx), PDF (.pdf), atau Text (.txt)</span>
                            </div>
                        </div>

                        <div>
                            <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-teal-800 px-3.5 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-xs transition" :class="{ 'opacity-60 cursor-not-allowed': extracting }">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span x-text="extracting ? 'Mengekstrak Teks...' : 'Unggah & Ekstrak Berkas'"></span>
                                <input type="file" accept=".docx,.pdf,.txt" class="hidden" @change="handleFileChange" :disabled="extracting">
                            </label>
                        </div>
                    </div>

                    {{-- Status Banner Ekstraksi --}}
                    <div x-show="extractSuccess" x-cloak class="mt-3 rounded-xl border border-emerald-300 bg-emerald-50 p-2.5 text-xs text-emerald-900 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span>✓</span>
                            <span x-text="extractMessage"></span>
                        </div>
                        <button type="button" @click="extractSuccess = false" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
                    </div>

                    <div x-show="extracting" x-cloak class="mt-3 text-xs text-teal-800 flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-teal-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Sedang membaca dan mengekstrak teks dokumen naskah...</span>
                    </div>
                </div>

                {{-- Form Pengiriman Submisi Revisi --}}
                <form method="post" action="{{ route('assignments.revisions.store', $assignment) }}" enctype="multipart/form-data" class="mt-4 space-y-4" @submit="submitting = true">
                    @csrf

                    {{-- Input Khusus Format Milestone: Pilihan Tahap Milestone --}}
                    @if($assignment->isMilestone() && !empty($assignment->config_json['milestones']))
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700">Pilih Tahap Milestone yang Anda Kumpulkan *</label>
                            <select name="milestone_id" required class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-rose-700 focus:ring-rose-700 shadow-xs">
                                @foreach($assignment->config_json['milestones'] as $m)
                                <option value="{{ $m['id'] }}" @selected((string) old('milestone_id', $mySubmission?->draft_milestone_id) === (string) $m['id'])>{{ $m['title'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700">Tautan Artefak Digital / Presentasi (YouTube, Google Drive, GitHub, Figma)</label>
                            <input type="url" name="artifact_url" value="{{ old('artifact_url', $mySubmission?->draft_artifact_url) }}" placeholder="https://youtube.com/watch?v=... atau https://drive.google.com/..."
                                class="mt-1.5 w-full rounded-xl border-slate-300 text-sm focus:border-rose-700 focus:ring-rose-700 shadow-xs">
                            <p class="mt-1 text-xs text-slate-500">Tautan video presentasi unlisted, berkas slide seminar, atau repositori proyek.</p>
                        </div>
                    @endif

                    @if(!empty($assignment->config_json['sections']))
                        @php
                            $draftSectionAnswers = old('section_responses', $mySubmission?->draft_section_answers ?? []);
                        @endphp
                        @foreach($assignment->config_json['sections'] as $section)
                            @php
                                $sectionKey = $section['key'];
                            @endphp
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <label class="block text-xs sm:text-sm font-semibold text-slate-700">{{ $section['title'] }} @if($section['required'])<span class="text-rose-700">*</span>@endif @if($section['weight'] > 0)<span class="font-normal text-slate-500">· bobot acuan {{ $section['weight'] }}%</span>@endif</label>
                                    @if($section['response_type'] === 'long_text')
                                        <span class="text-[11px] font-mono text-slate-500" x-text="sectionWordCount('{{ $sectionKey }}') + ' kata'"></span>
                                    @endif
                                </div>
                                @if(!empty($section['cpmk_outcomes']))<p class="mt-1 text-[11px] text-indigo-800">CPMK: {{ implode(' · ', $section['cpmk_outcomes']) }}</p>@endif
                                @if($section['prompt'])<p class="mt-1 text-xs text-slate-600 whitespace-pre-line">{{ $section['prompt'] }}</p>@endif
                                @if($section['response_type'] === 'long_text')
                                    <textarea name="section_responses[{{ $sectionKey }}]" x-model="sectionResponses['{{ $sectionKey }}']" @required($section['required']) maxlength="50000" rows="7" class="mt-1.5 w-full rounded-xl border-slate-300 p-3 font-serif text-sm sm:text-base leading-relaxed text-slate-800 focus:border-teal-700 focus:ring-teal-700">{{ $draftSectionAnswers[$sectionKey] ?? '' }}</textarea>
                                @else
                                    <input name="section_responses[{{ $sectionKey }}]" x-model="sectionResponses['{{ $sectionKey }}']" @required($section['required']) type="{{ $section['response_type'] === 'numeric' ? 'number' : 'url' }}" value="{{ $draftSectionAnswers[$sectionKey] ?? '' }}" class="mt-1.5 w-full rounded-xl border-slate-300 p-3 text-sm focus:border-teal-700 focus:ring-teal-700" placeholder="{{ $section['response_type'] === 'audio_video' ? 'Tautan audio/video' : ($section['response_type'] === 'numeric' ? 'Masukkan nilai angka' : 'https://...') }}">
                                @endif
                                @error('section_responses.'.$sectionKey)<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    @else
                        <div>
                            <div class="flex items-center justify-between pb-1.5">
                                <label class="block text-xs sm:text-sm font-semibold text-slate-700">
                                    {{ $assignment->isTranslation() ? 'Teks Naskah Hasil Terjemahan (Target Text) *' : ($assignment->isMilestone() ? 'Naskah Draf Bab Proposal *' : 'Teks Naskah Esai / Tulisan Akademik *') }}
                                </label>
                                <span class="text-xs font-mono text-slate-500" x-text="wordCount + ' kata · ' + charCount + ' karakter'"></span>
                            </div>
                            <textarea maxlength="50000" name="body_text" rows="12" x-model="text" class="w-full rounded-2xl border-slate-300 p-4 font-serif text-base leading-relaxed text-slate-800 focus:border-teal-700 focus:ring-teal-700 shadow-xs" placeholder="Ketik, tempelkan draf naskah, atau gunakan tombol 'Unggah & Ekstrak Berkas' di atas..."></textarea>
                        </div>
                    @endif

                    {{-- Kolom Catatan Padanan Penerjemahan (Untuk Format Translation) --}}
                    @if($assignment->isTranslation())
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-purple-900">Catatan Padanan & Strategi Penerjemahan (Translator's Notes)</label>
                            <textarea name="notes" rows="3" placeholder="Catat alasan pemilihan padanan istilah sulit, modulasi, transposisi, atau adaptasi budaya pada terjemahan ini..."
                                class="mt-1.5 w-full rounded-xl border-purple-200 bg-purple-50/30 text-sm text-slate-800 focus:border-purple-700 focus:ring-purple-700 shadow-xs">{{ old('notes', $mySubmission?->draft_notes ?? '') }}</textarea>
                        </div>
                    @endif

                    {{-- Kolom Refleksi / Ringkasan Eksekutif (Untuk Format Milestone) --}}
                    @if($assignment->isMilestone())
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-rose-900">Catatan Refleksi / Ringkasan Pembaruan Draf</label>
                            <textarea name="notes" rows="3" placeholder="Jelaskan ringkasan perubahan dari draf bimbingan sebelumnya atau poin penting yang ingin dikonsultasikan..."
                                class="mt-1.5 w-full rounded-xl border-rose-200 bg-rose-50/30 text-sm text-slate-800 focus:border-rose-700 focus:ring-rose-700 shadow-xs">{{ old('notes', $mySubmission?->draft_notes ?? '') }}</textarea>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <span class="text-xs text-slate-500">
                            {{ !empty($assignment->config_json['sections']) ? 'Jawaban tiap bagian akan dievaluasi sesuai CPMK dan kriteria tugas.' : 'Minimal 20 karakter · Naskah akan dievaluasi sesuai CPMK & rubrik akademik kelas.' }}
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" formaction="{{ route('assignments.draft.store', $assignment) }}" formnovalidate :disabled="submitting || extracting" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 shadow transition disabled:opacity-60 disabled:cursor-not-allowed">
                                Simpan Draf
                            </button>
                            <button type="submit" :disabled="submitting || extracting" class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs sm:text-sm font-bold text-white hover:bg-slate-800 shadow transition disabled:opacity-60 disabled:cursor-not-allowed">
                                <span x-text="submitting ? 'Mengirim...' : submitLabel">{{ $mySubmission?->submitted_at ? 'Kirim Revisi' : 'Kirim Tugas' }} &rarr;</span>
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
        @include('assignments.show-student-history')
    </section>
