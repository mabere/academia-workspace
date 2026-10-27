<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained('study_programs')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name', 160);
            $table->unsignedTinyInteger('credit_hours')->default(2);
            $table->string('class_group', 32)->default('A');
            $table->string('schedule_day', 20)->nullable();
            $table->string('schedule_time', 32)->nullable();
            $table->string('room', 80)->nullable();
            $table->unsignedTinyInteger('semester_level')->default(1);
            $table->text('description')->nullable();
            $table->text('syllabus')->nullable();
            $table->string('status', 24)->default('active'); // active, completed, archived
            $table->timestamps();

            $table->index(['lecturer_id', 'academic_period_id', 'status']);
            $table->index(['code', 'class_group']);
        });

        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('session_number')->nullable(); // 1-16, null for general/RPS
            $table->string('title', 180);
            $table->string('material_type', 24)->default('document'); // document, audio, video, link, text, archive
            $table->string('file_path', 255)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->text('external_url')->nullable();
            $table->longText('content_text')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'session_number']);
            $table->index(['course_id', 'material_type']);
        });

        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedSmallInteger('session_number'); // 1-16
            $table->date('date')->nullable();
            $table->string('topic', 255);
            $table->string('teaching_method', 80)->nullable();
            $table->unsignedSmallInteger('attendance_count')->default(0);
            $table->unsignedSmallInteger('total_students')->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('completed'); // scheduled, completed, cancelled
            $table->timestamps();

            $table->unique(['course_id', 'session_number']);
        });

        Schema::create('course_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('nim', 32)->nullable();
            $table->decimal('attendance_rate', 5, 2)->default(100.00);
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->string('letter_grade', 4)->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
        });

        Schema::table('writing_assignments', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('project_id')->constrained('courses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('writing_assignments', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });

        Schema::dropIfExists('course_students');
        Schema::dropIfExists('course_sessions');
        Schema::dropIfExists('course_materials');
        Schema::dropIfExists('courses');
    }
};
