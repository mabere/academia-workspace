<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('expert_validation_responses', 'consented_at')) {
            Schema::table('expert_validation_responses', function (Blueprint $table) {
                $table->timestamp('consented_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('expert_validation_responses', 'consented_at')) {
            Schema::table('expert_validation_responses', function (Blueprint $table) {
                $table->dropColumn('consented_at');
            });
        }
    }
};
