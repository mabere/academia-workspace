<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'academic_period_id',
        'study_program_id',
        'lecturer_id',
        'code',
        'name',
        'credit_hours',
        'class_group',
        'schedule_day',
        'schedule_time',
        'room',
        'semester_level',
        'description',
        'syllabus',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'credit_hours' => 'integer',
            'semester_level' => 'integer',
        ];
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class)->orderByRaw('session_number IS NULL DESC, session_number ASC');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CourseSession::class)->orderBy('session_number');
    }

    public function students(): HasMany
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_students', 'course_id', 'student_id')
            ->withPivot(['nim', 'attendance_rate', 'final_grade', 'letter_grade'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WritingAssignment::class);
    }

    public function scheduleLabel(): string
    {
        $parts = array_filter([$this->schedule_day, $this->schedule_time, $this->room]);

        return ! empty($parts) ? implode(' · ', $parts) : 'Jadwal belum ditentukan';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function activeAttendanceSession(): ?CourseSession
    {
        return $this->sessions->first(fn ($s) => $s->isOpenForAttendance());
    }

    public function syncStudentAttendanceRates(): void
    {
        $totalSessions = $this->sessions()
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhere('is_attendance_open', true)
                    ->orWhere('attendance_count', '>', 0);
            })
            ->count();

        if ($totalSessions === 0) {
            return;
        }

        $sessionIds = $this->sessions()->pluck('id');

        $attendancesGrouped = CourseSessionAttendance::query()
            ->whereIn('course_session_id', $sessionIds)
            ->whereIn('status', ['present', 'late'])
            ->get()
            ->groupBy('user_id');

        $this->loadMissing('students');

        foreach ($this->students as $courseStudent) {
            $attendedCount = isset($attendancesGrouped[$courseStudent->student_id])
                ? $attendancesGrouped[$courseStudent->student_id]->count()
                : 0;

            $rate = round(min(100, ($attendedCount / $totalSessions) * 100), 1);
            $courseStudent->update(['attendance_rate' => $rate]);
        }
    }
}
