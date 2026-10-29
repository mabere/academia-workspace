<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Project;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use App\Notifications\AcademicNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseAssignmentController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['required', 'string', 'max:10000'],
            'due_at' => ['nullable', 'date'],
            'feedback_language' => ['nullable', 'string', 'in:id,en'],
            'feedback_level' => ['nullable', 'string', 'in:direct,indirect,balanced'],
            'rubric_name' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($course, $data) {
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

            $assignment = $course->assignments()->create([
                'project_id' => $project->id,
                'created_by' => Auth::id(),
                'title' => $data['title'],
                'instructions' => $data['instructions'],
                'feedback_language' => $data['feedback_language'] ?? 'id',
                'feedback_level' => $data['feedback_level'] ?? 'indirect',
                'status' => 'published',
                'due_at' => $data['due_at'] ?? null,
            ]);

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
                'created_by' => Auth::id(),
            ]);

            // Buat rubrik penilaian analitik default
            $criteria = [
                ['name' => 'Content & Argument', 'description' => 'Kesesuaian isi, kejelasan tesis, dan kedalaman argumen.', 'weight' => 1.0],
                ['name' => 'Organization & Structure', 'description' => 'Koherensi, transisi antar paragraf, dan alur pemikiran logis.', 'weight' => 1.0],
                ['name' => 'Vocabulary & Lexicon', 'description' => 'Kekayaan kosakata akademik dan ketepatan istilah bidang studi.', 'weight' => 1.0],
                ['name' => 'Language Use & Grammar', 'description' => 'Ketepatan tata bahasa, variasi kalimat, dan tanda baca.', 'weight' => 1.0],
                ['name' => 'Mechanics & Academic Style', 'description' => 'Format sitasi, ejaan, kerapian, dan konvensi akademik.', 'weight' => 1.0],
            ];

            $assignment->rubric()->create([
                'version' => 1,
                'name' => ! empty($data['rubric_name']) ? $data['rubric_name'] : 'Rubrik Analitik Penulisan Akademik',
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
                        message: "Dosen telah menerbitkan tugas \"{$assignment->title}\" pada mata kuliah {$course->name}.",
                        actionUrl: route('courses.show', ['course' => $course, 'tab' => 'assignments']),
                        icon: 'assignment',
                        category: 'assignment',
                        meta: ['course_id' => $course->id, 'assignment_id' => $assignment->id]
                    ));
                }
            }
        });

        return back()->with('status', 'Tugas perkuliahan "'.$data['title'].'" berhasil diterbitkan untuk seluruh mahasiswa kelas.');
    }

    public function grade(Request $request, Course $course, WritingAssignment $assignment, WritingSubmission $submission): RedirectResponse
    {
        abort_unless($request->user()->id === $course->lecturer_id || $request->user()->role === 'admin', 403);

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

            // Jika ada revisi terbaru, simpan atau mutakhirkan laporan feedback dosen
            $latestRevision = $submission->revisions()->latest('revision_number')->first();
            if ($latestRevision) {
                $feedbackReport = $latestRevision->feedbackReports()->first();
                $payload = [
                    'overall_summary' => ($data['feedback_notes'] ?? null) ?: 'Penilaian dan umpan balik tugas oleh dosen pengampu.',
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
                    message: "Draf tugas Anda untuk \"{$assignment->title}\" ({$course->name}) telah dinilai dosen. Nilai: {$data['score']}/100.",
                    actionUrl: route('courses.show', ['course' => $course, 'tab' => 'assignments']),
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
        if (! $user->isStaff()) {
            abort_unless($course->students()->where('student_id', $user->id)->exists(), 403);
        }

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
        }
    }
}
