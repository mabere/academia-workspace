<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('literature_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('original_name');
            $table->string('storage_path')->unique();
            $table->string('sha256', 64);
            $table->unsignedBigInteger('file_size');
            $table->string('status', 32)->default('uploaded');
            $table->string('title')->nullable();
            $table->text('authors')->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->text('abstract')->nullable();
            $table->text('notes')->nullable();
            $table->text('extraction_message')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });

        Schema::create('literature_source_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('literature_source_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->longText('extracted_text');
            $table->timestamps();
            $table->unique(['literature_source_id', 'page_number']);
        });

        Schema::create('literature_matrix_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('literature_source_id')->constrained()->cascadeOnDelete();
            $table->text('research_question')->nullable();
            $table->text('methodology')->nullable();
            $table->text('participants_context')->nullable();
            $table->longText('key_findings')->nullable();
            $table->text('limitations')->nullable();
            $table->text('relevance_notes')->nullable();
            $table->json('evidence_pages')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique('literature_source_id');
        });

        Schema::create('instrument_blueprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 180);
            $table->text('purpose');
            $table->text('target_constructs')->nullable();
            $table->text('respondent_context')->nullable();
            $table->json('items')->nullable();
            $table->json('literature_source_ids')->nullable();
            $table->string('status', 24)->default('draft');
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_blueprints');
        Schema::dropIfExists('literature_matrix_entries');
        Schema::dropIfExists('literature_source_pages');
        Schema::dropIfExists('literature_sources');
    }
};
