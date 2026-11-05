<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('writing_assignment_versions', 'assessment_config')) {
            Schema::table('writing_assignment_versions', function (Blueprint $table) {
                $table->json('assessment_config')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('writing_assignment_versions', function (Blueprint $table) {
            $table->dropColumn('assessment_config');
        });
    }
};
