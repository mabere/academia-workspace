<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_sessions', function (Blueprint $table) {
            $table->string('passcode', 8)->nullable()->after('notes');
            $table->timestamp('passcode_expires_at')->nullable()->after('passcode');
            $table->boolean('is_attendance_open')->default(false)->after('passcode_expires_at');
        });

        Schema::create('course_session_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_session_id')->constrained('course_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 24)->default('present'); // present, late, excused, absent
            $table->timestamp('checked_in_at')->nullable();
            $table->string('device_ip', 45)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['course_session_id', 'user_id']);
            $table->index(['course_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_session_attendances');

        Schema::table('course_sessions', function (Blueprint $table) {
            $table->dropColumn(['passcode', 'passcode_expires_at', 'is_attendance_open']);
        });
    }
};
