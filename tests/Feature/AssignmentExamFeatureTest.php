<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AssignmentQuestion;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\Project;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentExamFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $student;

    private Course $course;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $faculty = Faculty::create(['name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'code' => 'FKIP']);
        $studyProgram = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'name' => 'Pendidikan Bahasa Inggris',
            'code' => 'PBI-S1',
            'level' => 'S1',
        ]);

        $this->lecturer = User::factory()->create(['role' => 'lecturer', 'name' => 'Dr. Hasan Basri']);
        LecturerProfile::create([
            'user_id' => $this->lecturer->id,
            'study_program_id' => $studyProgram->id,
            'nidn' => '0099887766',
            'target_sks' => 12,
        ]);

        $period = AcademicPeriod::create([
            'name' => '2026/2027 Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'academic_period_id' => $period->id,
            'study_program_id' => $studyProgram->id,
            'lecturer_id' => $this->lecturer->id,
            'code' => 'ENG6328',
            'name' => 'Listening for Academic Purposes',
            'credit_hours' => 3,
            'class_group' => 'A - 2025',
        ]);

        $this->project = Project::create([
            'owner_id' => $this->lecturer->id,
            'name' => 'Writewise Container - '.$this->course->code,
            'status' => 'active',
            'pillars' => ['teaching'],
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Bambang Pamungkas',
            'nim' => '2101010077',
            'study_program_id' => $studyProgram->id,
        ]);

        CourseStudent::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'nim' => '2101010077',
            'attendance_rate' => 100.0,
        ]);
    }

    public function test_lecturer_can_create_stimulus_listening_assignment_and_redirects_to_question_builder(): void
    {
        $fakeAudio = UploadedFile::fake()->create('academic_lecture.mp3', 1024, 'audio/mpeg');

        $response = $this->actingAs($this->lecturer)->post(route('courses.assignments.store', $this->course), [
            'title' => 'Academic Lecture Listening Practice 1',
            'instructions' => 'Dengarkan monolog kuliah tentang sosiolinguistik dan jawab pertanyaan.',
            'assignment_type' => 'stimulus_exam',
            'time_limit_minutes' => 40,
            'audio_file' => $fakeAudio,
            'audio_max_plays' => 2,
        ]);

        $assignment = WritingAssignment::where('course_id', $this->course->id)->firstOrFail();
        $this->assertTrue($assignment->isStimulusExam());
        $this->assertEquals(40, $assignment->time_limit_minutes);
        $this->assertEquals(2, $assignment->config_json['audio_max_plays']);
        $this->assertNotEmpty($assignment->config_json['audio_file_path']);
        Storage::disk('public')->assertExists($assignment->config_json['audio_file_path']);

        $response->assertRedirect(route('assignments.questions.index', $assignment));
    }

    public function test_lecturer_can_add_multiple_choice_questions_to_assignment(): void
    {
        $assignment = WritingAssignment::create([
            'course_id' => $this->course->id,
            'project_id' => $this->project->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Listening Comprehension Section 1',
            'instructions' => 'Petunjuk...',
            'assignment_type' => 'stimulus_exam',
        ]);

        $response = $this->actingAs($this->lecturer)
            ->post(route('assignments.questions.store', $assignment), [
                'question_type' => 'multiple_choice',
                'prompt' => 'What is the main finding regarding bilingual code-switching presented in the lecture?',
                'options' => [
                    'A' => 'It signifies poor linguistic competence.',
                    'B' => 'It follows grammatical constraints and demonstrates pragmatic agility.',
                    'C' => 'It is restricted only to informal colloquial environments.',
                    'D' => 'It leads to systemic language attrition in the first generation.',
                ],
                'answer_key' => 'B',
                'points' => 10,
                'explanation' => 'The lecturer cites Poplack (1980) on equivalence constraint.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $question = AssignmentQuestion::where('assignment_id', $assignment->id)->firstOrFail();
        $this->assertEquals('B', $question->answer_key);
        $this->assertEquals(10, $question->points);
        $this->assertCount(4, $question->options_json);
    }

    public function test_student_can_take_and_submit_stimulus_exam_with_instant_auto_grading(): void
    {
        $fakeAudio = UploadedFile::fake()->create('lecture_test.mp3', 512, 'audio/mpeg');
        $path = $fakeAudio->store("courses/{$this->course->id}/audio", 'public');

        $assignment = WritingAssignment::create([
            'course_id' => $this->course->id,
            'project_id' => $this->project->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Listening Exam 1',
            'instructions' => 'Dengarkan audio dan jawab soal.',
            'assignment_type' => 'stimulus_exam',
            'config_json' => [
                'audio_file_path' => $path,
                'audio_max_plays' => 2,
            ],
            'status' => 'published',
        ]);

        $q1 = AssignmentQuestion::create([
            'assignment_id' => $assignment->id,
            'question_type' => 'multiple_choice',
            'prompt' => 'Question 1',
            'options_json' => [
                ['key' => 'A', 'text' => 'Option A'],
                ['key' => 'B', 'text' => 'Option B'],
            ],
            'answer_key' => 'A',
            'points' => 10,
            'order' => 1,
        ]);

        $q2 = AssignmentQuestion::create([
            'assignment_id' => $assignment->id,
            'question_type' => 'multiple_choice',
            'prompt' => 'Question 2',
            'options_json' => [
                ['key' => 'A', 'text' => 'Option A'],
                ['key' => 'B', 'text' => 'Option B'],
            ],
            'answer_key' => 'B',
            'points' => 10,
            'order' => 2,
        ]);

        // Student opens the exam page
        $takeResponse = $this->actingAs($this->student)->get(route('assignments.exam.take', $assignment));
        $takeResponse->assertOk();
        $takeResponse->assertSee('Listening Exam 1');

        // Test audio streaming endpoint
        $audioResponse = $this->actingAs($this->student)->get(route('assignments.audio-stream', $assignment));
        $audioResponse->assertOk();

        // Student submits answers: Q1 correct (A), Q2 incorrect (A instead of B)
        $submitResponse = $this->actingAs($this->student)->post(route('assignments.exam.submit', $assignment), [
            'answers' => [
                $q1->id => 'A',
                $q2->id => 'A',
            ],
        ]);

        $submitResponse->assertRedirect(route('assignments.exam.take', $assignment));

        $submission = WritingSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $this->student->id)
            ->firstOrFail();

        // 1 of 2 questions correct = 50.0%
        $this->assertEquals(50.0, $submission->mcq_score);
        $this->assertEquals(50.0, $submission->score);
        $this->assertEquals('graded', $submission->status);
        $this->assertEquals('D', $submission->letterGrade());

        // Verify CourseStudent grade was updated
        $courseStudent = CourseStudent::where('course_id', $this->course->id)
            ->where('student_id', $this->student->id)
            ->firstOrFail();
        $this->assertEquals(50.0, $courseStudent->final_grade);
        $this->assertEquals('D', $courseStudent->letter_grade);
    }

    public function test_hybrid_assessment_flow_with_mcq_and_essay(): void
    {
        $hybridAssignment = WritingAssignment::create([
            'course_id' => $this->course->id,
            'project_id' => $this->project->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Bahasa Inggris Hukum: Midterm Exam',
            'instructions' => 'Seksi 1: PG (30%), Seksi 2: Studi Kasus (70%)',
            'assignment_type' => 'hybrid',
            'mcq_weight_percentage' => 30,
            'essay_weight_percentage' => 70,
            'status' => 'published',
        ]);

        $q1 = AssignmentQuestion::create([
            'assignment_id' => $hybridAssignment->id,
            'question_type' => 'multiple_choice',
            'prompt' => 'Which term denotes a legally binding contract violation?',
            'options_json' => [
                ['key' => 'A', 'text' => 'Breach of contract'],
                ['key' => 'B', 'text' => 'Tortious liability'],
            ],
            'answer_key' => 'A',
            'points' => 10,
            'order' => 1,
        ]);

        // Student submits answers for Section 1 (MCQ) and Section 2 (Essay)
        $submitResponse = $this->actingAs($this->student)->post(route('assignments.exam.submit', $hybridAssignment), [
            'answers' => [
                $q1->id => 'A', // 100% MCQ score
            ],
            'essay_answer' => 'Based on the facts of the scenario, Party A has materially breached Clause 4 of the commercial lease agreement by failing to provide notice within 14 calendar days.',
        ]);

        $submitResponse->assertRedirect(route('assignments.exam.take', $hybridAssignment));

        $submission = WritingSubmission::where('assignment_id', $hybridAssignment->id)
            ->where('student_id', $this->student->id)
            ->firstOrFail();

        $this->assertEquals(100.0, $submission->mcq_score);
        $this->assertEquals('submitted', $submission->status); // Waiting for lecturer essay grade
        $this->assertEquals(1, $submission->revisions()->count());
        $this->assertStringContainsString('Party A has materially breached', $submission->revisions()->first()->body_text);

        // Now lecturer grades the essay via CourseAssignmentController::grade
        $this->actingAs($this->lecturer)->post(
            route('courses.assignments.grade', [
                'course' => $this->course,
                'assignment' => $hybridAssignment,
                'submission' => $submission,
            ]),
            [
                'score' => 80.0, // Lecturer gives 80 on essay
                'feedback_notes' => 'Good legal analysis, correct contractual reasoning.',
            ]
        );

        $submission->refresh();
        $this->assertEquals('graded', $submission->status);
        $this->assertEquals(80.0, $submission->score);
    }
}
