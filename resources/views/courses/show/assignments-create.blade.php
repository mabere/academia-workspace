        {{-- TAB 3: TUGAS MAHASISWA & PENILAIAN --}}
        {{-- ======================================================== --}}
        <div x-show="tab === 'assignments'" x-cloak class="space-y-5">
            @php
                $canManageAssignments = auth()->user()->isStaff() && (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true));
            @endphp
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-extrabold text-slate-900">
                    {{ auth()->user()->isStaff() ? 'Penugasan & Evaluasi Asesmen' : 'Tugas Perkuliahan' }}
                </h2>

                <div class="flex flex-wrap items-center gap-2">
                    @if($canManageAssignments)
                        <button type="button" @click="openAiModal()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-teal-700 via-indigo-700 to-purple-700 px-3.5 py-2 text-xs font-bold text-white shadow-2xs hover:opacity-90 transition">
                            <span>✨ Rancang dengan AI</span>
                        </button>
                        <button type="button" @click="assignOpen = !assignOpen"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-2xs transition">
                            <span x-text="assignOpen ? 'Tutup Formulir' : '+ Buat Tugas Baru'"></span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="assignOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    @endif

                    @if(auth()->user()->isStaff())
                        <a href="{{ route('courses.grades.export', $course) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-2xs hover:bg-emerald-100 transition">
                            <span>Ekspor CSV</span>
                        </a>
                        <a href="{{ route('courses.grades.print', $course) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                            <span>Cetak Nilai</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Form Terbitkan Tugas Baru (Dosen Pengampu) --}}
            @if($canManageAssignments)
                <div x-show="assignOpen" x-cloak class="rounded-3xl border border-teal-200 bg-white p-6 shadow-xs">
                    <form method="post" action="{{ route('courses.assignments.store', $course) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        {{-- Pemilihan Format Model Penugasan --}}
                        <div class="rounded-2xl bg-slate-50/70 p-4 border border-slate-200">
                            <label class="block text-xs font-bold text-slate-800 mb-2">Format Penugasan *</label>
                            <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-5">
                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'writing' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="writing" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <span class="text-xs font-bold text-slate-900">Esai Akademik</span>
                                </label>

                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'stimulus_exam' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="stimulus_exam" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <span class="text-xs font-bold text-slate-900">Ujian Stimulus</span>
                                </label>

                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'hybrid' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="hybrid" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <span class="text-xs font-bold text-slate-900">Asesmen Hibrida</span>
                                </label>

                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'translation' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="translation" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <span class="text-xs font-bold text-slate-900">Penerjemahan Teks</span>
                                </label>

                                <label class="flex items-start gap-2.5 rounded-xl border p-3 cursor-pointer transition shadow-2xs"
                                    :class="assignmentType === 'milestone_artifact' ? 'border-teal-700 bg-teal-50/80 ring-1 ring-teal-700' : 'border-slate-200 hover:bg-white bg-white'">
                                    <input type="radio" name="assignment_type" value="milestone_artifact" x-model="assignmentType" class="mt-0.5 text-teal-700 focus:ring-teal-700">
                                    <span class="text-xs font-bold text-slate-900">Proposal &amp; Proyek</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700">Judul Penugasan *</label>
                                <input type="text" name="title" id="assignment_title_input" required
                                    class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700">Tenggat Waktu *</label>
                                <input type="datetime-local" name="due_at" required class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-xs text-slate-700">
                            <input type="hidden" name="allow_revisions" value="0">
                            <input type="checkbox" name="allow_revisions" value="1" checked class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                            <span class="font-semibold">Izinkan mahasiswa mengirim revisi</span>
                        </label>

                        <div>
                            <label class="block text-xs font-bold text-slate-700">Pertemuan Perkuliahan</label>
                            <select name="course_session_id" id="assignment_course_session_id" class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700">
                                <option value="">Tugas Umum (Tanpa Sesi Tertentu)</option>
                                @foreach($sessions as $sess)
                                    <option value="{{ $sess->id }}">Pertemuan {{ $sess->session_number }}: {{ $sess->topic }}</option>
                                @endforeach
                            </select>
                        </div>

                        @php
                            $courseCpmks = $course->cpmkList();
                        @endphp
                        <fieldset class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-3.5">
                            <legend class="px-1 text-xs font-bold text-indigo-950">CPMK</legend>
                            @if(count($courseCpmks))
                                <div class="mt-1 grid gap-2 sm:grid-cols-2">
                                    @foreach($courseCpmks as $i => $cpmk)
                                        <label class="flex items-start gap-2 text-xs text-slate-700">
                                            <input type="checkbox" name="cpmk_indices[]" value="{{ $i }}" @checked(in_array((string) $i, old('cpmk_indices', []), true)) class="mt-0.5 rounded border-slate-300 text-indigo-700">
                                            <span>{{ preg_replace('/^(?:(?:\d+|cpmk\s*\d*)[.:)\s]+|[-*•]\s*)/iu', '', $cpmk) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-1 text-xs text-amber-800">CPMK belum diatur pada mata kuliah ini.</p>
                            @endif
                        </fieldset>

                        <details class="rounded-xl border border-teal-200 bg-white p-3.5">
                            <summary class="cursor-pointer text-xs font-bold text-teal-950">Bagian Tugas Terstruktur (Opsional)</summary>
                            <div class="mt-3 space-y-2">
                                @for($part = 0; $part < 4; $part++)
                                    <div class="grid gap-2 rounded-lg bg-slate-50 p-2 sm:grid-cols-12">
                                        <input name="sections[{{ $part }}][title]" aria-label="Judul bagian {{ $part + 1 }}" class="sm:col-span-3 rounded-lg border-slate-300 text-xs">
                                        <input name="sections[{{ $part }}][prompt]" aria-label="Instruksi bagian {{ $part + 1 }}" class="sm:col-span-4 rounded-lg border-slate-300 text-xs">
                                        <select name="sections[{{ $part }}][response_type]" class="sm:col-span-2 rounded-lg border-slate-300 text-xs">
                                            <option value="long_text">Teks</option><option value="url">Tautan</option><option value="audio_video">Audio/Video</option><option value="numeric">Angka</option>
                                        </select>
                                        <input type="number" name="sections[{{ $part }}][weight]" min="0" max="100" step="0.1" aria-label="Bobot bagian {{ $part + 1 }}" class="sm:col-span-1 rounded-lg border-slate-300 text-xs">
                                        <label class="sm:col-span-2 flex items-center gap-1 text-[10px]"><input type="checkbox" name="sections[{{ $part }}][required]" value="1" class="rounded"> Wajib</label>
                                        @if(count($courseCpmks))
                                            <select name="sections[{{ $part }}][cpmk_indices][]" multiple class="sm:col-span-12 rounded-lg border-slate-300 text-xs" aria-label="CPMK bagian {{ $part + 1 }}">
                                                @foreach($courseCpmks as $cpmkIndex => $cpmk)<option value="{{ $cpmkIndex }}">{{ \Illuminate\Support\Str::limit(strip_tags($cpmk), 100) }}</option>@endforeach
                                            </select>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </details>

                        <div>
                            <label class="block text-xs font-bold text-slate-700">Instruksi Pengerjaan *</label>
                            <textarea name="instructions" id="assignment_instructions_input" rows="5" required
                                class="mt-1 w-full rounded-xl border-slate-300 text-xs sm:text-sm focus:border-teal-700 focus:ring-teal-700"></textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="rounded-xl bg-teal-800 px-6 py-2.5 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition">
                                Simpan &amp; Terbitkan Tugas
                            </button>
                        </div>
                    </form>
                </div>
            @endif

