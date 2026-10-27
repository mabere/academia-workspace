<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSession extends Model
{
    protected $fillable = [
        'course_id',
        'session_number',
        'date',
        'topic',
        'teaching_method',
        'attendance_count',
        'total_students',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'session_number' => 'integer',
            'date' => 'date',
            'attendance_count' => 'integer',
            'total_students' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attendancePercentage(): float
    {
        if ($this->total_students <= 0) {
            return 0.0;
        }

        return round(($this->attendance_count / $this->total_students) * 100, 1);
    }
}
