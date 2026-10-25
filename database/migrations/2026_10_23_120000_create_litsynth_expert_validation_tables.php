<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_blueprints', function (Blueprint $table) {
            $table->unsignedInteger('current_version')->default(1);
        });

        Schema::create('instrument_blueprint_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_blueprint_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title', 180);
            $table->text('purpose');
            $table->text('target_constructs')->nullable();
            $table->text('respondent_context')->nullable();
            $table->json('items');
            $table->json('literature_source_ids')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['instrument_blueprint_id', 'version_number'], 'instrument_blueprint_version_unique');
        });

        Schema::create('expert_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_blueprint_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->restrictOnDelete();
            $table->string('expert_email', 255);
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['instrument_blueprint_version_id', 'revoked_at']);
        });

        Schema::create('expert_validation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_invitation_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('overall_comment')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
        });

        Schema::create('expert_validation_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_validation_response_id')->constrained()->cascadeOnDelete();
            $table->string('item_key', 80);
            $table->unsignedTinyInteger('relevance_rating');
            $table->unsignedTinyInteger('clarity_rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['expert_validation_response_id', 'item_key'], 'expert_response_item_unique');
        });

        foreach (DB::table('instrument_blueprints')->orderBy('id')->get() as $blueprint) {
            $items = json_decode($blueprint->items ?: '[]', true) ?: [];
            $snapshotItems = [];
            foreach ($items as $item) {
                $text = is_array($item) ? (string) ($item['text'] ?? '') : (string) $item;
                $snapshotItems[] = ['key' => (string) Str::uuid(), 'text' => $text];
            }
            DB::table('instrument_blueprint_versions')->insert([
                'instrument_blueprint_id' => $blueprint->id,
                'version_number' => 1,
                'title' => $blueprint->title,
                'purpose' => $blueprint->purpose,
                'target_constructs' => $blueprint->target_constructs,
                'respondent_context' => $blueprint->respondent_context,
                'items' => json_encode($snapshotItems),
                'literature_source_ids' => $blueprint->literature_source_ids,
                'created_by' => $blueprint->created_by,
                'created_at' => $blueprint->created_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_validation_ratings');
        Schema::dropIfExists('expert_validation_responses');
        Schema::dropIfExists('expert_invitations');
        Schema::dropIfExists('instrument_blueprint_versions');
        Schema::table('instrument_blueprints', function (Blueprint $table) {
            $table->dropColumn('current_version');
        });
    }
};
