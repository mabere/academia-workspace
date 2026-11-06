<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Project;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use App\Models\WritingSubmissionAttempt;
use App\Notifications\AcademicNotification;
use App\Services\Ai\AssignmentAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseAssignmentController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || in_array($request->user()->role, ['admin', 'superadmin'], true), 403);

        $data = $request->validate([
            'course_session_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['required', 'string', 'max:10000'],
            'assignment_type' => ['nullable', 'string', 'in:writing,stimulus_exam,hybrid,translation,milestone_artifact'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:5', 'max:360'],
            'mcq_weight_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'essay_weight_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:51200'],
            'audio_max_plays' => ['nullable', 'integer', 'min:0', 'max:10'],
            'reading_passage' => ['nullable', 'string', 'max:20000'],
            'source_text' => ['nullable', 'string', 'max:20000'],
            'source_language' => ['nullable', 'string', 'max:60'],
            'target_language' => ['nullable', 'string', 'max:60'],
            'due_at' => ['nullable', 'date'],
            'allow_revisions' => ['nullable', 'boolean'],
            'feedback_language' => ['nullable', 'string', 'in:id,en'],
            'feedback_level' => ['nullable', 'string', 'in:direct,indirect,balanced'],
            'rubric_name' => ['nullable', 'string', 'max:120'],
            'cpmk_indices' => ['nullable', 'array'],
            'cpmk_indices.*' => ['integer', 'min:0'],
            'sections' => ['nullable', 'array', 'max:12'],
            'sections.*.title' => ['nullable', 'string', 'max:120'],
            'sections.*.prompt' => ['nullable', 'string', 'max:2000'],
            'sections.*.response_type' => ['nullable', 'in:long_text,url,audio_video,numeric'],
            'sections.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sections.*.required' => ['nullable', 'boolean'],
            'sections.*.cpmk_indices' => ['nullable', 'array'],
            'sections.*.cpmk_indices.*' => ['integer', 'min:0'],
        ]);

        if (($data['assignment_type'] ?? 'writing') === 'hybrid'
            && (int) ($data['mcq_weight_percentage'] ?? 30) + (int) ($data['essay_weight_percentage'] ?? 70) !== 100) {
            return back()->withErrors(['essay_weight_percentage' => 'Jumlah bobot pilihan ganda dan esai harus 100%.'])->withInput();
        }

        $data['cpmk_outcomes'] = $this->selectedCpmkOutcomes($course, $data['cpmk_indices'] ?? []);
        $data['sections'] = $this->normalizeSections($course, $data['sections'] ?? []);
        if ($this->sectionWeightTotal($data['sections']) !== 0.0 && abs($this->sectionWeightTotal($data['sections']) - 100) > 0.01) {
            return back()->withErrors(['sections' => 'Jika bobot bagian digunakan, jumlahnya harus 100%.'])->withInput();
        }

        $createdAssignment = null;

        DB::transaction(function () use ($course, $data, $request, &$createdAssignment) {
            // Temukan atau buat wadah Proyek Tri Dharma untuk mata kuliah ini
            $project = Project::firstOrCreate(
                [
                    'owner_id' => $course->lecturer_id,
                    'academic_period_id' => $course->academic_period_id,
                    'name' => 'Perkuliahan: '.$course->name.' ('.$course->code.')',
                ],
                [
                    'description' => 'Wadah Tri Dharma pengajaran untuk mata kuliah '.$course->name.' kelas '.$course->class_group,
                    'pillars' => ['teaching'],
                    'status' => 'active',
                ]
            );

            $configJson = [];
            $configJson['cpmk_outcomes'] = $data['cpmk_outcomes'];
            $configJson['sections'] = $data['sections'];
            if (! empty($data['course_session_id'])) {
                $session = $course->sessions()->find($data['course_session_id']);
                if ($session) {
                    $configJson['course_session_id'] = $session->id;
                    $configJson['session_number'] = $session->session_number;
                    $configJson['session_topic'] = $session->topic;
                }
            }
            if ($request->hasFile('audio_file')) {
                $configJson['audio_file_path'] = $request->file('audio_file')->store("courses/{$course->id}/audio", 'local');
                $configJson['audio_max_plays'] = isset($data['audio_max_plays']) ? (int) $data['audio_max_plays'] : 2;
            }
            if (! empty($data['reading_passage'])) {
                $configJson['reading_passage'] = trim($data['reading_passage']);
            }
            if (! empty($data['source_text'])) {
                $configJson['source_text'] = trim($data['source_text']);
                $configJson['source_language'] = $data['source_language'] ?? 'Bahasa Indonesia';
                $configJson['target_language'] = $data['target_language'] ?? 'English';
            }

            $assignmentType = $data['assignment_type'] ?? 'writing';

            if ($assignmentType === 'milestone_artifact') {
                $milestones = [];
                $customTitles = $request->input('milestones', []);
                if (is_array($customTitles) && count($customTitles) > 0) {
                    foreach ($customTitles as $i => $item) {
                        $title = is_array($item) ? ($item['title'] ?? '') : $item;
                        if (trim((string) $title) !== '') {
                            $milestones[] = [
                                'id' => $i + 1,
                                'title' => trim((string) $title),
                                'due_date' => is_array($item) ? ($item['due_date'] ?? null) : null,
                            ];
                        }
                    }
                }
                if (empty($milestones)) {
                    // Default template milestone proposal riset/skripsi untuk Seminar ELT
                    $milestones = [
                        ['id' => 1, 'title' => 'Bab 1: Pendahuluan & Research Questions', 'due_date' => null],
                        ['id' => 2, 'title' => 'Bab 2: Kajian Pustaka & Kerangka Teori (LitSynth)', 'due_date' => null],
                        ['id' => 3, 'title' => 'Bab 3: Desain Metodologi & Instrumen Penelitian', 'due_date' => null],
                        ['id' => 4, 'title' => 'Naskah Final Proposal & Tautan Slide/Video Seminar', 'due_date' => null],
                    ];
                }
                $configJson['milestones'] = $milestones;
                $configJson['allow_media_url'] = true;
            }

            $assignment = $course->assignments()->create([
                'project_id' => $project->id,
                'created_by' => Auth::id(),
                'title' => $data['title'],
                'instructions' => $data['instructions'],
                'assignment_type' => $assignmentType,
                'time_limit_minutes' => ! empty($data['time_limit_minutes']) ? (int) $data['time_limit_minutes'] : null,
                'mcq_weight_percentage' => (int) ($data['mcq_weight_percentage'] ?? 30),
                'essay_weight_percentage' => (int) ($data['essay_weight_percentage'] ?? 70),
                'config_json' => ! empty($configJson) ? $configJson : null,
                'feedback_language' => $data['feedback_language'] ?? 'id',
                'feedback_level' => $data['feedback_level'] ?? 'indirect',
                'status' => 'published',
                'due_at' => $data['due_at'] ?? null,
                'allow_revisions' => $data['allow_revisions'] ?? true,
            ]);

            $createdAssignment = $assignment;

            // Daftarkan semua mahasiswa kelas ke tugas ini secara otomatis
            $studentIds = $course->students()->pluck('student_id')->all();
            if (! empty($studentIds)) {
                $assignment->students()->sync($studentIds);
            }

            // Buat snapshot versi 1
            $assignment->versions()->create([
                'version' => 1,
                'title' => $assignment->title,
                'instructions' => $assignment->instructions,
                'feedback_language' => $assignment->feedback_language,
                'feedback_level' => $assignment->feedback_level,
                'status' => $assignment->status,
                'due_at' => $assignment->due_at,
                'edited_by' => Auth::id(),
                'assessment_config' => ['cpmk_outcomes' => $configJson['cpmk_outcomes'] ?? [], 'sections' => $configJson['sections'] ?? []],
            ]);

            // Buat rubrik penilaian analitik default sesuai format tugas
            if ($assignmentType === 'translation') {
                $defaultRubricName = 'Rubrik Kualitas Penerjemahan (Translation Quality Assessment)';
                $criteria = [
                    ['name' => 'Accuracy & Faithfulness', 'description' => 'Ketepatan pengalihan pesan dari teks sumber (ST) ke teks sasaran (TT) tanpa distorsi, omisi (penghilangan), atau penambahan makna yang tidak semestinya.', 'weight' => 1.5],
                    ['name' => 'Acceptability & Naturalness', 'description' => 'Keberterimaan, kelancaran (fluency), dan kealamian bahasa sasaran sesuai kaidah dan gaya bahasa teks tanpa kekakuan interferensi bahasa sumber.', 'weight' => 1.0],
                    ['name' => 'Terminology & Register', 'description' => 'Ketepatan pemilihan padanan istilah teknis/spesifik bidang ilmu, konsistensi istilah, dan kesesuaian laras bahasa akademik/formal.', 'weight' => 1.0],
                    ['name' => 'Translation Strategies & Notes', 'description' => 'Keberhasilan penerapan prosedur penerjemahan (transposisi, modulasi, adaptasi) serta mutu pertimbangan pada Catatan Penerjemah (Translator\'s Notes).', 'weight' => 1.0],
                    ['name' => 'Target Mechanics & Style', 'description' => 'Ketepatan ejaan baku, tanda baca, tata bahasa sasaran, dan kepatuhan format teks terjemahan.', 'weight' => 0.5],
                ];
            } elseif ($assignmentType === 'milestone_artifact') {
                $defaultRubricName = 'Rubrik Evaluasi Proposal Penelitian & Presentasi';
                $criteria = [
                    ['name' => 'Research Problem & Urgency', 'description' => 'Kejelasan latar belakang, ketajaman gap penelitian, dan kejelasan rumusan masalah / research questions.', 'weight' => 1.0],
                    ['name' => 'Literature Synthesis & Theory', 'description' => 'Kedalaman sintesis pustaka relevan bereputasi, integrasi kajian terdahulu (LitSynth), dan kerangka berpikir teoretis.', 'weight' => 1.0],
                    ['name' => 'Methodology & Design', 'description' => 'Ketepatan desain penelitian, populasi/partisipan, prosedur pengumpulan data, dan rencana instrumen riset.', 'weight' => 1.0],
                    ['name' => 'Academic Presentation & Delivery', 'description' => 'Kerapian draf naskah, kualitas artefak slide/video presentasi seminar, dan artikulasi gagasan penelitian.', 'weight' => 1.0],
                ];
            } else {
                $defaultRubricName = 'Rubrik Analitik Penulisan Akademik';
                $criteria = [
                    ['name' => 'Content & Argument', 'description' => 'Kesesuaian isi, kejelasan tesis, dan kedalaman argumen.', 'weight' => 1.0],
                    ['name' => 'Organization & Structure', 'description' => 'Koherensi, transisi antar paragraf, dan alur pemikiran logis.', 'weight' => 1.0],
                    ['name' => 'Vocabulary & Lexicon', 'description' => 'Kekayaan kosakata akademik dan ketepatan istilah bidang studi.', 'weight' => 1.0],
                    ['name' => 'Language Use & Grammar', 'description' => 'Ketepatan tata bahasa, variasi kalimat, dan tanda baca.', 'weight' => 1.0],
                    ['name' => 'Mechanics & Academic Style', 'description' => 'Format sitasi, ejaan, kerapian, dan konvensi akademik.', 'weight' => 1.0],
                ];
            }

            $rubricName = (! empty($data['rubric_name']) && $data['rubric_name'] !== 'Rubrik Analitik Penulisan Akademik')
                ? $data['rubric_name']
                : $defaultRubricName;

            $assignment->rubric()->create([
                'version' => 1,
                'name' => $rubricName,
                'scale_min' => 1,
                'scale_max' => 5,
                'criteria' => $criteria,
            ]);

            // Kirim notifikasi tugas baru ke seluruh mahasiswa yang terdaftar di kelas
            $enrolledStudents = $course->students()->with('student')->get();
            foreach ($enrolledStudents as $cs) {
                if ($cs->student) {
                    $cs->student->notify(new AcademicNotification(
                        title: 'Tugas Perkuliahan Baru',
                        message: "Dosen telah menerbitkan tugas \"{$assignment->title}\" ({$assignment->typeLabel()}) pada mata kuliah {$course->name}.",
                        actionUrl: route('courses.show', ['course' => $course, 'tab' => 'assignments']),
                        icon: 'assignment',
                        category: 'assignment',
                        meta: ['course_id' => $course->id, 'assignment_id' => $assignment->id, 'assignment_type' => $assignment->assignment_type]
                    ));
                }
            }
        });

        if ($createdAssignment && in_array($createdAssignment->assignment_type, ['stimulus_exam', 'hybrid'])) {
            return redirect()->route('assignments.questions.index', $createdAssignment)->with('status', 'Tugas format '.$createdAssignment->typeLabel().' berhasil diterbitkan. Silakan tambahkan butir-butir pertanyaan di bawah.');
        }

        return back()->with('status', 'Tugas perkuliahan "'.$data['title'].'" berhasil diterbitkan untuk seluruh mahasiswa kelas.');
    }

    public function edit(Request $request, Course $course, WritingAssignment $assignment): View
    {
        abort_unless($request->user()->id === $course->lecturer_id || in_array($request->user()->role, ['admin', 'superadmin'], true), 403);
        abort_unless($assignment->course_id === $course->id, 404);

        $assignment->load(['rubric', 'questions', 'submissions.student']);

        return view('courses.assignments.edit', [
            'course' => $course,
            'assignment' => $assignment,
            'currentRubric' => $assignment->rubric()->latest('version')->first(),
        ]);
    }

    public function update(Request $request, Course $course, WritingAssignment $assignment): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || in_array($request->user()->role, ['admin', 'superadmin'], true), 403);
        abort_unless($assignment->course_id === $course->id, 404);

        $data = $request->validate([
            'course_session_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['required', 'string', 'max:10000'],
            'assignment_type' => ['nullable', 'string', 'in:writing,stimulus_exam,hybrid,translation,milestone_artifact'],
            'status' => ['required', 'string', 'in:draft,published'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:5', 'max:360'],
            'mcq_weight_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'essay_weight_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:51200'],
            'audio_max_plays' => ['nullable', 'integer', 'min:0', 'max:10'],
            'reading_passage' => ['nullable', 'string', 'max:20000'],
            'source_text' => ['nullable', 'string', 'max:20000'],
            'source_language' => ['nullable', 'string', 'max:60'],
            'target_language' => ['nullable', 'string', 'max:60'],
            'due_at' => ['nullable', 'date'],
            'feedback_language' => ['nullable', 'string', 'in:id,en'],
            'feedback_level' => ['nullable', 'string', 'in:direct,indirect,balanced'],
            'rubric_name' => ['nullable', 'string', 'max:120'],
            'cpmk_indices' => ['nullable', 'array'],
            'cpmk_indices.*' => ['integer', 'min:0'],
            'sections' => ['nullable', 'array', 'max:12'],
            'sections.*.title' => ['nullable', 'string', 'max:120'],
            'sections.*.prompt' => ['nullable', 'string', 'max:2000'],
            'sections.*.response_type' => ['nullable', 'in:long_text,url,audio_video,numeric'],
            'sections.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sections.*.required' => ['nullable', 'boolean'],
            'sections.*.cpmk_indices' => ['nullable', 'array'],
            'sections.*.cpmk_indices.*' => ['integer', 'min:0'],
            'delete_audio' => ['nullable', 'boolean'],
            'allow_revisions' => ['nullable', 'boolean'],
        ]);

        if (($data['assignment_type'] ?? $assignment->assignment_type) === 'hybrid'
            && (int) ($data['mcq_weight_percentage'] ?? 30) + (int) ($data['essay_weight_percentage'] ?? 70) !== 100) {
            return back()->withErrors(['essay_weight_percentage' => 'Jumlah bobot pilihan ganda dan esai harus 100%.'])->withInput();
        }

        $data['cpmk_outcomes'] = $this->selectedCpmkOutcomes($course, $data['cpmk_indices'] ?? []);
        $data['sections'] = $this->normalizeSections($course, $data['sections'] ?? []);
        if ($this->sectionWeightTotal($data['sections']) !== 0.0 && abs($this->sectionWeightTotal($data['sections']) - 100) > 0.01) {
            return back()->withErrors(['sections' => 'Jika bobot bagian digunakan, jumlahnya harus 100%.'])->withInput();
        }

        DB::transaction(function () use ($course, $assignment, $data, $request) {
            $configJson = $assignment->config_json ?? [];
            $configJson['cpmk_outcomes'] = $data['cpmk_outcomes'];
            $configJson['sections'] = $data['sections'];

            if (array_key_exists('course_session_id', $data)) {
                if (! empty($data['course_session_id'])) {
                    $session = $course->sessions()->find($data['course_session_id']);
                    if ($session) {
                        $configJson['course_session_id'] = $session->id;
                        $configJson['session_number'] = $session->session_number;
                        $configJson['session_topic'] = $session->topic;
                    }
                } else {
                    unset($configJson['course_session_id'], $configJson['session_number'], $configJson['session_topic']);
                }
            }

            if ($request->boolean('delete_audio')) {
                if (! empty($configJson['audio_file_path'])) {
                    foreach (['local', 'public'] as $disk) {
                        if (Storage::disk($disk)->exists($configJson['audio_file_path'])) {
                            Storage::disk($disk)->delete($configJson['audio_file_path']);
                        }
                    }
                }
                unset($configJson['audio_file_path']);
            }

            if ($request->hasFile('audio_file')) {
                if (! empty($configJson['audio_file_path'])) {
                    foreach (['local', 'public'] as $disk) {
                        if (Storage::disk($disk)->exists($configJson['audio_file_path'])) {
                            Storage::disk($disk)->delete($configJson['audio_file_path']);
                        }
                    }
                }
                $configJson['audio_file_path'] = $request->file('audio_file')->store("courses/{$course->id}/audio", 'local');
            }

            if (isset($data['audio_max_plays'])) {
                $configJson['audio_max_plays'] = (int) $data['audio_max_plays'];
            }

            if (array_key_exists('reading_passage', $data)) {
                if (! empty($data['reading_passage'])) {
                    $configJson['reading_passage'] = trim($data['reading_passage']);
                } else {
                    unset($configJson['reading_passage']);
                }
            }

            if (array_key_exists('source_text', $data)) {
                if (! empty($data['source_text'])) {
                    $configJson['source_text'] = trim($data['source_text']);
                    $configJson['source_language'] = $data['source_language'] ?? 'Bahasa Indonesia';
                    $configJson['target_language'] = $data['target_language'] ?? 'English';
                } else {
                    unset($configJson['source_text'], $configJson['source_language'], $configJson['target_language']);
                }
            }

            $assignmentType = $data['assignment_type'] ?? $assignment->assignment_type ?? 'writing';

            if ($assignmentType === 'milestone_artifact') {
                $customMilestones = $request->input('milestones', []);
                if (is_array($customMilestones) && count($customMilestones) > 0) {
                    $milestones = [];
                    foreach ($customMilestones as $i => $item) {
                        $mTitle = is_array($item) ? ($item['title'] ?? '') : $item;
                        if (trim((string) $mTitle) !== '') {
                            $milestones[] = [
                                'id' => $i + 1,
                                'title' => trim((string) $mTitle),
                                'due_date' => is_array($item) ? ($item['due_date'] ?? null) : null,
                            ];
                        }
                    }
                    if (! empty($milestones)) {
                        $configJson['milestones'] = $milestones;
                    }
                }
                $configJson['allow_media_url'] = true;
            }

            $assignment->update([
                'title' => $data['title'],
                'instructions' => $data['instructions'],
                'assignment_type' => $assignmentType,
                'status' => $data['status'],
                'time_limit_minutes' => ! empty($data['time_limit_minutes']) ? (int) $data['time_limit_minutes'] : null,
                'mcq_weight_percentage' => (int) ($data['mcq_weight_percentage'] ?? 30),
                'essay_weight_percentage' => (int) ($data['essay_weight_percentage'] ?? 70),
                'config_json' => ! empty($configJson) ? $configJson : null,
                'feedback_language' => $data['feedback_language'] ?? 'id',
                'feedback_level' => $data['feedback_level'] ?? 'indirect',
                'due_at' => $data['due_at'] ?? null,
                'allow_revisions' => array_key_exists('allow_revisions', $data)
                    ? (bool) $data['allow_revisions']
                    : $assignment->allow_revisions,
            ]);

            $nextVersion = (int) $assignment->versions()->max('version') + 1;
            $assignment->versions()->create([
                'version' => $nextVersion,
                'title' => $assignment->title,
                'instructions' => $assignment->instructions,
                'feedback_language' => $assignment->feedback_language,
                'feedback_level' => $assignment->feedback_level,
                'status' => $assignment->status,
                'due_at' => $assignment->due_at,
                'edited_by' => Auth::id(),
                'assessment_config' => ['cpmk_outcomes' => $configJson['cpmk_outcomes'] ?? [], 'sections' => $configJson['sections'] ?? []],
            ]);

            if (! empty($data['rubric_name'])) {
                $latestRubric = $assignment->rubric()->latest('version')->first();
                if ($latestRubric && $latestRubric->name !== $data['rubric_name']) {
                    $latestRubric->update(['name' => $data['rubric_name']]);
                }
            }
        });

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'assignments'])
            ->with('status', 'Tugas perkuliahan "'.$assignment->title.'" berhasil diperbarui.');
    }

    /** @return array<int, string> */
    private function selectedCpmkOutcomes(Course $course, array $indices): array
    {
        $outcomes = $course->cpmkList();

        return collect($indices)->map(fn ($index) => $outcomes[(int) $index] ?? null)
            ->filter(fn ($outcome) => is_string($outcome) && trim($outcome) !== '')
            ->map(fn ($outcome) => trim($outcome))->unique()->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function normalizeSections(Course $course, array $sections): array
    {
        return collect($sections)->values()->map(function ($section, $index) use ($course) {
            if (! is_array($section) || trim((string) ($section['title'] ?? '')) === '') {
                return null;
            }

            return [
                'key' => 's'.$index,
                'title' => trim($section['title']),
                'prompt' => trim((string) ($section['prompt'] ?? '')),
                'response_type' => in_array($section['response_type'] ?? '', ['long_text', 'url', 'audio_video', 'numeric'], true)
                    ? $section['response_type'] : 'long_text',
                'weight' => (float) ($section['weight'] ?? 0),
                'required' => filter_var($section['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'cpmk_outcomes' => $this->selectedCpmkOutcomes($course, $section['cpmk_indices'] ?? []),
            ];
        })->filter()->values()->all();
    }

    private function sectionWeightTotal(array $sections): float
    {
        return round(collect($sections)->sum(fn ($section) => (float) ($section['weight'] ?? 0)), 2);
    }

    public function destroy(Request $request, Course $course, WritingAssignment $assignment): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || in_array($request->user()->role, ['admin', 'superadmin'], true), 403);
        abort_unless($assignment->course_id === $course->id, 404);

        $title = $assignment->title;

        DB::transaction(function () use ($course, $assignment) {
            if (! empty($assignment->config_json['audio_file_path'])) {
                foreach (['local', 'public'] as $disk) {
                    if (Storage::disk($disk)->exists($assignment->config_json['audio_file_path'])) {
                        Storage::disk($disk)->delete($assignment->config_json['audio_file_path']);
                    }
                }
            }

            $affectedStudentIds = $assignment->submissions()->pluck('student_id')->all();

            $assignment->delete();

            foreach ($affectedStudentIds as $studentId) {
                $this->recalculateCourseStudentGrade($course, $studentId);
            }
        });

        return redirect()
            ->route('courses.show', ['course' => $course, 'tab' => 'assignments'])
            ->with('status', 'Tugas perkuliahan "'.$title.'" berhasil dihapus.');
    }

    public function grade(Request $request, Course $course, WritingAssignment $assignment, WritingSubmission $submission): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || in_array($request->user()->role, ['admin', 'superadmin'], true), 403);
        abort_unless($assignment->course_id === $course->id && $submission->assignment_id === $assignment->id, 404);
        abort_unless(in_array($submission->status, ['submitted', 'graded'], true), 409, 'Draf mahasiswa belum dapat dinilai sebelum dikirim.');

        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback_notes' => ['nullable', 'string', 'max:5000'],
            'criteria_scores' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($course, $assignment, $submission, $data) {
            $submission->update([
                'score' => $data['score'],
                'graded_by' => Auth::id(),
                'graded_at' => now(),
                'status' => 'graded',
            ]);

            if ($assignment->isStimulusExam() || $assignment->isHybrid()) {
                WritingSubmissionAttempt::updateOrCreate(
                    ['submission_id' => $submission->id, 'attempt_number' => $submission->current_attempt ?: 1],
                    [
                        'status' => 'graded',
                        'submitted_at' => $submission->submitted_at,
                        'score' => $data['score'],
                        'graded_by' => Auth::id(),
                        'graded_at' => now(),
                    ]
                );
            }

            // Jika ada revisi terbaru, simpan atau mutakhirkan laporan feedback dosen
            $latestRevision = $submission->latestRevision();
            if ($latestRevision) {
                $feedbackReport = $latestRevision->feedbackReports()->first();
                $payload = [
                    'overall_summary' => trim((string) ($data['feedback_notes'] ?? '')),
                    'score' => $data['score'],
                    'criteria_scores' => $data['criteria_scores'] ?? [],
                    'evaluated_by' => Auth::user()->name,
                ];

                if ($feedbackReport) {
                    $feedbackReport->update([
                        'feedback_json' => $payload,
                        'edited_by' => Auth::id(),
                        'status' => 'published',
                        'published_at' => now(),
                    ]);
                } else {
                    $latestRevision->feedbackReports()->create([
                        'edited_by' => Auth::id(),
                        'feedback_json' => $payload,
                        'prompt_version' => 'manual-evaluation-v1',
                        'status' => 'published',
                        'published_at' => now(),
                    ]);
                }
            }

            // Hitung ulang akumulasi nilai akhir mahasiswa pada kelas ini
            $this->recalculateCourseStudentGrade($course, $submission->student_id);

            // Kirim notifikasi hasil penilaian ke akun mahasiswa
            if ($submission->student) {
                $submission->student->notify(new AcademicNotification(
                    title: 'Nilai & Evaluasi Tugas Dirilis',
                    message: "Tugas Anda untuk \"{$assignment->title}\" ({$course->name}) telah dinilai. Nilai: {$data['score']}/100.",
                    actionUrl: route('assignments.show', $assignment),
                    icon: 'grade',
                    category: 'grade',
                    meta: ['course_id' => $course->id, 'assignment_id' => $assignment->id, 'score' => $data['score']]
                ));
            }
        });

        return back()->with('status', 'Nilai ('.$data['score'].') dan umpan balik tugas berhasil disimpan.');
    }

    public function printGrades(Request $request, Course $course): View
    {
        $user = $request->user();
        abort_unless($user->isStaff() && ($user->id === $course->lecturer_id || in_array($user->role, ['admin', 'superadmin'], true)), 403);

        $course->load([
            'academicPeriod',
            'studyProgram.faculty',
            'lecturer.lecturerProfile',
            'assignments.submissions',
            'students.student',
        ]);

        return view('courses.grades-print', [
            'course' => $course,
            'students' => $course->students,
            'assignments' => $course->assignments,
            'lecturer' => $course->lecturer,
            'profile' => $course->lecturer->lecturerProfile,
        ]);
    }

    public function exportGrades(Request $request, Course $course): StreamedResponse
    {
        abort_unless($request->user()->isStaff() && ($request->user()->id === $course->lecturer_id || in_array($request->user()->role, ['admin', 'superadmin'], true)), 403, 'Akses ekspor nilai hanya untuk dosen pengampu dan administrator.');

        $course->load([
            'academicPeriod',
            'studyProgram.faculty',
            'lecturer.lecturerProfile',
            'assignments.submissions',
            'students.student.studyProgram',
        ]);

        $assignments = $course->assignments;
        $students = $course->students;

        $safeClass = str_replace(['/', '\\', ' '], '-', $course->class_group ?: 'A');
        $safeCode = str_replace(['/', '\\', ' '], '-', $course->code);
        $filename = "rekap-nilai-{$safeCode}-{$safeClass}-".now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($course, $assignments, $students) {
            $handle = fopen('php://output', 'w');

            // Tulis UTF-8 BOM agar Microsoft Excel di Windows/Mac otomatis mengenali encoding dengan tepat
            fwrite($handle, "\xEF\xBB\xBF");

            // Baris Metadata Perkuliahan
            fputcsv($handle, ['REKAPITULASI EVALUASI & NILAI AKHIR MAHASISWA']);
            fputcsv($handle, ['Mata Kuliah', $course->code.' - '.$course->name.' ('.($course->class_group ?: 'A').')']);
            fputcsv($handle, ['Program Studi', ($course->studyProgram?->name ?? '-').' ('.($course->studyProgram?->faculty?->name ?? '-').')']);
            fputcsv($handle, ['Semester / Periode', $course->academicPeriod?->name ?? '-']);
            fputcsv($handle, ['Bobot SKS', $course->credit_hours.' SKS']);
            fputcsv($handle, ['Dosen Pengampu', $course->lecturer->name.($course->lecturer->lecturerProfile?->nidn ? ' (NIDN: '.$course->lecturer->lecturerProfile->nidn.')' : '')]);
            fputcsv($handle, ['Waktu Ekspor', now()->translatedFormat('d F Y, H:i:s')]);
            fputcsv($handle, []);

            // Baris Header Kolom Tabel
            $headers = ['No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Angkatan'];
            foreach ($assignments as $idx => $assignment) {
                $headers[] = 'T'.($idx + 1).': '.$assignment->title;
            }
            $headers[] = 'Presensi Kehadiran (%)';
            $headers[] = 'Nilai Akhir (0-100)';
            $headers[] = 'Huruf Mutu';
            $headers[] = 'Status Kelulusan';
            fputcsv($handle, $headers);

            // Baris Data Mahasiswa
            $gradedCount = 0;
            $passedCount = 0;
            $totalScore = 0;
            $maxScore = null;
            $minScore = null;

            foreach ($students as $idx => $cs) {
                $studentUser = $cs->student;
                $row = [
                    $idx + 1,
                    '="'.($cs->nim ?: ($studentUser->nim ?: '-')).'"',
                    $studentUser->name,
                    $studentUser->studyProgram?->code ?: ($course->studyProgram?->code ?: '-'),
                    $studentUser->cohort_year ?: '-',
                ];

                foreach ($assignments as $assignment) {
                    $sub = $assignment->submissions->firstWhere('student_id', $cs->student_id);
                    if ($sub && $sub->score !== null) {
                        $row[] = number_format($sub->score, 1, '.', '');
                    } elseif ($sub) {
                        $row[] = 'Draft';
                    } else {
                        $row[] = '-';
                    }
                }

                $row[] = number_format($cs->attendance_rate, 1, '.', '').'%';

                if ($cs->final_grade !== null) {
                    $row[] = number_format($cs->final_grade, 1, '.', '');
                    $gradedCount++;
                    $totalScore += $cs->final_grade;
                    $maxScore = $maxScore === null ? $cs->final_grade : max($maxScore, $cs->final_grade);
                    $minScore = $minScore === null ? $cs->final_grade : min($minScore, $cs->final_grade);
                } else {
                    $row[] = '-';
                }

                $row[] = $cs->letter_grade ?: '-';

                if ($cs->final_grade !== null) {
                    $isPassed = $cs->final_grade >= 55.0;
                    if ($isPassed) {
                        $passedCount++;
                    }
                    $row[] = $isPassed ? 'LULUS' : 'TIDAK LULUS';
                } else {
                    $row[] = 'BELUM DINILAI';
                }

                fputcsv($handle, $row);
            }

            // Ringkasan Statistik Kelas
            fputcsv($handle, []);
            fputcsv($handle, ['RINGKASAN STATISTIK KELAS']);
            fputcsv($handle, ['Total Mahasiswa Terdaftar', $students->count().' Orang']);
            fputcsv($handle, ['Mahasiswa Sudah Dinilai', $gradedCount.' Orang']);
            fputcsv($handle, ['Rata-rata Nilai Kelas', $gradedCount > 0 ? number_format($totalScore / $gradedCount, 1, '.', '') : '-']);
            fputcsv($handle, ['Nilai Tertinggi', $maxScore !== null ? number_format($maxScore, 1, '.', '') : '-']);
            fputcsv($handle, ['Nilai Terendah', $minScore !== null ? number_format($minScore, 1, '.', '') : '-']);
            fputcsv($handle, ['Jumlah Mahasiswa Lulus', $passedCount.' Orang']);
            fputcsv($handle, ['Tingkat Kelulusan', $gradedCount > 0 ? number_format(($passedCount / $gradedCount) * 100, 1, '.', '').'%' : '-']);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function recalculateCourseStudentGrade(Course $course, int $studentId): void
    {
        $courseStudent = CourseStudent::where('course_id', $course->id)->where('student_id', $studentId)->first();
        if (! $courseStudent) {
            return;
        }

        // Ambil semua submisi tugas mahasiswa pada mata kuliah ini
        $scores = WritingSubmission::whereHas('assignment', fn ($q) => $q->where('course_id', $course->id))
            ->where('student_id', $studentId)
            ->whereNotNull('score')
            ->pluck('score');

        if ($scores->isNotEmpty()) {
            $average = round($scores->average(), 2);
            $letter = match (true) {
                $average >= 85.0 => 'A',
                $average >= 80.0 => 'A-',
                $average >= 75.0 => 'B+',
                $average >= 70.0 => 'B',
                $average >= 65.0 => 'B-',
                $average >= 60.0 => 'C+',
                $average >= 55.0 => 'C',
                $average >= 40.0 => 'D',
                default => 'E',
            };

            $courseStudent->update([
                'final_grade' => $average,
                'letter_grade' => $letter,
            ]);
        } else {
            $courseStudent->update([
                'final_grade' => null,
                'letter_grade' => null,
            ]);
        }
    }

    /**
     * Endpoint API AJAX untuk generate rancangan tugas & rubrik perkuliahan dengan AI.
     */
    public function generateWithAi(Request $request, Course $course, AssignmentAiService $aiService): JsonResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || in_array($request->user()->role, ['admin', 'superadmin'], true), 403);

        $validated = $request->validate([
            'session_id' => ['nullable', 'integer'],
            'topic' => ['nullable', 'string', 'max:255'],
            'assignment_focus' => ['nullable', 'string', 'max:255'],
            'assignment_type' => ['nullable', 'string', 'in:writing,stimulus_exam,hybrid,translation,milestone_artifact'],
            'bloom_level' => ['nullable', 'string', 'max:60'],
            'include_rubric' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'provider' => ['nullable', 'string', 'in:deepseek,gemini,openai'],
        ]);

        $sessionContext = [];
        if (! empty($validated['session_id'])) {
            $session = $course->sessions()->find($validated['session_id']);
            if (! $session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi pertemuan perkuliahan yang dipilih tidak ditemukan pada kelas ini.',
                ], 422);
            }

            $sessionContext['session_id'] = $session->id;
            $sessionContext['session_number'] = $session->session_number;
            $sessionContext['meeting_topic'] = $session->topic;
            $sessionContext['meeting_material'] = trim(
                ($session->teaching_method ? "Metode Ajar: {$session->teaching_method}. " : '').
                ($session->notes ? "Catatan Sesi: {$session->notes}" : '')
            );

            // Ringkasan bahan ajar terkait sesi pertemuan tersebut jika ada
            $materials = $course->materials()
                ->where('session_number', $session->session_number)
                ->get(['title', 'material_type', 'description']);

            if ($materials->isNotEmpty()) {
                $sessionContext['materials_summary'] = $materials->map(function ($m) {
                    return "- {$m->title} ({$m->material_type})".($m->description ? ": {$m->description}" : '');
                })->implode("\n");
            }
        }

        $topic = $sessionContext['meeting_topic'] ?? ($validated['topic'] ?? null);
        if (empty($topic)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih sesi pertemuan perkuliahan atau masukkan topik penugasan terlebih dahulu.',
            ], 422);
        }

        $params = array_merge($validated, $sessionContext);

        try {
            $draft = $aiService->generateAssignmentDraft($course, $params);

            return response()->json([
                'success' => true,
                'data' => $draft,
                'provider' => $validated['provider'] ?? config('ai.assignment_provider', 'deepseek'),
                'session' => $sessionContext ? [
                    'id' => $sessionContext['session_id'] ?? null,
                    'session_number' => $sessionContext['session_number'] ?? null,
                    'topic' => $sessionContext['meeting_topic'] ?? null,
                ] : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
