<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom konfigurasi format tugas ke writing_assignments
        Schema::table('writing_assignments', function (Blueprint $table) {
            $table->string('assignment_type', 32)->default('writing')->after('instructions');
            $table->unsignedSmallInteger('time_limit_minutes')->nullable()->after('assignment_type');
            $table->unsignedSmallInteger('mcq_weight_percentage')->default(30)->after('time_limit_minutes');
            $table->unsignedSmallInteger('essay_weight_percentage')->default(70)->after('mcq_weight_percentage');
            $table->json('config_json')->nullable()->after('essay_weight_percentage');

            $table->index(['course_id', 'assignment_type']);
        });

        // 2. Buat tabel butir pertanyaan (Soal Pilihan Ganda, Isian Singkat, & Esai Analitis)
        Schema::create('assignment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('writing_assignments')->cascadeOnDelete();
            $table->string('question_type', 32)->default('multiple_choice'); // multiple_choice, short_answer, essay
            $table->string('stimulus_type', 32)->nullable(); // audio, reading_text, none
            $table->text('prompt');
            $table->json('options_json')->nullable(); // [{"key": "A", "text": "..."}, ...]
            $table->string('answer_key', 64)->nullable(); // Misal "A", atau teks kunci
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedSmallInteger('order')->default(1);
            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->index(['assignment_id', 'order']);
        });

        // 3. Tambah kolom pencatatan skor komposit pada writing_submissions
        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->decimal('mcq_score', 5, 2)->nullable()->after('score');
            $table->decimal('essay_score', 5, 2)->nullable()->after('mcq_score');
            $table->timestamp('started_at')->nullable()->after('essay_score');
            $table->timestamp('submitted_at')->nullable()->after('started_at');
        });

        // 4. Buat tabel jawaban per butir soal mahasiswa
        Schema::create('assignment_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('writing_submissions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('assignment_questions')->cascadeOnDelete();
            $table->text('student_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score_awarded', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['submission_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_question_answers');
        Schema::dropIfExists('assignment_questions');

        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->dropColumn(['mcq_score', 'essay_score', 'started_at', 'submitted_at']);
        });

        Schema::table('writing_assignments', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'assignment_type']);
            $table->dropColumn(['assignment_type', 'time_limit_minutes', 'mcq_weight_percentage', 'essay_weight_percentage', 'config_json']);
        });
    }
};
