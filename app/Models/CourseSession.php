<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'passcode',
        'passcode_expires_at',
        'is_attendance_open',
    ];

    protected function casts(): array
    {
        return [
            'session_number' => 'integer',
            'date' => 'date',
            'attendance_count' => 'integer',
            'total_students' => 'integer',
            'is_attendance_open' => 'boolean',
            'passcode_expires_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(CourseSessionAttendance::class);
    }

    public function isOpenForAttendance(): bool
    {
        return (bool) $this->is_attendance_open && (! $this->passcode_expires_at || $this->passcode_expires_at->isFuture());
    }

    public function hasStudentAttended(int $userId): bool
    {
        return $this->attendances->contains('user_id', $userId);
    }

    public function studentAttendance(int $userId): ?CourseSessionAttendance
    {
        return $this->attendances->firstWhere('user_id', $userId);
    }

    public function attendancePercentage(): float
    {
        if ($this->total_students <= 0) {
            return 0.0;
        }

        return round(($this->attendance_count / $this->total_students) * 100, 1);
    }
}
