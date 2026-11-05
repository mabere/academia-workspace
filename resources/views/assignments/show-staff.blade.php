@php
    $course = $assignment->course;
    $canGrade = auth()->user()->isStaff() && ($course ? (auth()->id() === $course->lecturer_id || in_array(auth()->user()->role, ['admin', 'superadmin'], true)) : ($assignment->project->isOwnedBy(auth()->user()) || $assignment->project->hasEditor(auth()->user())));

    $enrolledStudents = $course
        ? $course->students->map(fn ($cs) => $cs->student)->filter()
        : $assignment->students;

    $submittedIds = $assignment->submissions->pluck('student_id')->all();
    $unsubmittedStudents = $enrolledStudents->reject(fn ($student) => in_array($student->id, $submittedIds, true))->values();

    $totalTargetStudents = max($enrolledStudents->count(), $assignment->submissions->count());
    $submittedCount = $assignment->submissions->whereIn('status', ['submitted', 'graded'])->count();
    $gradedCount = $assignment->submissions->where('status', 'graded')->count();
    $needsGradingCount = $assignment->submissions->where('status', 'submitted')->count();
    $draftCount = $assignment->submissions->where('status', 'draft')->count();
    $scoredSubmissions = $assignment->submissions->whereNotNull('score');
    $avgScore = $scoredSubmissions->isNotEmpty() ? $scoredSubmissions->avg('score') : null;
@endphp

