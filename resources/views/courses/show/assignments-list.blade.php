            {{-- Daftar Tugas Kelas (Katalog Ringkas) --}}
            @if($assignments->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-2xs">
                    <h3 class="text-sm font-bold text-slate-800">Belum Ada Tugas Perkuliahan</h3>
                </div>
            @else
                @php
                    $assignmentsNeedingGrading = $assignments->filter(fn ($a) => ($a->submitted_submissions_count - $a->graded_submissions_count) > 0)->count();
                    $draftAssignmentsCount = $assignments->where('status', 'draft')->count();
                    $usedSessionNumbers = $assignments->map(fn ($a) => $a->config_json['session_number'] ?? null)->filter()->unique()->sort()->values();
                @endphp

                <div class="space-y-4" x-data="{
                    aStatus: 'all',
                    aSession: 'all',
                    aSearch: '',
                    matchesAssignment(title, sessionNum, status, needsGrading) {
                        if (this.aStatus === 'needs_grading' && !needsGrading) return false;
                        if (this.aStatus === 'draft' && status !== 'draft') return false;
                        if (this.aStatus === 'published' && status !== 'published') return false;
                        if (this.aSession === 'general' && sessionNum !== '') return false;
                        if (this.aSession !== 'all' && this.aSession !== 'general' && String(sessionNum) !== String(this.aSession)) return false;
                        if (this.aSearch.trim() !== '' && !(title || '').toLowerCase().includes(this.aSearch.toLowerCase())) return false;
                        return true;
                    }
                }" @filter-assignments-session.window="aSession = String($event.detail)">

                    {{-- Bar Filter & Pencarian Tugas --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                            <button type="button" @click="aStatus = 'all'"
                                :class="aStatus === 'all' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200/70'"
                                class="rounded-xl px-3 py-1.5 transition">
                                Semua ({{ $assignments->count() }})
                            </button>
                            @if(auth()->user()->isStaff())
                                <button type="button" @click="aStatus = 'needs_grading'"
                                    :class="aStatus === 'needs_grading' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100'"
                                    class="rounded-xl px-3 py-1.5 transition">
                                    Perlu Dinilai ({{ $assignmentsNeedingGrading }})
                                </button>
                                @if($draftAssignmentsCount > 0)
                                    <button type="button" @click="aStatus = 'draft'"
                                        :class="aStatus === 'draft' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200/70'"
                                        class="rounded-xl px-3 py-1.5 transition">
                                        Draf ({{ $draftAssignmentsCount }})
                                    </button>
                                @endif
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                            <select x-model="aSession" aria-label="Filter pertemuan"
                                class="rounded-xl border-slate-300 py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 focus:border-teal-700 focus:ring-teal-700">
                                <option value="all">Semua Pertemuan</option>
                                <option value="general">Tugas Umum</option>
                                @for($i = 1; $i <= 16; $i++)
                                    <option value="{{ $i }}">Pertemuan {{ $i }}</option>
                                @endfor
                            </select>

                            <input type="search" x-model="aSearch" aria-label="Cari tugas" placeholder="Cari judul tugas..."
                                class="w-full sm:w-52 rounded-xl border-slate-300 py-1.5 px-3 text-xs focus:border-teal-700 focus:ring-teal-700 bg-slate-50/50">
                        </div>
                    </div>

                    {{-- Daftar Kartu Tugas Ringkas --}}
                    <div class="space-y-3">
                        @foreach($assignments as $assignment)
                            @php
                                $sessionNum = $assignment->config_json['session_number'] ?? '';
                                $needsGradingCount = max(0, (int) $assignment->submitted_submissions_count - (int) $assignment->graded_submissions_count);
                                $hasLongInstructions = mb_strlen((string) $assignment->instructions) > 180;
                            @endphp
                            <div x-show="matchesAssignment(@js($assignment->title), @js((string) $sessionNum), @js($assignment->status), @js($needsGradingCount > 0))"
                                x-data="{ expandInst: false }"
                                class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs hover:border-teal-300 transition">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="space-y-1.5 max-w-3xl flex-1 min-w-[240px]">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @if($sessionNum !== '')
                                                <span class="inline-flex rounded-lg bg-indigo-50 px-2.5 py-0.5 text-[11px] font-bold text-indigo-800 border border-indigo-200">
                                                    Pertemuan {{ $sessionNum }}
                                                </span>
                                            @endif

                                            <span class="inline-flex rounded-lg bg-teal-50 px-2.5 py-0.5 text-[11px] font-bold text-teal-800 border border-teal-200">
                                                {{ $assignment->typeLabel() }}
                                            </span>

                                            @if($assignment->status === 'draft')
                                                <span class="inline-flex rounded-lg bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800 border border-amber-200">
                                                    Draf
                                                </span>
                                            @endif

                                            @if(!empty($assignment->config_json['cpmk_outcomes']))
                                                <span class="inline-flex rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700 border border-slate-200">
                                                    {{ count($assignment->config_json['cpmk_outcomes']) }} CPMK
                                                </span>
                                            @endif

                                            @if(!empty($assignment->config_json['sections']))
                                                <span class="inline-flex rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700 border border-slate-200">
                                                    {{ count($assignment->config_json['sections']) }} Bagian
                                                </span>
                                            @endif

                                            @if($assignment->due_at)
                                                @php
                                                    $isPassed = $assignment->due_at->isPast();
                                                    $isNear = !$isPassed && $assignment->due_at->diffInHours(now()) <= 48;
                                                @endphp
                                                <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-0.5 text-[11px] font-bold border
                                                    {{ $isPassed ? 'bg-slate-100 text-slate-600 border-slate-200' :
                                                       ($isNear ? 'bg-rose-100 text-rose-800 border-rose-200' : 'bg-amber-50 text-amber-900 border-amber-200') }}">
                                                    Tenggat: {{ $assignment->due_at->format('d/m/Y H:i') }} WIB
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="text-base font-extrabold text-slate-900">
                                            <a href="{{ (!auth()->user()->isStaff() && ($assignment->isStimulusExam() || $assignment->isHybrid())) ? route('assignments.exam.take', $assignment) : route('assignments.show', $assignment) }}" class="hover:text-teal-800 transition">
                                                {{ $assignment->title }}
                                            </a>
                                        </h3>

                                        @if($assignment->instructions)
                                            <div class="text-xs text-slate-600 leading-relaxed">
                                                <p x-show="!expandInst" class="line-clamp-2 whitespace-pre-line">{{ $assignment->instructions }}</p>
                                                <p x-show="expandInst" x-cloak class="whitespace-pre-wrap">{{ $assignment->instructions }}</p>
                                                @if($hasLongInstructions)
                                                    <button type="button" @click="expandInst = !expandInst" class="mt-1 text-[11px] font-bold text-teal-800 hover:underline">
                                                        <span x-text="expandInst ? 'Ringkas instruksi' : 'Baca instruksi lengkap'"></span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Ringkasan Status & Tombol Aksi --}}
                                    @if(auth()->user()->isStaff())
                                        <div class="flex flex-col items-start sm:items-end justify-between gap-3 shrink-0">
                                            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-semibold text-slate-700 border border-slate-200">
                                                    Terkirim: <strong>{{ $assignment->submitted_submissions_count }}/{{ $students->count() }}</strong>
                                                </span>
                                                <span class="rounded-lg bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-800 border border-emerald-200">
                                                    Dinilai: <strong>{{ $assignment->graded_submissions_count }}</strong>
                                                </span>
                                                @if($needsGradingCount > 0)
                                                    <span class="rounded-lg bg-amber-100 px-2.5 py-1 font-bold text-amber-900 border border-amber-300">
                                                        {{ $needsGradingCount }} Perlu Dinilai
                                                    </span>
                                                @endif
                                                @if($assignment->average_score !== null)
                                                    <span class="rounded-lg bg-teal-50 px-2.5 py-1 font-bold text-teal-900 border border-teal-200">
                                                        Rata-rata: {{ number_format((float) $assignment->average_score, 1) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <a href="{{ route('assignments.show', $assignment) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition shadow-2xs">
                                                    <span>Periksa &amp; Nilai</span>
                                                    <span class="rounded-full bg-teal-950/60 px-1.5 py-0.2 text-[10px] font-mono text-teal-100">{{ $assignment->submissions_count }}</span>
                                                    <span>&rarr;</span>
                                                </a>

                                                @if($assignment->isStimulusExam() || $assignment->isHybrid())
                                                    <a href="{{ route('assignments.questions.index', $assignment) }}"
                                                        class="inline-flex items-center gap-1 rounded-xl border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-900 hover:bg-amber-100 transition">
                                                        <span>Soal ({{ $assignment->questions_count }})</span>
                                                    </a>
                                                @endif

                                                @if(auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true))
                                                    <a href="{{ route('courses.assignments.edit', [$course, $assignment]) }}"
                                                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                                        Sunting
                                                    </a>

                                                    <form method="post" action="{{ route('courses.assignments.destroy', [$course, $assignment]) }}"
                                                        onsubmit="return confirm('Hapus tugas ini beserta seluruh submisi mahasiswa?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-xl border border-red-200 bg-white px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 transition">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $mySub = $assignment->submissions->first();
                                            $studentActionUrl = ($assignment->isStimulusExam() || $assignment->isHybrid())
                                                ? route('assignments.exam.take', $assignment)
                                                : route('assignments.show', $assignment);
                                        @endphp
                                        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                                            @if($mySub)
                                                @if($mySub->status === 'graded' && $mySub->score !== null)
                                                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-900 border border-emerald-200">
                                                        <span>Nilai: {{ number_format($mySub->score, 1) }} ({{ $mySub->letterGrade() }})</span>
                                                    </span>
                                                @elseif($mySub->status === 'submitted')
                                                    <span class="inline-flex rounded-xl bg-teal-50 px-3 py-1.5 text-xs font-bold text-teal-800 border border-teal-200">
                                                        Sudah Dikumpulkan
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-xl bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-800 border border-amber-200">
                                                        Draf Tersimpan
                                                    </span>
                                                @endif
                                            @else
                                                <span class="inline-flex rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                                    Belum Mengumpulkan
                                                </span>
                                            @endif

                                            <a href="{{ $studentActionUrl }}" class="inline-flex items-center gap-1.5 rounded-xl bg-teal-800 px-4 py-1.5 text-xs font-bold text-white hover:bg-teal-700 shadow-2xs transition">
                                                @if($assignment->isStimulusExam() || $assignment->isHybrid())
                                                    <span>{{ ($mySub && in_array($mySub->status, ['submitted', 'graded'], true)) ? 'Lihat Hasil' : 'Kerjakan Ujian' }} &rarr;</span>
                                                @else
                                                    <span>{{ $mySub ? 'Lihat Tugas' : 'Kerjakan Tugas' }} &rarr;</span>
                                                @endif
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
