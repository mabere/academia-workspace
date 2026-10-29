@extends('layouts.hub')
@section('title', $assignment->title.' — WriteWise ELT')
@section('content')
<a href="{{ $assignment->course_id ? route('courses.show', ['course' => $assignment->course_id, 'tab' => 'assignments']) : (auth()->user()->isStaff() ? route('projects.show', $assignment->project) : route('dashboard')) }}" class="text-sm font-medium text-teal-800 hover:underline">← Kembali ke {{ $assignment->course_id ? 'Daftar Tugas Perkuliahan' : 'Proyek' }}</a>
<section class="mt-3 rounded-2xl border bg-white p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm text-teal-800">{{ $assignment->course ? $assignment->course->name.' ('.$assignment->course->code.')' : $assignment->project->name }} · WriteWise ELT</p>
            <h1 class="mt-2 text-2xl font-bold">{{ $assignment->title }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full {{ $assignment->status === 'published' ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }} px-3 py-1 text-xs font-semibold">{{ $assignment->status === 'published' ? 'Tugas terbit' : 'Draf dosen' }}</span>
            @if(auth()->user()->isStaff() && ($assignment->course_id ? (auth()->id() === $assignment->course->lecturer_id || auth()->user()->role === 'admin') : ($assignment->project->isOwnedBy(auth()->user()) || $assignment->project->hasEditor(auth()->user()))))
                @if($assignment->course_id)
                    <a href="{{ route('courses.assignments.edit', [$assignment->course_id, $assignment]) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Sunting
                    </a>
                @endif
                <form method="post" action="{{ $assignment->course_id ? route('courses.assignments.destroy', [$assignment->course_id, $assignment]) : route('assignments.destroy', $assignment) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini secara permanen?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                        <svg class="h-3.5 w-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>
    <p class="mt-4 whitespace-pre-line text-slate-700">{{ $assignment->instructions }}</p>
    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500"><span>Feedback {{ $assignment->feedback_level }} · {{ $assignment->feedback_language === 'id' ? 'Bahasa Indonesia' : 'English' }}</span>@if($assignment->due_at)<span>Tenggat {{ $assignment->due_at->format('d M Y, H:i') }}</span>@endif @if($assignment->currentRubric)<span>Rubrik v{{ $assignment->currentRubric->version }} · skala {{ $assignment->currentRubric->scale_min }}–{{ $assignment->currentRubric->scale_max }}</span>@endif</div>
</section>

@if($assignment->currentRubric)
    <details class="mt-4 rounded-2xl border bg-white p-5"><summary class="cursor-pointer font-semibold">Rubrik {{ $assignment->currentRubric->name }} · versi {{ $assignment->currentRubric->version }}</summary><p class="mt-2 text-sm text-slate-600">Skala {{ $assignment->currentRubric->scale_min }}–{{ $assignment->currentRubric->scale_max }}</p><ul class="mt-3 grid gap-2 sm:grid-cols-2">@foreach($assignment->currentRubric->criteria as $criterion)<li class="rounded-lg bg-slate-50 p-3 text-sm"><strong>{{ $criterion['name'] }}</strong><span class="ml-1 text-slate-500">· bobot {{ $criterion['weight'] }}</span>@if(!empty($criterion['description']))<p class="mt-1 text-slate-600">{{ $criterion['description'] }}</p>@endif</li>@endforeach</ul></details>
@endif

@if(auth()->user()->isStaff() && $assignment->project->archived_at === null)
    @if(!$assignment->course_id)
        <details class="mt-5 rounded-2xl border bg-white p-6">
            <summary class="cursor-pointer font-semibold">Sunting tugas dan penugasan mahasiswa</summary>
            <p class="mt-2 text-sm text-slate-600">Setiap penyimpanan membuat versi tugas baru. Submission yang sudah ada tetap tertaut ke versi tugas saat mahasiswa mengirimnya.</p>
            <form method="post" action="{{ route('assignments.update', $assignment) }}" class="mt-5 grid gap-4 md:grid-cols-2">@csrf @method('PATCH')
                <label class="block text-sm font-medium md:col-span-2">Judul<input name="title" required maxlength="180" value="{{ old('title', $assignment->title) }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
                <label class="block text-sm font-medium md:col-span-2">Instruksi<textarea name="instructions" required maxlength="10000" rows="4" class="mt-1 w-full rounded-lg border-slate-300">{{ old('instructions', $assignment->instructions) }}</textarea></label>
                <label class="block text-sm font-medium">Bahasa feedback<select name="feedback_language" class="mt-1 w-full rounded-lg border-slate-300"><option value="id" @selected($assignment->feedback_language === 'id')>Indonesia</option><option value="en" @selected($assignment->feedback_language === 'en')>English</option></select></label>
                <label class="block text-sm font-medium">Gaya feedback<select name="feedback_level" class="mt-1 w-full rounded-lg border-slate-300"><option value="indirect" @selected($assignment->feedback_level === 'indirect')>Indirect</option><option value="direct" @selected($assignment->feedback_level === 'direct')>Direct</option><option value="socratic" @selected($assignment->feedback_level === 'socratic')>Socratic</option></select></label>
                <label class="block text-sm font-medium">Tenggat (opsional)<input type="datetime-local" name="due_at" value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
                <label class="block text-sm font-medium">Status<select name="status" class="mt-1 w-full rounded-lg border-slate-300"><option value="draft" @selected($assignment->status === 'draft')>Draf</option><option value="published" @selected($assignment->status === 'published')>Terbit</option></select></label>
                <label class="block text-sm font-medium md:col-span-2">Email mahasiswa yang ditugaskan<textarea name="student_emails" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('student_emails', $assignment->students->pluck('email')->implode(', ')) }}</textarea><span class="mt-1 block text-xs font-normal text-slate-500">Akun mahasiswa yang dihapus dari daftar tidak lagi dapat membuka tugas ini.</span></label>
                <fieldset class="md:col-span-2"><legend class="font-semibold">Rubrik versi baru</legend><p class="mt-1 text-xs text-slate-500">Versi rubrik baru dibuat hanya jika nama, skala, atau kriteria berubah.</p>
                    @php($rubric = $assignment->currentRubric)
                    <div class="mt-3 grid gap-3 sm:grid-cols-3"><label class="text-sm font-medium sm:col-span-1">Nama rubrik<input name="rubric_name" required maxlength="120" value="{{ old('rubric_name', $rubric?->name ?? 'Rubrik menulis') }}" class="mt-1 w-full rounded-lg border-slate-300"></label><label class="text-sm font-medium">Skala minimum<input type="number" name="scale_min" required min="0" max="100" value="{{ old('scale_min', $rubric?->scale_min ?? 1) }}" class="mt-1 w-full rounded-lg border-slate-300"></label><label class="text-sm font-medium">Skala maksimum<input type="number" name="scale_max" required min="1" max="100" value="{{ old('scale_max', $rubric?->scale_max ?? 5) }}" class="mt-1 w-full rounded-lg border-slate-300"></label></div>
                    @foreach(($rubric?->criteria ?? []) as $index => $criterion)<div class="mt-3 grid gap-3 rounded-lg bg-slate-50 p-3 sm:grid-cols-12"><label class="text-sm font-medium sm:col-span-3">Kriteria<input name="criteria[{{ $index }}][name]" required maxlength="120" value="{{ old("criteria.$index.name", $criterion['name'] ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300"></label><label class="text-sm font-medium sm:col-span-7">Deskripsi<textarea name="criteria[{{ $index }}][description]" maxlength="1000" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old("criteria.$index.description", $criterion['description'] ?? '') }}</textarea></label><label class="text-sm font-medium sm:col-span-2">Bobot<input type="number" step="0.1" min="0.1" max="100" name="criteria[{{ $index }}][weight]" required value="{{ old("criteria.$index.weight", $criterion['weight'] ?? 1) }}" class="mt-1 w-full rounded-lg border-slate-300"></label></div>@endforeach
                </fieldset>
                <div class="md:col-span-2"><button class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Simpan versi tugas</button></div>
            </form>
        </details>
    @endif
    <details class="mt-4 rounded-2xl border bg-white p-5"><summary class="cursor-pointer font-semibold">Riwayat versi tugas</summary><ol class="mt-3 space-y-2">@foreach($assignment->versions as $version)<li class="rounded-lg bg-slate-50 p-3 text-sm"><span class="font-semibold">Versi {{ $version->version }}</span> · {{ $version->title }} · {{ $version->status === 'published' ? 'Terbit' : 'Draf' }} · {{ $version->created_at->format('d M Y H:i') }}</li>@endforeach</ol></details>
    @if($assignment->rubric->count() > 1)<details class="mt-4 rounded-2xl border bg-white p-5"><summary class="cursor-pointer font-semibold">Riwayat versi rubrik ({{ $assignment->rubric->count() }})</summary><ol class="mt-3 space-y-2">@foreach($assignment->rubric as $rubric)<li class="rounded-lg bg-slate-50 p-3 text-sm"><span class="font-semibold">v{{ $rubric->version }} · {{ $rubric->name }}</span> · skala {{ $rubric->scale_min }}–{{ $rubric->scale_max }} · {{ $rubric->created_at->format('d M Y H:i') }}</li>@endforeach</ol></details>@endif
