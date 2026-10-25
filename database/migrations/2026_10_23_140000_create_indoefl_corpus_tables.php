<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corpora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 180);
            $table->string('language', 80);
            $table->text('research_purpose');
            $table->text('participant_population');
            $table->text('sampling_method');
            $table->text('consent_process');
            $table->string('ethics_authority', 180);
            $table->string('ethics_reference', 120);
            $table->date('ethics_approved_on');
            $table->text('anonymization_plan');
            $table->text('privacy_plan');
            $table->date('retention_until');
            $table->string('status', 24)->default('draft');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('purged_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'retention_until']);
        });

        Schema::create('corpus_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corpus_id')->constrained('corpora')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('participant_code', 64);
            $table->string('consent_reference', 120)->nullable();
            $table->boolean('consent_confirmed');
            $table->date('collected_on');
            $table->longText('anonymized_text');
            $table->unsignedInteger('word_count')->default(0);
            $table->string('status', 24)->default('pending');
            $table->timestamps();
            $table->index(['corpus_id', 'status', 'created_at']);
            $table->index(['corpus_id', 'participant_code']);
        });

        Schema::create('corpus_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corpus_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('label', 100);
            $table->text('span_text')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['corpus_entry_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corpus_annotations');
        Schema::dropIfExists('corpus_entries');
        Schema::dropIfExists('corpora');
    }
};
