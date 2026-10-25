<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_assignment_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('writing_assignments')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('title', 180);
            $table->text('instructions');
            $table->string('feedback_language', 16);
            $table->string('feedback_level', 24);
            $table->string('status', 24);
            $table->timestamp('due_at')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['assignment_id', 'version']);
        });

        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->foreignId('assignment_version_id')->nullable()->constrained('writing_assignment_versions')->nullOnDelete();
        });

        DB::table('writing_assignments')->orderBy('id')->chunkById(100, function ($assignments): void {
            foreach ($assignments as $assignment) {
                DB::table('writing_assignment_versions')->insert([
                    'assignment_id' => $assignment->id,
                    'version' => 1,
                    'title' => $assignment->title,
                    'instructions' => $assignment->instructions,
                    'feedback_language' => $assignment->feedback_language,
                    'feedback_level' => $assignment->feedback_level,
                    'status' => $assignment->status,
                    'due_at' => $assignment->due_at,
                    'edited_by' => $assignment->created_by,
                    'created_at' => $assignment->created_at,
                    'updated_at' => $assignment->updated_at,
                ]);
            }
        });

        DB::table('writing_submissions')->whereNull('assignment_version_id')->orderBy('id')->chunkById(100, function ($submissions): void {
            foreach ($submissions as $submission) {
                $versionId = DB::table('writing_assignment_versions')->where('assignment_id', $submission->assignment_id)->where('version', 1)->value('id');
                DB::table('writing_submissions')->where('id', $submission->id)->update(['assignment_version_id' => $versionId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_version_id');
        });
        Schema::dropIfExists('writing_assignment_versions');
    }
};
