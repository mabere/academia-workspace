<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('writing_submissions', 'draft_section_answers')) {
            Schema::table('writing_submissions', function (Blueprint $table) {
                $table->json('draft_section_answers')->nullable();
            });
        }

        if (! Schema::hasColumn('writing_revisions', 'section_answers')) {
            Schema::table('writing_revisions', function (Blueprint $table) {
                $table->json('section_answers')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('writing_revisions', function (Blueprint $table) {
            $table->dropColumn('section_answers');
        });

        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->dropColumn('draft_section_answers');
        });

    }
};
