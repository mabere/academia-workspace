<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->decimal('score', 5, 2)->nullable()->after('status');
            $table->foreignId('graded_by')->nullable()->after('score')->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable()->after('graded_by');
        });
    }

    public function down(): void
    {
        Schema::table('writing_submissions', function (Blueprint $table) {
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['score', 'graded_by', 'graded_at']);
        });
    }
};
