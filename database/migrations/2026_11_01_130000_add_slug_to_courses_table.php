<?php

use App\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('slug', 190)->nullable()->unique()->after('code');
        });

        // Generate slug untuk setiap mata kuliah yang sudah ada
        $courses = Course::all();
        $generatedSlugs = [];

        foreach ($courses as $course) {
            $baseSlug = Str::slug($course->code . ' ' . $course->class_group);
            if (empty($baseSlug)) {
                $baseSlug = Str::slug($course->name ?: 'course');
            }

            $slug = $baseSlug;
            $counter = 1;
            while (in_array($slug, $generatedSlugs, true)) {
                $counter++;
                $slug = $baseSlug . '-' . $counter;
            }

            $generatedSlugs[] = $slug;
            $course->updateQuietly(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
