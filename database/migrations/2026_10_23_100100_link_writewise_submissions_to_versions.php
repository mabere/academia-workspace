<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->foreignId('rubric_version_id')->nullable()->constrained('writing_rubrics')->nullOnDelete();
        });

        DB::table('writing_submissions')->orderBy('id')->chunkById(100, function ($submissions): void {
            foreach ($submissions as $submission) {
                $assignmentVersionId = $submission->assignment_version_id ?? DB::table('writing_assignment_versions')
                    ->where('assignment_id', $submission->assignment_id)->orderBy('version')->value('id');
                $rubricVersionId = DB::table('writing_rubrics')->where('assignment_id', $submission->assignment_id)->orderByDesc('version')->value('id');

                DB::table('writing_submissions')->where('id', $submission->id)->update([
                    'assignment_version_id' => $assignmentVersionId,
                    'rubric_version_id' => $rubricVersionId,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rubric_version_id');
        });
    }
};
