<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 20)->unique();
            $table->string('level', 10)->default('S1'); // D3, D4, S1, S2, S3
            $table->timestamps();
            $table->index(['faculty_id', 'level']);
        });

        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80); // Contoh: 2026/2027 Ganjil
            $table->string('academic_year', 20); // 2026/2027
            $table->string('semester', 16)->default('odd'); // odd, even, short
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index(['is_active', 'academic_year']);
        });

        Schema::create('lecturer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('study_program_id')->nullable()->constrained('study_programs')->nullOnDelete();
            $table->string('nidn', 30)->nullable()->unique();
            $table->string('nip', 30)->nullable()->unique();
            $table->string('functional_rank', 40)->default('tenaga_pengajar'); // tenaga_pengajar, asisten_ahli, lektor, lektor_kepala, guru_besar
            $table->string('highest_education', 10)->default('S2'); // S2, S3
            $table->boolean('is_certified')->default(false); // sertifikasi dosen
            $table->unsignedSmallInteger('target_sks')->default(12); // target minimum BKD
            $table->timestamps();
            $table->index(['study_program_id', 'functional_rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_profiles');
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('study_programs');
        Schema::dropIfExists('faculties');
    }
};
