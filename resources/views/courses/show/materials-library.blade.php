            {{-- Interactive Filter & Search Bar untuk Bahan Ajar --}}
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                {{-- Filter Chips --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="materialFilter = 'all'"
                        :class="materialFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        Semua Format ({{ $materials->count() }})
                    </button>
                    <button type="button" @click="materialFilter = 'document'"
                        :class="materialFilter === 'document' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        📄 Dokumen / Slide
                    </button>
                    <button type="button" @click="materialFilter = 'audio'"
                        :class="materialFilter === 'audio' ? 'bg-purple-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        🎧 Audio
                    </button>
                    <button type="button" @click="materialFilter = 'video'"
                        :class="materialFilter === 'video' ? 'bg-rose-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        🎥 Video
                    </button>
                    <button type="button" @click="materialFilter = 'link'"
                        :class="materialFilter === 'link' ? 'bg-blue-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        🔗 Tautan
                    </button>
                    <button type="button" @click="materialFilter = 'text'"
                        :class="materialFilter === 'text' ? 'bg-amber-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        📝 Teks
                    </button>
                </div>

                {{-- Realtime Search Input --}}
                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="materialSearch" placeholder="Cari judul materi..."
                        class="w-full rounded-xl border-slate-300 pl-9 pr-3 py-1.5 text-xs focus:border-teal-700 focus:ring-teal-700 bg-slate-50/50">
                    <svg class="absolute left-3 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- Empty State --}}
            @if($materials->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-2xs">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-50 text-teal-700 mb-3">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Bahan Ajar Diterbitkan</h3>
                    <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                        Dosen pengampu belum mengunggah berkas modul atau tautan referensi untuk ruang perkuliahan ini.
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($materials as $material)
                        @php
                            $mTypeCategory = $material->material_type;
                            if ($material->isWordDocument() || $material->isPowerpoint() || $material->isPdf()) {
                                $mTypeCategory = 'document';
                            }
                        @endphp
                        <div x-show="(materialFilter === 'all' || materialFilter === '{{ $mTypeCategory }}') && ('{{ strtolower(addslashes($material->title)) }}'.includes(materialSearch.toLowerCase()) || '{{ strtolower(addslashes($material->description ?? '')) }}'.includes(materialSearch.toLowerCase()))"
                            class="group rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs hover:border-teal-300 hover:shadow-md transition">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="flex items-start gap-4 max-w-3xl">
                                    {{-- Format Visual Tile Badge --}}
                                    <div class="h-12 w-12 rounded-2xl flex items-center justify-center font-bold text-lg shrink-0 shadow-2xs
                                        {{ $material->isAudio() ? 'bg-gradient-to-br from-purple-600 to-indigo-700 text-white' :
                                           ($material->isVideo() ? 'bg-gradient-to-br from-rose-500 to-red-700 text-white' :
                                           ($material->isPdf() ? 'bg-gradient-to-br from-red-600 to-rose-700 text-white' :
                                           ($material->isWordDocument() ? 'bg-gradient-to-br from-blue-600 to-sky-700 text-white' :
                                           ($material->isPowerpoint() ? 'bg-gradient-to-br from-amber-600 to-orange-700 text-white' :
                                           ($material->isLink() ? 'bg-gradient-to-br from-sky-600 to-blue-700 text-white' :
                                           ($material->isText() ? 'bg-gradient-to-br from-emerald-600 to-teal-700 text-white' : 'bg-gradient-to-br from-slate-700 to-slate-900 text-white')))))) }}">
                                        @if($material->isAudio()) 🎧
                                        @elseif($material->isVideo()) 🎥
                                        @elseif($material->isPdf()) 📄
                                        @elseif($material->isWordDocument()) 📝
                                        @elseif($material->isPowerpoint()) 📊
                                        @elseif($material->isLink()) 🔗
                                        @elseif($material->isText()) 📜
                                        @else 📦
                                        @endif
                                    </div>

                                    <div class="space-y-1.5 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider
                                                {{ $material->isAudio() ? 'bg-purple-100 text-purple-900 border border-purple-200' :
                                                   ($material->isVideo() ? 'bg-rose-100 text-rose-900 border border-rose-200' :
                                                   ($material->isLink() ? 'bg-blue-100 text-blue-900 border border-blue-200' :
                                                   ($material->isText() ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-slate-100 text-slate-800 border border-slate-200'))) }}">
                                                {{ $material->typeLabel() }}
                                            </span>

                                            @if($material->session_number)
                                                <span class="rounded-lg bg-teal-50 px-2.5 py-0.5 text-[11px] font-bold text-teal-800 border border-teal-200">
                                                    Pertemuan Ke-{{ $material->session_number }}
                                                </span>
                                            @else
                                                <span class="rounded-lg bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                                                    Materi Umum / Silabus RPS
                                                </span>
                                            @endif

                                            <span class="text-xs text-slate-400">· Diunggah {{ $material->created_at->diffForHumans() }}</span>
                                        </div>

                                        <h3 class="text-base font-extrabold text-slate-900 leading-snug">
                                            {{ $material->title }}
                                        </h3>

                                        @if($material->description)
                                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $material->description }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($material->file_path)
                                        @if($material->isPdf())
                                            <a href="{{ route('courses.materials.stream', [$course, $material]) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-teal-700 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Buka PDF</span>
                                            </a>
                                        @elseif($material->isWordDocument())
                                            <button type="button"
                                                @click="openDocPreview('{{ addslashes($material->title) }}', '{{ $material->getGoogleDocsViewerUrl($course) }}', '{{ route('courses.materials.download', [$course, $material]) }}')"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-blue-700 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-blue-800 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span>Lihat Dokumen</span>
                                            </button>
                                        @elseif($material->isPowerpoint())
                                            <button type="button"
                                                @click="openDocPreview('{{ addslashes($material->title) }}', '{{ $material->getGoogleDocsViewerUrl($course) }}', '{{ route('courses.materials.download', [$course, $material]) }}')"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-amber-700 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-amber-800 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                                </svg>
                                                <span>Lihat Slide</span>
                                            </button>
                                        @endif

                                        <a href="{{ route('courses.materials.download', [$course, $material]) }}"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>Unduh ({{ $material->formattedFileSize() }})</span>
                                        </a>
                                    @elseif($material->external_url)
                                        <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:bg-teal-700 transition">
                                            <span>Buka Tautan &rarr;</span>
                                        </a>
                                    @endif

                                    @if(auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)))
                                        <button type="button"
                                            @click="openEditMaterialModal({
                                                id: {{ $material->id }},
                                                title: '{{ addslashes($material->title) }}',
                                                session_number: '{{ $material->session_number ?? '' }}',
                                                material_type: '{{ $material->material_type }}',
                                                external_url: '{{ addslashes($material->external_url ?? '') }}',
                                                content_text: {{ json_encode($material->content_text ?? '') }},
                                                description: '{{ addslashes($material->description ?? '') }}',
                                                file_name: '{{ addslashes($material->file_name ?? '') }}',
                                                file_size: '{{ $material->formattedFileSize() }}',
                                                update_url: '{{ route('courses.materials.update', [$course, $material]) }}'
                                            })"
                                            class="rounded-xl p-2 text-slate-500 hover:bg-teal-50 hover:text-teal-800 transition" title="Edit bahan ajar">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <form method="post" action="{{ route('courses.materials.destroy', [$course, $material]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl p-2 text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Hapus bahan ajar">
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
                                <div class="mt-4 rounded-2xl border border-purple-200 bg-purple-50/50 p-4">
                                    <p class="text-xs font-bold text-purple-900 mb-2">Putar Audio Perkuliahan:</p>
                                    <audio controls class="w-full h-10">
                                        <source src="{{ route('courses.materials.stream', [$course, $material]) }}" type="{{ $material->mime_type ?: 'audio/mpeg' }}">
                                        Browser Anda tidak mendukung pemutar audio langsung.
                                    </audio>
                                </div>
                            @endif

                            {{-- PLAYER VIDEO MULTIMEDIA --}}
                            @if($material->isVideo() && $material->file_path)
                                <div class="mt-4 rounded-2xl border border-rose-200 bg-black/5 p-3">
                                    <video controls class="w-full max-h-96 rounded-xl">
                                        <source src="{{ route('courses.materials.stream', [$course, $material]) }}" type="{{ $material->mime_type ?: 'video/mp4' }}">
                                        Browser Anda tidak mendukung pemutar video langsung.
                                    </video>
                                </div>
                            @elseif($material->isLink() && $material->getYouTubeEmbedUrl())
                                <div class="mt-4 aspect-video w-full max-w-2xl overflow-hidden rounded-2xl shadow-sm border border-slate-200">
                                    <iframe class="h-full w-full" src="{{ $material->getYouTubeEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            @endif

                            {{-- KONTEN TEKS ARTIKEL --}}
                            @if($material->isText() && $material->content_text)
                                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50/40 p-5 text-sm leading-relaxed text-slate-800 whitespace-pre-wrap font-serif">
                                    {{ $material->content_text }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ======================================================== --}}