@endif

@if(auth()->user()->role === 'student')
    {{-- Banner Khusus Tugas Penerjemahan Teks (Source Text Panel) --}}
    @if($assignment->isTranslation() && !empty($assignment->config_json['source_text']))
        <div class="mt-6 rounded-2xl border border-purple-200 bg-purple-50/50 p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-purple-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-700 text-white font-bold text-xs">
                        ST
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-purple-950">
                            Naskah Sumber (Source Text) — {{ $assignment->config_json['source_language'] ?? 'Bahasa Indonesia' }}
                        </h3>
                        <p class="text-[11px] text-purple-700">
                            Terjemahkan teks di bawah ke dalam <strong>{{ $assignment->config_json['target_language'] ?? 'English' }}</strong> pada editor di bawah.
                        </p>
                    </div>
                </div>
                <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-mono font-semibold text-purple-800">
                    {{ str_word_count($assignment->config_json['source_text']) }} kata
                </span>
            </div>
            <div class="mt-4 max-h-80 overflow-y-auto whitespace-pre-line rounded-xl bg-white p-5 font-serif text-sm leading-relaxed text-slate-800 border border-purple-100 shadow-2xs">
                {{ $assignment->config_json['source_text'] }}
            </div>
        </div>
    @endif

    {{-- Banner Khusus Tugas Milestone Proposal (Progress Tracker) --}}
    @if($assignment->isMilestone() && !empty($assignment->config_json['milestones']))
        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50/40 p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-rose-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-rose-950">Alur Pengumpulan Proposal Bertahap (Milestone Roadmap)</h3>
                    <p class="text-[11px] text-rose-700">Selesaikan setiap bab secara terstruktur sesuai tahapan bimbingan akademik.</p>
                </div>
                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800">
                    {{ count($assignment->config_json['milestones']) }} Tahapan
                </span>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($assignment->config_json['milestones'] as $m)
                    <div class="rounded-xl border border-rose-200 bg-white p-3.5 shadow-2xs">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-700 text-white font-bold text-[10px]">
                            {{ $m['id'] }}
                        </span>
                        <h4 class="mt-2 text-xs font-bold text-slate-900">{{ $m['title'] }}</h4>
                        @if(!empty($m['description']))
                            <p class="mt-1 text-[11px] text-slate-500 leading-snug">{{ $m['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" x-data="{
            text: @js(old('body_text', '')),
            extracting: false,
            extractSuccess: false,
            extractMessage: '',
            selectedFileName: '',
            get wordCount() {
                return this.text.trim() ? this.text.trim().split(/\s+/).length : 0;
            },
            get charCount() {
                return this.text.length;
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
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route('assignments.extract-document') }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.text = data.text;
                        this.extractSuccess = true;
                        this.extractMessage = `Ekstraksi berhasil: ${data.word_count} kata (${data.char_count} karakter) diekstrak dari ${data.filename}.`;
                    } else {
                        alert(data.message || 'Gagal mengekstrak teks dari berkas.');
                    }
                } catch (e) {
                    alert('Terjadi kendala saat membaca dokumen. Anda juga dapat menyalin-tempel teks secara langsung.');
                } finally {
                    this.extracting = false;
                }
            }
        }">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $assignment->isTranslation() ? 'Kirim Terjemahan (Target Text)' : ($assignment->isMilestone() ? 'Kirim Draf Milestone' : 'Kirim Revisi Tugas') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Unggah berkas Word / PDF atau ketik langsung di editor WriteWise.</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold border {{ $assignment->typeBadgeColor() }}">
                    {{ $assignment->typeLabel() }}
                </span>
            </div>

            @if($assignment->due_at && $assignment->due_at->isPast())
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <div class="flex items-center gap-2 font-bold">
                        <span>⚠️ Tenggat Tugas Telah Berakhir</span>
                    </div>
                    <p class="mt-1 text-xs text-amber-800">
                        Batas akhir pengumpulan draf tugas adalah {{ $assignment->due_at->format('d M Y, H:i') }} WIB. Pengiriman revisi telah ditutup oleh sistem.
                    </p>
                </div>
            @else
                {{-- Kotak Ekstraksi Berkas Word/PDF --}}
                <div class="mt-4 rounded-xl border border-teal-200 bg-teal-50/40 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-800 text-white font-bold text-xs shadow-xs">
                                📄
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Ekstraksi Otomatis Dokumen</span>
                                <span class="text-[11px] text-slate-500">Mendukung berkas Microsoft Word (.docx), PDF (.pdf), atau Text (.txt)</span>
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
                    <div x-show="extractSuccess" x-cloak class="mt-3 rounded-lg border border-emerald-300 bg-emerald-50 p-2.5 text-xs text-emerald-900 flex items-center justify-between gap-2">
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
                <form method="post" action="{{ route('assignments.revisions.store', $assignment) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf

                    {{-- Input Khusus Format Milestone: Pilihan Tahap Milestone --}}
                    @if($assignment->isMilestone() && !empty($assignment->config_json['milestones']))
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Pilih Tahap Milestone yang Anda Kumpulkan *</label>
                            <select name="milestone_id" required class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-rose-700 focus:ring-rose-700 shadow-xs">
                                @foreach($assignment->config_json['milestones'] as $m)
                                    <option value="{{ $m['id'] }}">{{ $m['title'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Tautan Artefak Digital / Presentasi (YouTube, Google Drive, GitHub, Figma)</label>
                            <input type="url" name="artifact_url" placeholder="https://youtube.com/watch?v=... atau https://drive.google.com/..."
                                class="mt-1 w-full rounded-xl border-slate-300 text-xs focus:border-rose-700 focus:ring-rose-700 shadow-xs">
                            <p class="mt-1 text-[11px] text-slate-500">Tautan video presentasi unlisted, berkas slide seminar, atau repositori proyek.</p>
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between pb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">
                                {{ $assignment->isTranslation() ? 'Teks Naskah Hasil Terjemahan (Target Text) *' : ($assignment->isMilestone() ? 'Naskah Draf Bab Proposal *' : 'Teks Naskah Esai / Tulisan Akademik *') }}
                            </label>
                            <span class="text-[11px] font-mono text-slate-500" x-text="wordCount + ' kata · ' + charCount + ' karakter'"></span>
                        </div>
                        <textarea required minlength="20" maxlength="50000" name="body_text" rows="12" x-model="text"
                            class="w-full rounded-xl border-slate-300 font-serif text-sm leading-relaxed text-slate-800 focus:border-teal-700 focus:ring-teal-700 shadow-xs"
                            placeholder="Ketik, tempelkan draf naskah, atau gunakan tombol 'Unggah & Ekstrak Berkas' di atas..."></textarea>
                    </div>

                    {{-- Kolom Catatan Padanan Penerjemahan (Untuk Format Translation) --}}
                    @if($assignment->isTranslation())
                        <div>
                            <label class="block text-xs font-semibold text-purple-900">Catatan Padanan & Strategi Penerjemahan (Translator's Notes)</label>
                            <textarea name="notes" rows="3" placeholder="Catat alasan pemilihan padanan istilah sulit, modulasi, transposisi, atau adaptasi budaya pada terjemahan ini..."
                                class="mt-1 w-full rounded-xl border-purple-200 bg-purple-50/30 text-xs text-slate-800 focus:border-purple-700 focus:ring-purple-700 shadow-xs"></textarea>
                        </div>
                    @endif

                    {{-- Kolom Refleksi / Ringkasan Eksekutif (Untuk Format Milestone) --}}
                    @if($assignment->isMilestone())
                        <div>
                            <label class="block text-xs font-semibold text-rose-900">Catatan Refleksi / Ringkasan Pembaruan Draf</label>
                            <textarea name="notes" rows="3" placeholder="Jelaskan ringkasan perubahan dari draf bimbingan sebelumnya atau poin penting yang ingin dikonsultasikan..."
                                class="mt-1 w-full rounded-xl border-rose-200 bg-rose-50/30 text-xs text-slate-800 focus:border-rose-700 focus:ring-rose-700 shadow-xs"></textarea>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <span class="text-[11px] text-slate-500">
                            Minimal 20 karakter · Naskah akan dievaluasi sesuai CPMK & rubrik akademik kelas.
                        </span>
                        <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white hover:bg-slate-800 shadow transition">
                            Kirim Sebagai Revisi Baru &rarr;
                        </button>
                    </div>
                </form>
            @endif
        </div>
        <div>
            <h2 class="text-xl font-bold">Riwayat revisi dan feedback</h2>
            @forelse($assignment->submissions as $submission)
                <div class="mt-3 space-y-3">
                    @foreach($submission->revisions as $index => $revision)
                        <article class="rounded-xl border bg-white p-5 space-y-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold">
                                    Revisi {{ $revision->revision_number }}
                                    <span class="font-normal text-slate-500">· {{ $revision->submitted_at->format('d M Y H:i') }}</span>
                                </p>
                                @if($revision->milestone_id)
                                    <span class="rounded-full bg-rose-100 text-rose-800 px-2.5 py-0.5 text-xs font-bold">
                                        {{ $revision->milestoneTitle() }}
                                    </span>
                                @endif
                            </div>

                            @if($revision->artifact_url)
                                <div class="flex items-center gap-2 rounded-lg bg-slate-50 p-2.5 border border-slate-200">
                                    <span class="text-xs font-bold text-slate-700">Tautan Artefak Digital:</span>
                                    <a href="{{ $revision->artifact_url }}" target="_blank" rel="noopener noreferrer"
                                        class="text-xs font-bold text-teal-800 underline hover:text-teal-900 truncate">
                                        {{ $revision->artifact_url }} &nearr;
                                    </a>
                                </div>
                            @endif

                            @if($revision->notes)
                                <details class="rounded-lg bg-slate-50 p-3 border border-slate-200">
                                    <summary class="cursor-pointer text-xs font-semibold text-slate-800">
                                        {{ $assignment->isTranslation() ? 'Catatan Padanan & Strategi Penerjemahan' : 'Catatan / Refleksi Mahasiswa' }}
                                    </summary>
                                    <p class="mt-1 text-xs text-slate-700 whitespace-pre-line leading-relaxed">{{ $revision->notes }}</p>
                                </details>
                            @endif

                            <details class="mt-2">
                                <summary class="cursor-pointer text-sm text-teal-800">Lihat teks revisi</summary>
                                <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $revision->body_text }}</p>
                            </details>

                            @if($index > 0)
                                @php($previousRevision = $submission->revisions[$index - 1])
                                <details class="mt-4 rounded-lg border border-slate-200 p-3">
                                    <summary class="cursor-pointer text-sm font-semibold">Bandingkan dengan revisi {{ $previousRevision->revision_number }}</summary>
                                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                                        <div class="rounded-lg bg-slate-50 p-3">
                                            <p class="mb-2 text-xs font-semibold uppercase text-slate-500">Sebelumnya · revisi {{ $previousRevision->revision_number }}</p>
                                            <p class="whitespace-pre-line text-sm">{{ $previousRevision->body_text }}</p>
                                        </div>
                                        <div class="rounded-lg bg-teal-50 p-3">
                                            <p class="mb-2 text-xs font-semibold uppercase text-teal-800">Terbaru · revisi {{ $revision->revision_number }}</p>
                                            <p class="whitespace-pre-line text-sm">{{ $revision->body_text }}</p>
                                        </div>
                                    </div>
                                </details>
                            @endif

                            @foreach($revision->feedbackReports->where('status', 'published') as $report)
                                <div class="mt-4 rounded-lg bg-teal-50 p-4">
                                    <p class="text-xs font-semibold uppercase text-teal-800">Feedback dosen</p>
                                    <p class="mt-2 whitespace-pre-line text-sm">{{ $report->feedback_json['overall_summary'] ?? '' }}</p>
                                    <p class="mt-2 text-xs text-teal-900">Diterbitkan {{ $report->published_at?->format('d M Y H:i') }}</p>
                                </div>
                            @endforeach
                        </article>
                    @endforeach
                </div>
            @empty
                <div class="mt-4 rounded-xl border border-dashed bg-white p-6 text-sm text-slate-600">
                    Belum ada revisi. Kirim tulisan pertama Anda melalui formulir.
                </div>
            @endforelse
        </div>
    </section>
@else
    <section class="mt-8"><div class="flex flex-wrap items-end justify-between gap-3"><div><h2 class="text-xl font-bold">Submission mahasiswa</h2><p class="mt-1 text-sm text-slate-600">Feedback draf hanya terlihat oleh tim pengajar sampai diterbitkan.</p></div><span class="rounded-full {{ $aiFeedbackAvailable ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700' }} px-3 py-1 text-xs">{{ $aiFeedbackAvailable ? 'Gateway AI siap' : 'Feedback AI nonaktif' }}</span></div>
        @forelse($assignment->submissions as $submission)<article class="mt-4 rounded-2xl border bg-white p-5"><h3 class="font-semibold">{{ $submission->student->name }}</h3>
            @foreach($submission->revisions as $index => $revision)<div class="mt-4 border-t pt-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium">
                            Revisi {{ $revision->revision_number }}
                            <span class="text-sm font-normal text-slate-500">· {{ $revision->submitted_at->format('d M Y H:i') }}</span>
                            @if($revision->milestone_id)
                                <span class="ml-2 rounded-full bg-rose-100 text-rose-800 px-2 py-0.5 text-xs font-bold">
                                    {{ $revision->milestoneTitle() }}
                                </span>
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Tugas v{{ $revision->assignmentVersion?->version ?? '—' }} · Rubrik v{{ $revision->rubricVersion?->version ?? '—' }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">Ditulis mahasiswa · AI tidak mengirim teks</span>
                </div>

                @if($revision->artifact_url)
                    <div class="mt-2 flex items-center gap-2 rounded-lg bg-slate-50 p-2.5 border border-slate-200">
                        <span class="text-xs font-bold text-slate-700">Tautan Artefak Digital:</span>
                        <a href="{{ $revision->artifact_url }}" target="_blank" rel="noopener noreferrer"
                            class="text-xs font-bold text-teal-800 underline hover:text-teal-900 truncate">
                            {{ $revision->artifact_url }} &nearr;
                        </a>
                    </div>
                @endif

                @if($revision->notes)
                    <details class="mt-2 rounded-lg bg-slate-50 p-3 border border-slate-200">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-800">
                            {{ $assignment->isTranslation() ? 'Catatan Padanan & Strategi Penerjemahan' : 'Catatan / Refleksi Mahasiswa' }}
                        </summary>
                        <p class="mt-1 text-xs text-slate-700 whitespace-pre-line leading-relaxed">{{ $revision->notes }}</p>
                    </details>
                @endif

                <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $revision->body_text }}</p>
                @if($index > 0) @php($previousRevision = $submission->revisions[$index - 1])<details class="mt-4 rounded-lg border p-3"><summary class="cursor-pointer text-sm font-semibold">Bandingkan revisi {{ $previousRevision->revision_number }} dan {{ $revision->revision_number }}</summary><div class="mt-3 grid gap-3 md:grid-cols-2"><div class="rounded-lg bg-slate-50 p-3"><p class="mb-2 text-xs font-semibold uppercase text-slate-500">Sebelumnya</p><p class="whitespace-pre-line text-sm">{{ $previousRevision->body_text }}</p></div><div class="rounded-lg bg-teal-50 p-3"><p class="mb-2 text-xs font-semibold uppercase text-teal-800">Terbaru</p><p class="whitespace-pre-line text-sm">{{ $revision->body_text }}</p></div></div></details>@endif
                @php($draftReports = $revision->feedbackReports->where('status', 'draft'))
                @forelse($draftReports as $report)<div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4"><p class="text-xs font-semibold uppercase text-amber-900">Draf feedback {{ $report->provider ? 'AI · perlu ditinjau' : 'dosen' }}</p><form method="post" action="{{ route('feedback.update', $report) }}" class="mt-3 space-y-2">@csrf @method('PATCH')<label class="block text-sm font-medium">Ringkasan feedback<textarea name="feedback_json[overall_summary]" required maxlength="5000" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ $report->feedback_json['overall_summary'] ?? '' }}</textarea></label><button class="rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm">Simpan suntingan</button></form><form method="post" action="{{ route('feedback.publish', $report) }}" class="mt-2">@csrf<button class="rounded-lg bg-teal-700 px-3 py-2 text-sm font-semibold text-white">Terbitkan ke mahasiswa</button></form></div>@empty<div class="mt-4 flex flex-wrap items-end gap-3"><form method="post" action="{{ route('feedback.store', $revision) }}" class="min-w-72 flex-1 space-y-2">@csrf<label class="block text-sm font-medium">Draf umpan balik dosen<textarea name="overall_summary" required maxlength="5000" rows="3" class="mt-1 w-full rounded-lg border-slate-300"></textarea></label><button class="rounded-lg border px-3 py-2 text-sm">Simpan draf manual</button></form>@if($aiFeedbackAvailable)<form method="post" action="{{ route('feedback.generate', $revision) }}">@csrf<button class="rounded-lg border border-teal-700 px-3 py-2 text-sm font-semibold text-teal-800">Buat draf AI untuk ditinjau</button></form>@endif</div>@endforelse
                @foreach($revision->feedbackReports->where('status', 'published') as $report)<div class="mt-4 rounded-xl bg-teal-50 p-4"><p class="text-xs font-semibold uppercase text-teal-800">Feedback diterbitkan · {{ $report->published_at?->format('d M Y H:i') }}</p><p class="mt-2 whitespace-pre-line text-sm">{{ $report->feedback_json['overall_summary'] ?? '' }}</p></div>@endforeach
            </div>@endforeach
        </article>@empty<div class="mt-4 rounded-xl border border-dashed bg-white p-8 text-sm text-slate-600">Belum ada submission mahasiswa.</div>@endforelse
    </section>
@endif
@endsection
