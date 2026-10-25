<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('writing_revisions', function (Blueprint $table) {
            $table->foreignId('assignment_version_id')->nullable()->constrained('writing_assignment_versions')->nullOnDelete();
            $table->foreignId('rubric_version_id')->nullable()->constrained('writing_rubrics')->nullOnDelete();
        });

        DB::table('writing_revisions')->join('writing_submissions', 'writing_submissions.id', '=', 'writing_revisions.submission_id')
            ->select(['writing_revisions.id', 'writing_submissions.assignment_version_id', 'writing_submissions.rubric_version_id'])
            ->orderBy('writing_revisions.id')->chunkById(100, function ($revisions): void {
                foreach ($revisions as $revision) {
                    DB::table('writing_revisions')->where('id', $revision->id)->update([
                        'assignment_version_id' => $revision->assignment_version_id,
                        'rubric_version_id' => $revision->rubric_version_id,
                    ]);
                }
            }, 'writing_revisions.id', 'id');
    }

    public function down(): void
    {
        Schema::table('writing_revisions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_version_id');
            $table->dropConstrainedForeignId('rubric_version_id');
        });
    }
};
