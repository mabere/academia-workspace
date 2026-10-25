<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gurubahasa_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 180);
            $table->string('topic', 180);
            $table->text('participant_context');
            $table->string('cefr_level', 2);
            $table->longText('source_text')->nullable();
            $table->longText('learning_objectives')->nullable();
            $table->longText('draft_text')->nullable();
            $table->json('glossary')->nullable();
            $table->json('questions')->nullable();
            $table->longText('worksheet')->nullable();
            $table->json('activities')->nullable();
            $table->json('rubric')->nullable();
            $table->string('status', 24)->default('draft');
            $table->timestamp('curated_at')->nullable();
            $table->foreignId('curated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gurubahasa_materials');
    }
};
