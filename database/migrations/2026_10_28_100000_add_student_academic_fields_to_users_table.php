<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim', 32)->nullable()->after('role')->index();
            $table->foreignId('study_program_id')->nullable()->after('nim')->constrained('study_programs')->nullOnDelete();
            $table->unsignedSmallInteger('cohort_year')->nullable()->after('study_program_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['study_program_id']);
            $table->dropColumn(['nim', 'study_program_id', 'cohort_year']);
        });
    }
};
