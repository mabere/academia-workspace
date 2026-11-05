<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('writing_assignments', 'allow_revisions')) {
            Schema::table('writing_assignments', function (Blueprint $table) {
                $table->boolean('allow_revisions')->default(true);
            });
        }

        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->string('status', 24)->default('draft')->change();
            if (! Schema::hasColumn('writing_submissions', 'current_attempt')) {
                $table->unsignedSmallInteger('current_attempt')->default(1);
            }
            if (! Schema::hasColumn('writing_submissions', 'draft_body_text')) {
                $table->longText('draft_body_text')->nullable();
            }
            if (! Schema::hasColumn('writing_submissions', 'draft_artifact_url')) {
                $table->string('draft_artifact_url', 500)->nullable();
            }
            if (! Schema::hasColumn('writing_submissions', 'draft_milestone_id')) {
                $table->unsignedSmallInteger('draft_milestone_id')->nullable();
            }
            if (! Schema::hasColumn('writing_submissions', 'draft_notes')) {
                $table->text('draft_notes')->nullable();
            }
            if (! Schema::hasColumn('writing_submissions', 'draft_saved_at')) {
                $table->timestamp('draft_saved_at')->nullable();
            }
        });

        if (! Schema::hasColumn('assignment_question_answers', 'attempt_number')) {
            Schema::table('assignment_question_answers', function (Blueprint $table) {
                $table->unsignedSmallInteger('attempt_number')->default(1);
            });
        }

        // Buat indeks unik baru terlebih dahulu agar foreign key submission_id di MySQL tetap memiliki indeks penopang
        if (! Schema::hasIndex('assignment_question_answers', ['submission_id', 'question_id', 'attempt_number'], 'unique')) {
            Schema::table('assignment_question_answers', function (Blueprint $table) {
                $table->unique(['submission_id', 'question_id', 'attempt_number'], 'aqa_sub_question_attempt_unique');
            });
        }

        // Setelah indeks baru terbentuk, barulah hapus indeks unik lama
        if (Schema::hasIndex('assignment_question_answers', 'assignment_question_answers_submission_id_question_id_unique')) {
            Schema::table('assignment_question_answers', function (Blueprint $table) {
                $table->dropUnique('assignment_question_answers_submission_id_question_id_unique');
            });
        }

        if (! Schema::hasTable('writing_submission_attempts')) {
            Schema::create('writing_submission_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('writing_submissions')->cascadeOnDelete();
                $table->unsignedSmallInteger('attempt_number');
                $table->string('status', 24)->default('draft');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->decimal('mcq_score', 5, 2)->nullable();
                $table->decimal('essay_score', 5, 2)->nullable();
                $table->decimal('score', 5, 2)->nullable();
                $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('graded_at')->nullable();
                $table->json('answers_snapshot')->nullable();
                $table->timestamps();
                $table->unique(['submission_id', 'attempt_number']);
            });
        }

        DB::table('writing_submissions')->orderBy('id')->chunkById(200, function ($submissions): void {
            foreach ($submissions as $submission) {
                $answers = DB::table('assignment_question_answers')
                    ->join('assignment_questions', 'assignment_questions.id', '=', 'assignment_question_answers.question_id')
                    ->where('assignment_question_answers.submission_id', $submission->id)
                    ->select([
                        'assignment_question_answers.question_id',
                        'assignment_questions.prompt as question',
                        'assignment_question_answers.student_answer as answer',
                        'assignment_question_answers.is_correct',
                        'assignment_question_answers.score_awarded as score',
                    ])
                    ->get()
                    ->map(fn ($answer) => (array) $answer)
                    ->all();

                DB::table('writing_submission_attempts')->insertOrIgnore([
                    'submission_id' => $submission->id,
                    'attempt_number' => 1,
                    'status' => $submission->status === 'graded' || $submission->graded_at
                        ? 'graded'
                        : ($submission->submitted_at ? 'submitted' : 'draft'),
                    'started_at' => $submission->started_at,
                    'submitted_at' => $submission->submitted_at,
                    'mcq_score' => $submission->mcq_score,
                    'essay_score' => $submission->essay_score,
                    'score' => $submission->score,
                    'graded_by' => $submission->graded_by,
                    'graded_at' => $submission->graded_at,
                    'answers_snapshot' => json_encode($answers),
                    'created_at' => $submission->created_at,
                    'updated_at' => $submission->updated_at,
                ]);
            }
        });

        DB::table('writing_submissions')
            ->whereNotNull('graded_at')
            ->update(['status' => 'graded']);

        DB::table('writing_submissions')
            ->whereNull('submitted_at')
            ->whereNull('graded_at')
            ->whereNotIn('status', ['graded'])
            ->update(['status' => 'draft']);

        DB::table('writing_submissions')
            ->whereNotNull('submitted_at')
            ->where('status', '!=', 'graded')
            ->update(['status' => 'submitted']);
    }

    public function down(): void
    {
        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->dropColumn(['draft_body_text', 'draft_artifact_url', 'draft_milestone_id', 'draft_notes', 'draft_saved_at', 'current_attempt']);
            $table->string('status', 24)->default('submitted')->change();
        });

        Schema::dropIfExists('writing_submission_attempts');

        Schema::table('assignment_question_answers', function (Blueprint $table) {
            $table->unique(['submission_id', 'question_id']);
        });

        Schema::table('assignment_question_answers', function (Blueprint $table) {
            if (Schema::hasIndex('assignment_question_answers', 'aqa_sub_question_attempt_unique')) {
                $table->dropUnique('aqa_sub_question_attempt_unique');
            } elseif (Schema::hasIndex('assignment_question_answers', ['submission_id', 'question_id', 'attempt_number'], 'unique')) {
                $table->dropUnique(['submission_id', 'question_id', 'attempt_number']);
            }
            if (Schema::hasColumn('assignment_question_answers', 'attempt_number')) {
                $table->dropColumn('attempt_number');
            }
        });

        Schema::table('writing_assignments', function (Blueprint $table) {
            $table->dropColumn('allow_revisions');
        });
    }
};
