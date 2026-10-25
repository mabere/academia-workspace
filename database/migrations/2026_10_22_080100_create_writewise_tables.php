<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 24)->default('editor');
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('writing_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 180);
            $table->text('instructions');
            $table->string('feedback_language', 16)->default('id');
            $table->string('feedback_level', 24)->default('indirect');
            $table->string('status', 24)->default('draft');
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });

        Schema::create('writing_rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('writing_assignments')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('name', 120)->default('Writing rubric');
            $table->unsignedSmallInteger('scale_min')->default(1);
            $table->unsignedSmallInteger('scale_max')->default(5);
            $table->json('criteria');
            $table->timestamps();
            $table->unique(['assignment_id', 'version']);
        });

        Schema::create('assignment_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('writing_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });

        Schema::create('writing_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('writing_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 24)->default('submitted');
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });

        Schema::create('writing_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('writing_submissions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('revision_number');
            $table->longText('body_text');
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['submission_id', 'revision_number']);
        });

        Schema::create('writing_feedback_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->constrained('writing_revisions')->cascadeOnDelete();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('feedback_json');
            $table->string('provider', 80)->nullable();
            $table->string('model', 120)->nullable();
            $table->string('prompt_version', 40)->default('writewise-v1');
            $table->string('status', 24)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['revision_id', 'status']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('target_type', 80)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['project_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('writing_feedback_reports');
        Schema::dropIfExists('writing_revisions');
        Schema::dropIfExists('writing_submissions');
        Schema::dropIfExists('assignment_students');
        Schema::dropIfExists('writing_rubrics');
        Schema::dropIfExists('writing_assignments');
        Schema::dropIfExists('project_members');
    }
};
