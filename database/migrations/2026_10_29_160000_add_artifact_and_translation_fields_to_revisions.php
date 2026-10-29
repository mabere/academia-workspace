<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->string('artifact_url', 500)->nullable()->after('essay_score');
            $table->unsignedSmallInteger('current_milestone_id')->nullable()->after('artifact_url');
            $table->text('notes')->nullable()->after('current_milestone_id');
        });

        Schema::table('writing_revisions', function (Blueprint $table) {
            $table->string('artifact_url', 500)->nullable()->after('body_text');
            $table->unsignedSmallInteger('milestone_id')->nullable()->after('artifact_url');
            $table->text('notes')->nullable()->after('milestone_id');
        });
    }

    public function down(): void
    {
        Schema::table('writing_revisions', function (Blueprint $table) {
            $table->dropColumn(['artifact_url', 'milestone_id', 'notes']);
        });

        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->dropColumn(['artifact_url', 'current_milestone_id', 'notes']);
        });
    }
};
