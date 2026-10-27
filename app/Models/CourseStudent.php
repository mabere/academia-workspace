<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseStudent extends Model
{
    protected $fillable = [
        'course_id',
        'student_id',
        'nim',
        'attendance_rate',
        'final_grade',
        'letter_grade',
    ];

    protected function casts(): array
    {
        return [
            'attendance_rate' => 'float',
            'final_grade' => 'float',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
