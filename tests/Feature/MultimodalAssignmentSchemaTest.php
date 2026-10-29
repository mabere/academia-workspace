<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AssignmentQuestion;
use App\Models\AssignmentQuestionAnswer;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\LecturerProfile;
use App\Models\Project;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultimodalAssignmentSchemaTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private User $student;

    private Course $course;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $faculty = Faculty::create(['name' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'code' => 'FKIP']);
        $studyProgram = StudyProgram::create([
            'faculty_id' => $faculty->id,
            'name' => 'Pendidikan Bahasa Inggris',
            'code' => 'PBI-S1',
            'level' => 'S1',
        ]);

        $this->lecturer = User::factory()->create(['role' => 'lecturer', 'name' => 'Dr. Ahmad Fauzi']);
        LecturerProfile::create([
            'user_id' => $this->lecturer->id,
            'study_program_id' => $studyProgram->id,
            'nidn' => '0011223344',
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

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Nurul Hidayah',
            'nim' => '2101010099',
            'study_program_id' => $studyProgram->id,
        ]);

        $this->project = Project::create([
            'owner_id' => $this->lecturer->id,
            'name' => 'Writewise Assessment Container - '.$this->course->code,
            'status' => 'active',
            'pillars' => ['teaching', 'research'],
        ]);
    }

    public function test_can_create_stimulus_listening_assignment_with_config(): void
    {
        $assignment = WritingAssignment::create([
            'course_id' => $this->course->id,
            'project_id' => $this->project->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Midterm Exam: Academic Lecture Listening Comprehension',
            'instructions' => 'Dengarkan rekaman kuliah linguistik berikut dan jawab 5 pertanyaan pilihan ganda.',
            'assignment_type' => 'stimulus_exam',
            'time_limit_minutes' => 45,
            'config_json' => [
                'audio_file_path' => 'audio/academic_lecture_01.mp3',
                'audio_max_plays' => 2,
            ],
            'status' => 'published',
        ]);

        $this->assertTrue($assignment->isStimulusExam());
        $this->assertFalse($assignment->isWriting());
        $this->assertEquals('Ujian Stimulus / Listening', $assignment->typeLabel());
        $this->assertEquals(45, $assignment->time_limit_minutes);
        $this->assertEquals(2, $assignment->config_json['audio_max_plays']);
    }

    public function test_can_create_questions_and_record_student_answers(): void
    {
        $assignment = WritingAssignment::create([
            'course_id' => $this->course->id,
            'project_id' => $this->project->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Listening Comprehension Section 1',
            'instructions' => 'Pilih jawaban yang paling tepat.',
            'assignment_type' => 'stimulus_exam',
        ]);

        $q1 = AssignmentQuestion::create([
            'assignment_id' => $assignment->id,
            'question_type' => 'multiple_choice',
            'stimulus_type' => 'audio',
            'prompt' => 'What is the main topic of the professor\'s opening remarks?',
            'options_json' => [
                ['key' => 'A', 'text' => 'The historical evolution of generative syntax.'],
                ['key' => 'B', 'text' => 'Language acquisition mechanisms in infants.'],
                ['key' => 'C', 'text' => 'Phonological variations in sociolinguistics.'],
                ['key' => 'D', 'text' => 'Pragmatics in intercultural discourse.'],
            ],
            'answer_key' => 'B',
            'points' => 10,
            'order' => 1,
            'explanation' => 'The professor clearly states the lecture focuses on Chomsky\'s LAD in early childhood.',
        ]);

        $this->assertEquals(1, $assignment->questions()->count());
        $this->assertTrue($q1->isMultipleChoice());

        $submission = WritingSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        $answer = AssignmentQuestionAnswer::create([
            'submission_id' => $submission->id,
            'question_id' => $q1->id,
            'student_answer' => 'B',
            'is_correct' => true,
            'score_awarded' => 10.0,
        ]);

        $this->assertEquals(1, $submission->answers()->count());
        $this->assertTrue($answer->is_correct);
        $this->assertEquals('B', $answer->student_answer);
    }

    public function test_composite_score_calculation_for_hybrid_assessment(): void
    {
        $hybridAssignment = WritingAssignment::create([
            'course_id' => $this->course->id,
            'project_id' => $this->project->id,
            'created_by' => $this->lecturer->id,
            'title' => 'Bahasa Inggris Hukum: Mid-Semester Test',
            'instructions' => 'Seksi 1: Pilihan Ganda (30%), Seksi 2: Studi Kasus Hukum (70%)',
            'assignment_type' => 'hybrid',
            'mcq_weight_percentage' => 30,
            'essay_weight_percentage' => 70,
        ]);

        $this->assertTrue($hybridAssignment->isHybrid());
        $this->assertEquals('Penilaian Campuran (PG + Esai)', $hybridAssignment->typeLabel());

        $submission = WritingSubmission::create([
            'assignment_id' => $hybridAssignment->id,
            'student_id' => $this->student->id,
            'mcq_score' => 80.0, // 80 * 0.30 = 24.0
            'essay_score' => 90.0, // 90 * 0.70 = 63.0
            'status' => 'graded',
        ]);

        $finalScore = $submission->calculateCompositeScore();

        // 24.0 + 63.0 = 87.0
        $this->assertEquals(87.0, $finalScore);
        $this->assertEquals(87.0, $submission->score);
        $this->assertEquals('A', $submission->letterGrade());
    }
}