<section class="mt-6 space-y-5" x-data="{
    subSearch: '',
    subFilter: 'all',
    submissionModalOpen: false,
    activeSub: null,
    activeRevIndex: 0,
    activeCopySuccess: false,
    openSubmissionModal(subData) {
        this.activeSub = subData;
        if (subData && subData.revisions && subData.revisions.length > 0) {
            this.activeRevIndex = subData.revisions.length - 1;
        } else {
            this.activeRevIndex = 0;
        }
        this.submissionModalOpen = true;
        this.activeCopySuccess = false;
    },
    currentRevision() {
        if (!this.activeSub || !this.activeSub.revisions || this.activeSub.revisions.length === 0) {
            return null;
        }
        return this.activeSub.revisions[this.activeRevIndex] || this.activeSub.revisions[0];
    },
    currentWordCount() {
        const rev = this.currentRevision();
        if (!rev || !rev.body_text) return 0;
        const trimmed = rev.body_text.trim();
        return trimmed ? trimmed.split(/\s+/).length : 0;
    },
    copyCurrentText() {
        const rev = this.currentRevision();
        if (!rev || !rev.body_text) return;
        navigator.clipboard.writeText(rev.body_text).then(() => {
            this.activeCopySuccess = true;
            setTimeout(() => { this.activeCopySuccess = false; }, 2000);
        });
    },
    matchesFilter(status, name, nim, email) {
        if (this.subFilter !== 'all' && this.subFilter !== status) {
            return false;
        }
        if (!this.subSearch.trim()) {
            return true;
        }
        const q = this.subSearch.toLowerCase();
        return (name || '').toLowerCase().includes(q)
            || (nim || '').toLowerCase().includes(q)
            || (email || '').toLowerCase().includes(q);
    }
}">
    {{-- Ringkasan Statistik Submisi --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs">
            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Peserta</span>
            <span class="mt-1 block text-2xl font-black text-slate-900">{{ $totalTargetStudents }}</span>
        </div>
        <div class="rounded-2xl border border-teal-200 bg-teal-50/40 p-4 shadow-2xs">
            <span class="block text-[11px] font-bold uppercase tracking-wider text-teal-800">Terkirim</span>
            <div class="mt-1 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-teal-950">{{ $submittedCount }}</span>
                <span class="text-xs font-semibold text-slate-500">/ {{ $totalTargetStudents }}</span>
            </div>
        </div>
        <div class="rounded-2xl border {{ $needsGradingCount > 0 ? 'border-amber-300 bg-amber-50/60' : 'border-slate-200 bg-white' }} p-4 shadow-2xs">
            <span class="block text-[11px] font-bold uppercase tracking-wider {{ $needsGradingCount > 0 ? 'text-amber-900' : 'text-slate-500' }}">Perlu Dinilai</span>
            <span class="mt-1 block text-2xl font-black {{ $needsGradingCount > 0 ? 'text-amber-900' : 'text-slate-900' }}">{{ $needsGradingCount }}</span>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4 shadow-2xs">
            <span class="block text-[11px] font-bold uppercase tracking-wider text-emerald-800">Rata-rata Nilai</span>
            <span class="mt-1 block text-2xl font-black text-emerald-950">{{ $avgScore !== null ? number_format($avgScore, 1) : '-' }}</span>
        </div>
    </div>

    {{-- Bar Filter & Pencarian Submisi --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
            <button type="button" @click="subFilter = 'all'"
                :class="subFilter === 'all' ? 'bg-teal-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200/70'"
                class="rounded-xl px-3 py-1.5 transition">
                Semua ({{ $assignment->submissions->count() + $unsubmittedStudents->count() }})
            </button>
            <button type="button" @click="subFilter = 'submitted'"
                :class="subFilter === 'submitted' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100'"
                class="rounded-xl px-3 py-1.5 transition">
                Perlu Dinilai ({{ $needsGradingCount }})
            </button>
            <button type="button" @click="subFilter = 'graded'"
                :class="subFilter === 'graded' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-900 border border-emerald-200 hover:bg-emerald-100'"
                class="rounded-xl px-3 py-1.5 transition">
                Sudah Dinilai ({{ $gradedCount }})
            </button>
            @if($draftCount > 0)
                <button type="button" @click="subFilter = 'draft'"
                    :class="subFilter === 'draft' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200/70'"
                    class="rounded-xl px-3 py-1.5 transition">
                    Draf ({{ $draftCount }})
                </button>
            @endif
            @if($unsubmittedStudents->isNotEmpty())
                <button type="button" @click="subFilter = 'unsubmitted'"
                    :class="subFilter === 'unsubmitted' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
                    class="rounded-xl px-3 py-1.5 transition">
                    Belum Mengumpulkan ({{ $unsubmittedStudents->count() }})
                </button>
            @endif
        </div>

        <div class="w-full sm:w-64">
            <input type="search" x-model="subSearch" aria-label="Cari mahasiswa"
                class="w-full rounded-xl border-slate-300 py-1.5 px-3 text-xs focus:border-teal-700 focus:ring-teal-700">
        </div>
    </div>

    {{-- Tabel Evaluasi Submisi Mahasiswa --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="py-3 px-4">Mahasiswa</th>
                        <th class="py-3 px-4 text-center">Naskah / Jawaban</th>
                        <th class="py-3 px-4 w-36 text-center">Status</th>
                        <th class="py-3 px-4 w-24 text-center">Nilai</th>
                        <th class="py-3 px-4 w-20 text-center">Mutu</th>
                        @if($canGrade && $course)
                            <th class="py-3 px-4">Evaluasi Nilai &amp; Catatan</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($assignment->submissions as $sub)
                        @php
                            $latestRev = $sub->revisions->last();
                            $revCount = $sub->revisions->count();
                            $latestFeedback = $latestRev?->feedbackReports?->first()?->feedback_json;
                            $feedbackText = '';
                            if (is_array($latestFeedback) && !empty($latestFeedback['overall_summary'])) {
                                $feedbackText = $latestFeedback['overall_summary'];
                            } elseif (!empty($sub->notes)) {
                                $feedbackText = $sub->notes;
                            }

                            $subPayload = [
                                'id' => $sub->id,
                                'student_name' => $sub->student->name,
                                'student_nim' => $sub->student->nim ?? '-',
                                'student_email' => $sub->student->email,
                                'status' => $sub->status,
                                'can_grade' => $course && $canGrade && in_array($sub->status, ['submitted', 'graded'], true),
                                'status_label' => match($sub->status) {
                                    'graded' => 'Sudah Dinilai',
                                    'submitted' => 'Sudah Dikumpulkan',
                                    default => 'Draf Mahasiswa',
                                },
                                'score' => $sub->score,
                                'letter_grade' => $sub->letterGrade(),
                                'feedback_notes' => $feedbackText,
                                'artifact_url' => $sub->artifact_url,
                                'notes' => $sub->notes,
                                'grade_action_url' => $course ? route('courses.assignments.grade', [$course, $assignment, $sub]) : '',
                                'assignment_title' => $assignment->title,
                                'assignment_type' => $assignment->assignment_type,
                                'assignment_type_label' => $assignment->typeLabel(),
                                'cpmk_outcomes' => $assignment->config_json['cpmk_outcomes'] ?? [],
                                'sections' => $assignment->config_json['sections'] ?? [],
                                'revisions' => $sub->revisions->map(fn($r) => [
                                    'id' => $r->id,
                                    'revision_number' => $r->revision_number === 1 ? 'Naskah Awal' : 'Revisi '.($r->revision_number - 1),
                                    'raw_revision_number' => $r->revision_number,
                                    'body_text' => $r->body_text ?? '',
                                    'body_html' => $r->renderedBodyHtml(),
                                    'section_answers' => $r->section_answers ?? [],
                                    'artifact_url' => $r->artifact_url,
                                    'notes' => $r->notes,
                                    'is_draft' => false,
                                    'submitted_at' => $r->submitted_at ? $r->submitted_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : ($r->created_at ? $r->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : '-'),
                                ])->when($sub->status === 'draft' && ($sub->draft_body_text || !empty($sub->draft_section_answers) || $sub->draft_artifact_url || $sub->draft_notes), fn($revisions) => $revisions->push([
                                    'id' => 'draft-'.$sub->id,
                                    'revision_number' => 'Draf',
                                    'body_text' => $sub->draft_body_text ?? '',
                                    'body_html' => $sub->renderedDraftBodyHtml(),
                                    'section_answers' => $sub->draft_section_answers ?? [],
                                    'artifact_url' => $sub->draft_artifact_url,
                                    'notes' => $sub->draft_notes,
                                    'is_draft' => true,
                                    'submitted_at' => $sub->draft_saved_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' ?? '-',
                                ]))->values()->all(),
                                'attempts' => $sub->attempts->map(fn($attempt) => [
                                    'attempt_number' => $attempt->attempt_number,
                                    'status' => $attempt->status,
                                    'submitted_at' => $attempt->submitted_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' ?? '-',
                                    'score' => $attempt->score,
                                    'answers' => $attempt->answers_snapshot ?? [],
                                ])->values()->all(),
                                'answers' => $sub->answers->where('attempt_number', $sub->current_attempt ?: 1)->map(fn($a) => [
                                    'id' => $a->id,
                                    'question_text' => $a->question?->prompt ?? 'Pertanyaan',
                                    'question_type' => $a->question?->question_type === 'multiple_choice' ? 'mcq' : 'essay',
                                    'selected_option' => $a->question?->question_type === 'multiple_choice' ? $a->student_answer : null,
                                    'answer_text' => $a->question?->question_type === 'multiple_choice' ? null : $a->student_answer,
                                    'is_correct' => $a->is_correct,
                                    'score' => $a->score_awarded,
                                ])->values()->all(),
                                'writewise_url' => route('assignments.show', $assignment),
                            ];
                        @endphp
                        <tr x-show="matchesFilter(@js($sub->status), @js($sub->student->name), @js($sub->student->nim ?? ''), @js($sub->student->email))"
                            class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $sub->student->name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $sub->student->nim ?: '-' }} · {{ $sub->student->email }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <button type="button"
                                        @click="openSubmissionModal(@js($subPayload))"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-teal-300 bg-teal-50 px-3 py-1.5 text-xs font-bold text-teal-800 hover:bg-teal-100 transition shadow-2xs">
                                        <svg class="h-3.5 w-3.5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Periksa Jawaban</span>
                                        @if($revCount > 1)
                                            <span class="rounded-full bg-teal-200/80 px-1.5 py-0.2 text-[10px] font-mono font-bold text-teal-900">Rev #{{ $revCount - 1 }}</span>
                                        @elseif($revCount === 1)
                                            <span class="rounded-full bg-teal-200/80 px-1.5 py-0.2 text-[10px] font-mono font-bold text-teal-900">1 Naskah</span>
                                        @elseif($sub->answers->count() > 0)
                                            <span class="rounded-full bg-teal-200/80 px-1.5 py-0.2 text-[10px] font-mono font-bold text-teal-900">{{ $sub->answers->count() }} Jwb</span>
                                        @endif
                                    </button>

                                    @if($sub->artifact_url)
                                        <a href="{{ $sub->artifact_url }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-slate-200 bg-white text-slate-600 hover:text-teal-700 hover:border-teal-300 transition"
                                            title="Buka tautan artefak">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($sub->status === 'graded')
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Sudah Dinilai
                                    </span>
                                @elseif($sub->status === 'submitted')
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Perlu Dinilai
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Draf
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-teal-900 text-sm">
                                {{ $sub->score !== null ? number_format($sub->score, 1) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700">
                                {{ $sub->letterGrade() }}
                            </td>
                            @if($canGrade && $course)
                                <td class="py-3 px-4">
                                    @if(in_array($sub->status, ['submitted', 'graded'], true))
                                        <form method="post" action="{{ route('courses.assignments.grade', [$course, $assignment, $sub]) }}" class="flex items-center gap-2">
                                            @csrf
                                            <input type="number" step="0.1" min="0" max="100" name="score" required value="{{ $sub->score }}" aria-label="Nilai" class="w-20 rounded-xl border-slate-300 py-1.5 text-xs text-center font-bold">
                                            <input type="text" name="feedback_notes" value="{{ $feedbackText }}" aria-label="Catatan evaluasi" class="w-48 sm:w-64 rounded-xl border-slate-300 py-1.5 text-xs">
                                            <button type="submit" class="rounded-xl bg-teal-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700 transition shadow-2xs">
                                                Simpan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach

                    {{-- Mahasiswa Terdaftar yang Belum Mengumpulkan --}}
                    @foreach($unsubmittedStudents as $student)
                        <tr x-show="matchesFilter('unsubmitted', @js($student->name), @js($student->nim ?? ''), @js($student->email))"
                            class="bg-slate-50/40 text-slate-500">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-700">{{ $student->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $student->nim ?: '-' }} · {{ $student->email }}</div>
                            </td>
                            <td class="py-3 px-4 text-center text-slate-400">—</td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500 border border-slate-200">
                                    Belum Mengumpulkan
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-slate-400">-</td>
                            <td class="py-3 px-4 text-center text-slate-400">-</td>
                            @if($canGrade && $course)
                                <td class="py-3 px-4 text-slate-400">—</td>
                            @endif
                        </tr>
                    @endforeach

                    @if($assignment->submissions->isEmpty() && $unsubmittedStudents->isEmpty())
                        <tr>
                            <td colspan="{{ $canGrade && $course ? 6 : 5 }}" class="py-10 px-4 text-center text-xs text-slate-500">
                                Belum ada data submisi mahasiswa.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel Umpan Balik per Versi Naskah (Jika Proyek Mandiri atau AI Aktif) --}}
    @if((!$course || $aiFeedbackAvailable) && $assignment->submissions->isNotEmpty())
        <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <summary class="cursor-pointer text-sm font-bold text-slate-900">Umpan Balik Draf &amp; AI per Revisi</summary>
            <div class="mt-4 space-y-4">
                @foreach($assignment->submissions as $submission)
                    @foreach($submission->revisions as $revision)
                        <div class="rounded-xl border border-slate-200 p-4 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-bold text-slate-900">
                                    {{ $submission->student->name }} · {{ $revision->revision_number === 1 ? 'Pengumpulan Awal' : 'Revisi ' . ($revision->revision_number - 1) }}
                                </p>
                                <span class="text-xs text-slate-500">{{ $revision->submitted_at ? $revision->submitted_at->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB' : '-' }}</span>
                            </div>

                            @php
                                $draftReports = $revision->feedbackReports->where('status', 'draft');
                            @endphp
                            @forelse($draftReports as $report)
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3.5 space-y-2">
                                    <form method="post" action="{{ route('feedback.update', $report) }}" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="block text-xs font-bold text-slate-800">Ringkasan umpan balik<textarea name="feedback_json[overall_summary]" required maxlength="5000" rows="3" class="mt-1 w-full rounded-xl border-slate-300 text-xs leading-relaxed">{{ $report->feedback_json['overall_summary'] ?? '' }}</textarea></label>
                                        <button class="rounded-xl border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 hover:bg-amber-100/50 transition">Simpan suntingan</button>
                                    </form>
                                    <form method="post" action="{{ route('feedback.publish', $report) }}">
                                        @csrf
                                        <button class="rounded-xl bg-teal-700 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-teal-800 transition">Terbitkan ke mahasiswa</button>
                                    </form>
                                </div>
                            @empty
                                <div class="flex flex-wrap items-end gap-3">
                                    <form method="post" action="{{ route('feedback.store', $revision) }}" class="min-w-72 flex-1 space-y-2">
                                        @csrf
                                        <label class="block text-xs font-bold text-slate-800">Umpan balik manual<textarea name="overall_summary" required maxlength="5000" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-xs leading-relaxed"></textarea></label>
                                        <button class="rounded-xl border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Simpan draf</button>
                                    </form>
                                    @if($aiFeedbackAvailable)
                                        <form method="post" action="{{ route('feedback.generate', $revision) }}">
                                            @csrf
                                            <button class="rounded-xl border border-teal-700 bg-teal-50/50 px-3.5 py-1.5 text-xs font-semibold text-teal-800 hover:bg-teal-50 transition">Buat draf AI</button>
                                        </form>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    @endforeach
                @endforeach
            </div>
        </details>
    @endif

    {{-- Modal Periksa Jawaban --}}
    @include('courses.show.submission-review')
</section>
