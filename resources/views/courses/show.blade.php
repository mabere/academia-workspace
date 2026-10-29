@extends('layouts.hub')
@section('title', $course->name . ' (' . $course->code . ') — Academic Hub')

@section('content')
<div class="space-y-6">
    {{-- Header Ruang Mata Kuliah --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-teal-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-teal-800 border border-teal-200">
                        {{ $course->code }} · {{ $course->class_group }}
                    </span>
                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                        {{ $course->credit_hours }} SKS
                    </span>
                    <span class="text-xs text-slate-500">
                        {{ $course->academicPeriod->name }} · Semester {{ $course->semester_level }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ $course->name }}</h1>

                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-600 pt-1">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>{{ $course->studyProgram->name }} ({{ $course->studyProgram->faculty->name ?? 'Fakultas' }})</span>
                    </div>
                    <span>•</span>
                    <div class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium text-slate-700">{{ $course->scheduleLabel() }}</span>
                    </div>
                    <span>•</span>
                    <div class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Dosen Pengampu: <strong>{{ $course->lecturer->name }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->user()->isStaff())
                    <a href="{{ route('courses.sessions.print', $course) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Jurnal Perkuliahan
                    </a>
                @endif

                @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || auth()->user()->role === 'admin'))
                    <a href="{{ route('courses.edit', $course) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit MK
                    </a>
                @endif

                <a href="{{ route('courses.index') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    &larr; Kembali
                </a>
            </div>
        </div>

        @if($course->description || $course->syllabus)
            <div class="mt-5 border-t border-slate-100 pt-4 grid gap-4 md:grid-cols-2 text-xs">
                @if($course->description)
                    <div>
                        <span class="font-bold uppercase tracking-wider text-slate-500">Deskripsi Mata Kuliah:</span>
                        <p class="mt-1 leading-relaxed text-slate-700">{{ $course->description }}</p>
                    </div>
                @endif
                @if($course->syllabus)
                    <div>
                        <span class="font-bold uppercase tracking-wider text-slate-500">Capaian Pembelajaran (CPMK):</span>
                        <p class="mt-1 leading-relaxed text-slate-700">{{ $course->syllabus }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Navigasi Tab Interaktif --}}
    <div x-data="{
        tab: '{{ in_array(request('tab'), ['materials', 'sessions', 'assignments', 'students'], true) ? request('tab') : 'materials' }}',
        docModalOpen: false,
        docModalTitle: '',
        docModalUrl: '',
        docDownloadUrl: '',
        openDocPreview(title, viewerUrl, dlUrl) {
            this.docModalTitle = title;
            this.docModalUrl = viewerUrl;
            this.docDownloadUrl = dlUrl;
            this.docModalOpen = true;
        }
    }" class="space-y-6">
        <div class="border-b border-slate-200">
            <nav class="-mb-px flex gap-6 text-sm font-semibold">
                <button type="button" @click="tab = 'materials'" :class="tab === 'materials' ? 'border-teal-700 text-teal-800 border-b-2' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Bahan Ajar Multi-Format
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $materials->count() }}</span>
                </button>

                <button type="button" @click="tab = 'sessions'" :class="tab === 'sessions' ? 'border-teal-700 text-teal-800 border-b-2' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Jurnal Perkuliahan (16 Pertemuan)
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $sessions->count() }}/16</span>
                </button>

                <button type="button" @click="tab = 'assignments'" :class="tab === 'assignments' ? 'border-teal-700 text-teal-800 border-b-2' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ auth()->user()->isStaff() ? 'Tugas & Penilaian' : 'Tugas Saya' }}
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $assignments->count() }}</span>
                </button>

                <button type="button" @click="tab = 'students'" :class="tab === 'students' ? 'border-teal-700 text-teal-800 border-b-2' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ auth()->user()->isStaff() ? 'Peserta Kelas' : 'Teman Sekelas' }}
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $students->count() }}</span>
                </button>
            </nav>
        </div>

        {{-- TAB 1: BAHAN AJAR MULTI-FORMAT --}}
        <div x-show="tab === 'materials'" class="space-y-6">
            {{-- Form Unggah Bahan Ajar (Untuk Dosen) --}}
            @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || auth()->user()->role === 'admin'))
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-slate-800 list-none">
                        <span class="inline-flex items-center gap-2 text-sm text-teal-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Unggah Bahan Ajar Baru (Mendukung PDF, DOCX, Audio, Video, URL, Teks)
                        </span>
                        <span class="text-xs text-slate-400 group-open:rotate-180 transition">&darr;</span>
                    </summary>

                    <form method="post" action="{{ route('courses.materials.store', $course) }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-slate-100 pt-5" x-data="{ mType: 'document' }">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700">Judul Bahan Ajar *</label>
                                <input type="text" name="title" required placeholder="Contoh: Slide Pertemuan 3 - Essay Organization" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Pertemuan Ke</label>
                                <select name="session_number" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                                    <option value="">Materi Umum / Silabus RPS</option>
                                    @for($i = 1; $i <= 16; $i++)
                                        <option value="{{ $i }}">Pertemuan Ke-{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Tipe Format Bahan Ajar *</label>
                            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-6">
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2 text-xs font-semibold" :class="mType === 'document' ? 'border-teal-700 bg-teal-50 text-teal-900' : 'border-slate-200 text-slate-600'">
                                    <input type="radio" name="material_type" value="document" x-model="mType" class="sr-only">
                                    📄 Dokumen
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2 text-xs font-semibold" :class="mType === 'audio' ? 'border-teal-700 bg-teal-50 text-teal-900' : 'border-slate-200 text-slate-600'">
                                    <input type="radio" name="material_type" value="audio" x-model="mType" class="sr-only">
                                    🎧 Audio
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2 text-xs font-semibold" :class="mType === 'video' ? 'border-teal-700 bg-teal-50 text-teal-900' : 'border-slate-200 text-slate-600'">
                                    <input type="radio" name="material_type" value="video" x-model="mType" class="sr-only">
                                    🎥 Video
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2 text-xs font-semibold" :class="mType === 'link' ? 'border-teal-700 bg-teal-50 text-teal-900' : 'border-slate-200 text-slate-600'">
                                    <input type="radio" name="material_type" value="link" x-model="mType" class="sr-only">
                                    🔗 Tautan Luar
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2 text-xs font-semibold" :class="mType === 'text' ? 'border-teal-700 bg-teal-50 text-teal-900' : 'border-slate-200 text-slate-600'">
                                    <input type="radio" name="material_type" value="text" x-model="mType" class="sr-only">
                                    📝 Catatan Teks
                                </label>
                                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-2 text-xs font-semibold" :class="mType === 'archive' ? 'border-teal-700 bg-teal-50 text-teal-900' : 'border-slate-200 text-slate-600'">
                                    <input type="radio" name="material_type" value="archive" x-model="mType" class="sr-only">
                                    📦 Paket ZIP
                                </label>
                            </div>
                        </div>

                        {{-- Input Berkas (Untuk Dokumen, Audio, Video, Arsip) --}}
                        <div x-show="['document', 'audio', 'video', 'archive'].includes(mType)">
                            <label class="block text-xs font-semibold text-slate-700">Pilih Berkas Unggahan (Maksimal 100 MB)</label>
                            <input type="file" name="file" class="mt-1 block w-full text-xs text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                            <p class="mt-1 text-[11px] text-slate-400">
                                Menerima format: PDF, DOCX, PPTX, XLSX, TXT, MP3, WAV, M4A, OGG, MP4, WebM, ZIP.
                            </p>
                        </div>

                        {{-- Input Tautan Luar --}}
                        <div x-show="mType === 'link'">
                            <label class="block text-xs font-semibold text-slate-700">Tautan / URL Eksternal *</label>
                            <input type="url" name="external_url" placeholder="https://www.youtube.com/watch?v=... atau Google Drive" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                            <p class="mt-1 text-[11px] text-slate-400">Jika berupa link YouTube, video akan disematkan secara otomatis di halaman kuliah.</p>
                        </div>

                        {{-- Input Teks Langsung --}}
                        <div x-show="mType === 'text'">
                            <label class="block text-xs font-semibold text-slate-700">Isi Catatan / Artikel Materi Kuliah *</label>
                            <textarea name="content_text" rows="5" placeholder="Tuliskan catatan, rangkuman teori, atau instruksi materi kuliah..." class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Petunjuk Pembelajaran / Catatan Mahasiswa</label>
                            <input type="text" name="description" placeholder="Misal: Pelajari sebelum pertemuan dimulai untuk diskusi kelompok." class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                                Simpan & Terbitkan Bahan Ajar
                            </button>
                        </div>
                    </form>
                </details>
            @endif

            {{-- Daftar Bahan Ajar Multi-Format --}}
            @if($materials->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <p class="text-sm font-semibold text-slate-700">Belum Ada Bahan Ajar</p>
                    <p class="mt-1 text-xs text-slate-500">Dosen belum mengunggah modul atau materi untuk kelas ini.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($materials as $material)
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
                                            {{ $material->isAudio() ? 'bg-purple-100 text-purple-800' :
                                               ($material->isVideo() ? 'bg-rose-100 text-rose-800' :
                                               ($material->isLink() ? 'bg-blue-100 text-blue-800' :
                                               ($material->isText() ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800'))) }}">
                                            {{ $material->typeLabel() }}
                                        </span>
                                        @if($material->session_number)
                                            <span class="text-xs font-semibold text-teal-800">Pertemuan Ke-{{ $material->session_number }}</span>
                                        @else
                                            <span class="text-xs font-semibold text-slate-500">Materi Umum / RPS</span>
                                        @endif
                                        <span class="text-xs text-slate-400">· Diunggah {{ $material->created_at->diffForHumans() }}</span>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900">{{ $material->title }}</h3>
                                    @if($material->description)
                                        <p class="text-xs text-slate-600">{{ $material->description }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    @if($material->file_path)
                                        @if($material->isPdf())
                                            <a href="{{ route('courses.materials.stream', [$course, $material]) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-teal-800 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-700 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Buka / Baca PDF</span>
                                            </a>
                                        @elseif($material->isWordDocument())
                                            <button type="button"
                                                @click="openDocPreview('{{ addslashes($material->title) }}', '{{ $material->getGoogleDocsViewerUrl($course) }}', '{{ route('courses.materials.download', [$course, $material]) }}')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-800 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span>Lihat Dokumen (Word)</span>
                                            </button>
                                        @elseif($material->isPowerpoint())
                                            <button type="button"
                                                @click="openDocPreview('{{ addslashes($material->title) }}', '{{ $material->getGoogleDocsViewerUrl($course) }}', '{{ route('courses.materials.download', [$course, $material]) }}')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-amber-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-800 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                                </svg>
                                                <span>Lihat Slide (PPT)</span>
                                            </button>
                                        @endif

                                        <a href="{{ route('courses.materials.download', [$course, $material]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Unduh ({{ $material->formattedFileSize() }})
                                        </a>
                                    @elseif($material->external_url)
                                        <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-800 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-700">
                                            Buka Tautan Eksternal &rarr;
                                        </a>
                                    @endif

                                    @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || auth()->user()->role === 'admin'))
                                        <form method="post" action="{{ route('courses.materials.destroy', [$course, $material]) }}" onsubmit="return confirm('Hapus materi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Hapus bahan ajar">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- PLAYER AUDIO MULTIMEDIA --}}
                            @if($material->isAudio() && $material->file_path)
                                <div class="mt-4 rounded-xl border border-purple-100 bg-purple-50/50 p-3">
                                    <p class="text-[11px] font-semibold text-purple-900 mb-1.5">Putar Audio Perkuliahan Langsung:</p>
                                    <audio controls class="w-full h-8">
                                        <source src="{{ route('courses.materials.stream', [$course, $material]) }}" type="{{ $material->mime_type ?: 'audio/mpeg' }}">
                                        Browser Anda tidak mendukung pemutar audio langsung.
                                    </audio>
                                </div>
                            @endif

                            {{-- PLAYER VIDEO MULTIMEDIA --}}
                            @if($material->isVideo() && $material->file_path)
                                <div class="mt-4 rounded-xl border border-rose-100 bg-black/5 p-3">
                                    <video controls class="w-full max-h-80 rounded-lg">
                                        <source src="{{ route('courses.materials.stream', [$course, $material]) }}" type="{{ $material->mime_type ?: 'video/mp4' }}">
                                        Browser Anda tidak mendukung pemutar video langsung.
                                    </video>
                                </div>
                            @elseif($material->isLink() && $material->getYouTubeEmbedUrl())
                                <div class="mt-4 aspect-video w-full max-w-2xl overflow-hidden rounded-xl shadow-sm">
                                    <iframe class="h-full w-full" src="{{ $material->getYouTubeEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            @endif

                            {{-- KONTEN TEKS / ARTIKEL BACAAN --}}
                            @if($material->isText() && $material->content_text)
                                <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50/40 p-4 text-xs leading-relaxed text-slate-800 whitespace-pre-wrap font-sans">
                                    {{ $material->content_text }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- MODAL PRATINJAU DOKUMEN GOOGLE DOCS --}}
            <div x-show="docModalOpen"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs"
                @keydown.escape.window="docModalOpen = false">
                <div class="relative w-full max-w-5xl rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="docModalOpen = false">
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-3.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="rounded-lg bg-blue-100 p-1.5 text-blue-800">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 truncate" x-text="docModalTitle"></h3>
                                <p class="text-[11px] text-slate-500">Pratinjau Dokumen Online (Google Docs Viewer)</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="docModalUrl" target="_blank"
                                class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                <span>Buka Tab Penuh &rarr;</span>
                            </a>
                            <a :href="docDownloadUrl"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                <span>Unduh Berkas</span>
                            </a>
                            <button type="button" @click="docModalOpen = false"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body Modal --}}
                    <div class="flex-1 overflow-hidden p-3 bg-slate-100 flex flex-col">
                        @if(app()->environment('local') || str_contains(request()->getHost(), '.test') || str_contains(request()->getHost(), 'localhost'))
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 mb-2">
                                <strong>Catatan Pengujian Lokal:</strong> Google Docs Viewer membutuhkan domain yang dapat diakses publik dari internet (seperti pada VPS production <code>https://academia-workspace.cloud</code>). Jika pratinjau Google kosong di localhost, silakan klik tombol <strong>Unduh Berkas</strong> di kanan atas.
                            </div>
                        @endif
                        <iframe :src="docModalUrl" class="w-full flex-1 min-h-[60vh] max-h-[72vh] rounded-xl border border-slate-200 bg-white shadow-inner" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: JURNAL PERKULIAHAN (16 PERTEMUAN) --}}
        <div x-show="tab === 'sessions'" class="space-y-6">
            {{-- Form Catat Jurnal (Untuk Dosen) --}}
            @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || auth()->user()->role === 'admin'))
                <details class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-slate-800 list-none">
                        <span class="inline-flex items-center gap-2 text-sm text-teal-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Catat Laporan Jurnal Perkuliahan Pertemuan Baru
                        </span>
                        <span class="text-xs text-slate-400 group-open:rotate-180 transition">&darr;</span>
                    </summary>

                    <form method="post" action="{{ route('courses.sessions.store', $course) }}" class="mt-5 space-y-4 border-t border-slate-100 pt-5">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Pertemuan Ke (1-16) *</label>
                                <select name="session_number" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                    @for($i = 1; $i <= 16; $i++)
                                        <option value="{{ $i }}" @selected($sessions->count() + 1 === $i)>Pertemuan Ke-{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Tanggal Pelaksanaan *</label>
                                <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Jumlah Mahasiswa Hadir *</label>
                                <input type="number" name="attendance_count" required value="{{ $students->count() ?: 1 }}" min="0" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Total Mahasiswa Kelas *</label>
                                <input type="number" name="total_students" required value="{{ $students->count() ?: 1 }}" min="1" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700">Pokok Bahasan / Materi Perkuliahan *</label>
                                <input type="text" name="topic" required placeholder="Contoh: Analisis Struktur Esai Argumentatif & Perumusan Tesis" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Metode Pembelajaran</label>
                                <input type="text" name="teaching_method" placeholder="Contoh: Ceramah, Diskusi, PBL" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Catatan Refleksi Dosen / Evaluasi Kelas</label>
                            <textarea name="notes" rows="2" placeholder="Catatan keaktifan mahasiswa, kendala, atau penugasan mandiri..." class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                                Simpan Jurnal Perkuliahan
                            </button>
                        </div>
                    </form>
                </details>
            @endif

            {{-- Rekapitulasi Tabel 16 Pertemuan --}}
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Rekapitulasi Jurnal 16 Pertemuan Sesi Perkuliahan</span>
                    <a href="{{ route('courses.sessions.print', $course) }}" target="_blank" class="text-xs font-semibold text-teal-800 hover:text-teal-900">
                        Cetak Laporan Format A4 &rarr;
                    </a>
                </div>

                @if($sessions->isEmpty())
                    <div class="p-8 text-center text-sm text-slate-500">
                        Belum ada riwayat pertemuan perkuliahan yang dicatat.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">Sesi</th>
                                    <th class="py-3 px-4 w-28">Tanggal</th>
                                    <th class="py-3 px-4">Pokok Bahasan / Materi</th>
                                    <th class="py-3 px-4 w-40">Metode</th>
                                    <th class="py-3 px-4 w-24 text-center">Kehadiran</th>
                                    <th class="py-3 px-4">Catatan Dosen</th>
                                    @if(auth()->user()->isStaff())
                                        <th class="py-3 px-4 w-16 text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($sessions as $session)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-4 text-center font-bold text-teal-800">
                                            #{{ $session->session_number }}
                                        </td>
                                        <td class="py-3 px-4 font-medium text-slate-600">
                                            {{ $session->date?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 font-bold text-slate-900">
                                            {{ $session->topic }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">
                                            {{ $session->teaching_method ?: 'Ceramah & Diskusi' }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-200">
                                                {{ $session->attendance_count }}/{{ $session->total_students }} ({{ $session->attendancePercentage() }}%)
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 italic">
                                            {{ $session->notes ?: '-' }}
                                        </td>
                                        @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || auth()->user()->role === 'admin'))
                                            <td class="py-3 px-4 text-center">
                                                <form method="post" action="{{ route('courses.sessions.destroy', [$course, $session]) }}" onsubmit="return confirm('Hapus jurnal pertemuan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-slate-400 hover:text-red-600 transition" title="Hapus sesi">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB 3: TUGAS MAHASISWA & PENILAIAN --}}
        <div x-show="tab === 'assignments'" class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        {{ auth()->user()->isStaff() ? 'Penugasan Perkuliahan & Evaluasi Nilai' : 'Tugas Perkuliahan Saya' }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        {{ auth()->user()->isStaff()
                            ? 'Tugas penulisan akademik, submisi draf mahasiswa, penilaian berkriteria, dan rekapitulasi nilai.'
                            : 'Kerjakan tugas akademik, kumpulkan draf via WriteWise AI, dan pantau umpan balik serta nilai dari dosen pengampu.' }}
                    </p>
                </div>
                @if(auth()->user()->isStaff())
                    <div class="flex items-center gap-2">
                        <a href="{{ route('courses.grades.export', $course) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-800 shadow-sm hover:bg-emerald-100 transition">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ekspor Excel / CSV
                        </a>
                        <a href="{{ route('courses.grades.print', $course) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak Rekap Nilai Sah A4
                        </a>
                    </div>
                @endif
            </div>

            {{-- Form Terbitkan Tugas Baru (Dosen Pengampu) --}}
            @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || auth()->user()->role === 'admin'))
                <details class="group rounded-2xl border border-teal-200 bg-teal-50/40 p-5 transition">
                    <summary class="flex cursor-pointer items-center justify-between font-bold text-teal-900">
                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Terbitkan Tugas Perkuliahan Baru (WriteWise AI)
                        </span>
                        <span class="rounded-lg bg-teal-700 px-3 py-1 text-xs text-white group-open:bg-teal-800">
                            + Buka Formulir
                        </span>
                    </summary>

                    <form method="post" action="{{ route('courses.assignments.store', $course) }}" class="mt-4 space-y-4 border-t border-teal-200 pt-4">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700">Judul Tugas Perkuliahan *</label>
                                <input type="text" name="title" required placeholder="Contoh: Esai Kritis: Perbandingan Teori Pembelajaran Bahasa" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Batas Waktu Pengumpulan (Deadline)</label>
                                <input type="datetime-local" name="due_at" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Nama Rubrik Penilaian</label>
                                <input type="text" name="rubric_name" value="Rubrik Analitik Penulisan Akademik" placeholder="Rubrik Penilaian" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Bahasa Umpan Balik AI</label>
                                <select name="feedback_language" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                                    <option value="id">Bahasa Indonesia</option>
                                    <option value="en">English (Academic)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Tingkat Intervensi Koreksi</label>
                                <select name="feedback_level" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700">
                                    <option value="indirect">Indirect (Koreksi Panduan/Pertanyaan Kritis)</option>
                                    <option value="balanced">Balanced (Seimbang & Rekomendasi)</option>
                                    <option value="direct">Direct (Perbaikan Langsung)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Petunjuk Pengerjaan & Kriteria Tugas *</label>
                            <textarea name="instructions" rows="4" required placeholder="Jelaskan topik penulisan, sistematika bab, jumlah kata minimum/maksimum, dan format referensi..." class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-700 focus:ring-teal-700"></textarea>
                            <p class="mt-1 text-[11px] text-slate-500">Sistem secara otomatis mendaftarkan seluruh mahasiswa kelas ini ke tugas dan mengaitkannya ke wadah Proyek Tri Dharma Pengajaran.</p>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                                Terbitkan Tugas ke Seluruh Mahasiswa
                            </button>
                        </div>
                    </form>
                </details>
            @endif

            @if($assignments->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <p class="text-sm font-semibold text-slate-700">Belum Ada Tugas Perkuliahan</p>
                    <p class="mt-1 text-xs text-slate-500">Tugas yang dibuat pada kelas ini akan muncul di sini untuk submisi mahasiswa.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($assignments as $assignment)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:border-slate-300 transition">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-900">
                                            <a href="{{ route('assignments.show', $assignment) }}" class="hover:text-teal-800">
                                                {{ $assignment->title }}
                                            </a>
                                        </h3>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                            {{ $assignment->status }}
                                        </span>
                                    </div>
                                    @if($assignment->due_at)
                                        <p class="mt-1 text-xs text-amber-700 font-medium">
                                            Batas Waktu: {{ $assignment->due_at->translatedFormat('d F Y, H:i') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('assignments.show', $assignment) }}" class="inline-flex items-center gap-1 text-xs font-bold text-teal-800 hover:text-teal-900">
                                        Buka di WriteWise &rarr;
                                    </a>
                                </div>
                            </div>

                            <p class="mt-3 text-xs text-slate-600 leading-relaxed">{{ $assignment->instructions }}</p>

                            @if(auth()->user()->isStaff())
                                {{-- Submisi Mahasiswa & Input Penilaian Cepat (Khusus Dosen/Staf) --}}
                                <div class="mt-5 border-t border-slate-100 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                            Submisi Mahasiswa Kelas ({{ $assignment->submissions->count() }}/{{ $students->count() }})
                                        </h4>
                                    </div>

                                    @if($assignment->submissions->isEmpty())
                                        <p class="text-xs text-slate-400 italic">Belum ada mahasiswa yang mengumpulkan draf tugas ini.</p>
                                    @else
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-xs text-slate-700">
                                                <thead class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-slate-600">
                                                    <tr>
                                                        <th class="py-2 px-3">Mahasiswa</th>
                                                        <th class="py-2 px-3 w-28 text-center">Status</th>
                                                        <th class="py-2 px-3 w-24 text-center">Nilai (0-100)</th>
                                                        <th class="py-2 px-3 w-20 text-center">Mutu</th>
                                                        @if(auth()->id() === $course->lecturer_id || auth()->user()->role === 'admin')
                                                            <th class="py-2 px-3">Form Penilaian Dosen</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach($assignment->submissions as $sub)
                                                        <tr class="hover:bg-slate-50/70 transition">
                                                            <td class="py-2.5 px-3">
                                                                <div class="font-bold text-slate-900">{{ $sub->student->name }}</div>
                                                                <div class="text-[11px] text-slate-400">{{ $sub->student->email }}</div>
                                                            </td>
                                                            <td class="py-2.5 px-3 text-center">
                                                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold
                                                                    {{ $sub->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                                    {{ $sub->status === 'graded' ? 'Dinilai' : 'Draf/Terkirim' }}
                                                                </span>
                                                            </td>
                                                            <td class="py-2.5 px-3 text-center font-bold text-teal-800 text-sm">
                                                                {{ $sub->score !== null ? number_format($sub->score, 1) : '-' }}
                                                            </td>
                                                            <td class="py-2.5 px-3 text-center font-bold text-slate-700">
                                                                {{ $sub->letterGrade() }}
                                                            </td>
                                                            @if(auth()->id() === $course->lecturer_id || auth()->user()->role === 'admin')
                                                                <td class="py-2.5 px-3">
                                                                    <form method="post" action="{{ route('courses.assignments.grade', [$course, $assignment, $sub]) }}" class="flex items-center gap-2">
                                                                        @csrf
                                                                        <input type="number" step="0.1" min="0" max="100" name="score" required value="{{ $sub->score }}" placeholder="Nilai" class="w-20 rounded border-slate-300 py-1 text-xs text-center font-bold">
                                                                        <input type="text" name="feedback_notes" placeholder="Catatan evaluasi singkat..." class="w-48 sm:w-64 rounded border-slate-300 py-1 text-xs">
                                                                        <button type="submit" class="rounded bg-teal-800 px-3 py-1 text-xs font-bold text-white hover:bg-teal-700 transition">
                                                                            Simpan
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Status Tugas Saya (Khusus Mahasiswa) --}}
                                @php
                                    $mySub = $assignment->submissions->firstWhere('student_id', auth()->id());
                                @endphp
                                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Status Pengumpulan Saya</span>
                                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                                @if($mySub)
                                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold
                                                        {{ $mySub->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                        {{ $mySub->status === 'graded' ? 'Sudah Dinilai Dosen' : 'Draf Terkumpul' }}
                                                    </span>
                                                    @if($mySub->score !== null)
                                                        <span class="font-bold text-teal-800 text-sm">
                                                            Nilai: {{ number_format($mySub->score, 1) }} (Mutu: {{ $mySub->letterGrade() }})
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-slate-500 italic">Sedang dalam proses evaluasi</span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                                                        Belum Mengumpulkan Draf
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <a href="{{ route('assignments.show', $assignment) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-800 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition">
                                            @if($mySub)
                                                <span>Buka Draf & Umpan Balik di WriteWise &rarr;</span>
                                            @else
                                                <span>Kerjakan Tugas di WriteWise &rarr;</span>
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- MODAL PRATINJAU DOKUMEN GOOGLE DOCS --}}
            <div x-show="docModalOpen"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs"
                @keydown.escape.window="docModalOpen = false">
                <div class="relative w-full max-w-5xl rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]"
                    @click.away="docModalOpen = false">
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-3.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="rounded-lg bg-blue-100 p-1.5 text-blue-800">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 truncate" x-text="docModalTitle"></h3>
                                <p class="text-[11px] text-slate-500">Pratinjau Dokumen Online (Google Docs Viewer)</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="docModalUrl" target="_blank"
                                class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                <span>Buka Tab Penuh &rarr;</span>
                            </a>
                            <a :href="docDownloadUrl"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition">
                                <span>Unduh Berkas</span>
                            </a>
                            <button type="button" @click="docModalOpen = false"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body Modal --}}
                    <div class="flex-1 overflow-hidden p-3 bg-slate-100 flex flex-col">
                        @if(app()->environment('local') || str_contains(request()->getHost(), '.test') || str_contains(request()->getHost(), 'localhost'))
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 mb-2">
                                <strong>Catatan Pengujian Lokal:</strong> Google Docs Viewer membutuhkan domain yang dapat diakses publik dari internet (seperti pada VPS production <code>https://academia-workspace.cloud</code>). Jika pratinjau Google kosong di localhost, silakan klik tombol <strong>Unduh Berkas</strong> di kanan atas.
                            </div>
                        @endif
                        <iframe :src="docModalUrl" class="w-full flex-1 min-h-[60vh] max-h-[72vh] rounded-xl border border-slate-200 bg-white shadow-inner" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 4: PESERTA KELAS (MAHASISWA) --}}
        <div x-show="tab === 'students'" class="space-y-6">
            @php
                $canManageStudents = auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true));
            @endphp

            @if($canManageStudents)
                <div class="rounded-2xl border border-teal-200 bg-teal-50/40 p-5 shadow-sm" x-data="{ enrollMode: '{{ ($availableStudents ?? collect())->isNotEmpty() ? 'directory' : 'paste' }}' }">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-teal-200/70 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-teal-950">Assign / Tambah Mahasiswa ke Kelas Ini</h2>
                            <p class="text-xs text-teal-800">Pilih dari direktori mahasiswa atau tempel daftar NIM & Nama langsung dari absen kelas.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="enrollMode = 'directory'"
                                :class="enrollMode === 'directory' ? 'bg-teal-800 text-white' : 'bg-white text-slate-700 border border-slate-300'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                Pilih dari Direktori ({{ ($availableStudents ?? collect())->count() }})
                            </button>
                            <button type="button" @click="enrollMode = 'paste'"
                                :class="enrollMode === 'paste' ? 'bg-teal-800 text-white' : 'bg-white text-slate-700 border border-slate-300'"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                + Input / Paste Mahasiswa Baru
                            </button>
                            <a href="{{ route('students.index', ['course_id' => $course->id]) }}"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                Direktori Lengkap &rarr;
                            </a>
                        </div>
                    </div>

                    <form method="post" action="{{ route('courses.students.enroll', $course) }}" class="mt-4 space-y-4">
                        @csrf

                        {{-- Mode 1: Pilih dari Direktori Mahasiswa --}}
                        <div x-show="enrollMode === 'directory'" class="space-y-3">
                            @if(($availableStudents ?? collect())->isEmpty())
                                <div class="rounded-xl border border-slate-200 bg-white p-4 text-center text-xs text-slate-500">
                                    Seluruh mahasiswa di direktori sudah terdaftar di kelas ini, atau direktori masih kosong. Gunakan tab <strong>+ Input / Paste Mahasiswa Baru</strong> di samping.
                                </div>
                            @else
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-700">Centang mahasiswa yang memprogram mata kuliah ini:</span>
                                    <button type="button"
                                        onclick="document.querySelectorAll('.dir-student-checkbox').forEach(cb => cb.checked = true)"
                                        class="font-semibold text-teal-800 hover:underline">
                                        Pilih Semua
                                    </button>
                                </div>
                                <div class="max-h-60 overflow-y-auto grid gap-2 sm:grid-cols-2 lg:grid-cols-3 rounded-xl border border-slate-200 bg-white p-3">
                                    @foreach($availableStudents as $avail)
                                        <label class="flex items-start gap-2.5 rounded-lg border border-slate-100 p-2 text-xs cursor-pointer hover:bg-teal-50/50 hover:border-teal-300 transition">
                                            <input type="checkbox" name="student_ids[]" value="{{ $avail->id }}"
                                                class="dir-student-checkbox mt-0.5 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                            <span class="min-w-0">
                                                <span class="block font-bold text-slate-900 truncate">{{ $avail->name }}</span>
                                                <span class="block font-mono text-[11px] text-slate-500">
                                                    NIM: {{ $avail->nim ?: '-' }}
                                                    @if($avail->studyProgram)
                                                        &middot; {{ $avail->studyProgram->code }}
                                                    @endif
                                                    @if($avail->cohort_year)
                                                        ({{ $avail->cohort_year }})
                                                    @endif
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Mode 2: Input / Paste Cepat --}}
                        <div x-show="enrollMode === 'paste'" class="space-y-3">
                            <div class="grid gap-3 sm:grid-cols-4">
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-slate-800">
                                        Daftar Mahasiswa Baru / Tambahan (<code class="rounded bg-white px-1.5 py-0.5 text-teal-900">NIM ; Nama Lengkap ; Email(opsional)</code>)
                                    </label>
                                    <textarea name="raw_list" rows="4"
                                        placeholder="240105010 ; Andi Saputra&#10;240105011 ; Nadia Putri ; nadia@student.ac.id"
                                        class="mt-1 w-full rounded-xl border-slate-300 bg-white font-mono text-xs"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-800">Tahun Angkatan</label>
                                    <input type="number" name="cohort_year" min="2000" max="2099" value="{{ now()->year }}"
                                        class="mt-1 w-full rounded-lg border-slate-300 bg-white text-xs">
                                    <p class="mt-2 text-[11px] text-slate-600 leading-relaxed">
                                        Mahasiswa otomatis terdaftar ke Prodi <strong>{{ $course->studyProgram->name }}</strong> dan di-assign ke seluruh tugas kelas ini.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-teal-800 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-900 transition">
                                Assign Mahasiswa ke Kelas
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @php
                $myEnrollment = $students->firstWhere('student_id', auth()->id());
            @endphp

            @if(!auth()->user()->isStaff() && $myEnrollment)
                <div class="rounded-2xl border border-teal-200 bg-teal-50/60 p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-teal-800">Rekap Capaian Akademik Saya</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-0.5">{{ auth()->user()->name }} ({{ auth()->user()->nim ?: $myEnrollment->nim ?: '-' }})</h3>
                            <p class="text-xs text-slate-500">Nilai akhir dan kehadiran dihitung berdasarkan jurnal perkuliahan dan tugas kelas ini.</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Presensi Kehadiran</span>
                                <span class="text-lg font-bold text-slate-900">{{ number_format($myEnrollment->attendance_rate, 0) }}%</span>
                            </div>
                            <div class="text-center border-l border-teal-200 pl-6">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Nilai Akhir</span>
                                <span class="text-lg font-bold text-teal-800">{{ $myEnrollment->final_grade ? number_format($myEnrollment->final_grade, 1) : '-' }}</span>
                            </div>
                            <div class="text-center border-l border-teal-200 pl-6">
                                <span class="block text-[10px] font-bold uppercase text-slate-500">Huruf Mutu</span>
                                <span class="inline-flex rounded-full bg-teal-100 px-3 py-1 text-sm font-bold text-teal-900">
                                    {{ $myEnrollment->letter_grade ?: 'Belum Ada' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">
                        {{ auth()->user()->isStaff() ? 'Daftar Mahasiswa Terdaftar' : 'Teman Sekelas' }} ({{ $students->count() }} Mahasiswa)
                    </span>
                    @if(auth()->user()->isStaff())
                        <div class="flex items-center gap-3">
                            <a href="{{ route('courses.grades.export', $course) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-800 hover:text-emerald-900">
                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Ekspor Excel / CSV</span>
                            </a>
                            <span class="text-slate-300">·</span>
                            <a href="{{ route('courses.grades.print', $course) }}" target="_blank" class="text-xs font-semibold text-teal-800 hover:text-teal-900">
                                Cetak Rekap Nilai Sah A4 &rarr;
                            </a>
                        </div>
                    @endif
                </div>

                @if($students->isEmpty())
                    <div class="p-8 text-center text-sm text-slate-500">Belum ada mahasiswa yang terdaftar di kelas ini.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">No</th>
                                    <th class="py-3 px-4 w-36">NIM</th>
                                    <th class="py-3 px-4">Nama & Prodi Mahasiswa</th>
                                    @if(auth()->user()->isStaff())
                                        <th class="py-3 px-4 w-44 text-center">Kehadiran (%)</th>
                                        <th class="py-3 px-4 w-24 text-center">Nilai Akhir</th>
                                        <th class="py-3 px-4 w-20 text-center">Huruf Mutu</th>
                                        @if($canManageStudents)
                                            <th class="py-3 px-4 w-24 text-right">Aksi</th>
                                        @endif
                                    @else
                                        <th class="py-3 px-4 w-28 text-center">Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($students as $idx => $cs)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-4 text-center text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $cs->nim ?: ($cs->student->nim ?: '-') }}</td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900">{{ $cs->student->name }}</div>
                                            <div class="text-[11px] text-slate-500">
                                                {{ $cs->student->email }}
                                                @if($cs->student->studyProgram)
                                                    &middot; <span class="font-semibold text-teal-800">{{ $cs->student->studyProgram->code }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        @if(auth()->user()->isStaff())
                                            <td class="py-3 px-4 text-center">
                                                @if($canManageStudents)
                                                    <form method="post" action="{{ route('courses.students.update', [$course, $cs]) }}" class="inline-flex items-center justify-center gap-1.5">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="nim" value="{{ $cs->nim ?: $cs->student->nim }}">
                                                        <input type="number" name="attendance_rate" min="0" max="100" step="1" value="{{ number_format($cs->attendance_rate, 0, '.', '') }}"
                                                            class="w-16 rounded border-slate-300 py-1 px-2 text-center text-xs font-bold">
                                                        <button type="submit" class="rounded bg-slate-800 px-2 py-1 text-[10px] font-bold text-white hover:bg-slate-700 transition" title="Simpan kehadiran">
                                                            OK
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="font-semibold text-slate-700">{{ number_format($cs->attendance_rate, 0) }}%</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center font-bold text-teal-800">{{ $cs->final_grade ? number_format($cs->final_grade, 1) : '-' }}</td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-800">
                                                    {{ $cs->letter_grade ?: '-' }}
                                                </span>
                                            </td>
                                            @if($canManageStudents)
                                                <td class="py-3 px-4 text-right">
                                                    <form method="post" action="{{ route('courses.students.unenroll', [$course, $cs]) }}"
                                                        onsubmit="return confirm('Keluarkan {{ $cs->student->name }} dari kelas ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-lg border border-red-200 px-2.5 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 transition">
                                                            Keluarkan
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                        @else
                                            <td class="py-3 px-4 text-center">
                                                @if($cs->student_id === auth()->id())
                                                    <span class="inline-flex rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold text-teal-900">
                                                        Anda
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-500">
                                                        Mahasiswa
                                                    </span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
