<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bkd_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('draft'); // draft, submitted, evaluated
            $table->string('verdict', 8)->nullable(); // M (Memenuhi), TM (Tidak Memenuhi)
            $table->decimal('teaching_sks', 5, 2)->default(0.00);
            $table->decimal('research_sks', 5, 2)->default(0.00);
            $table->decimal('community_sks', 5, 2)->default(0.00);
            $table->decimal('total_sks', 5, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
            $table->unique(['academic_period_id', 'lecturer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bkd_assessments');
    }
};
