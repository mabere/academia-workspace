<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $nim
 * @property int|null $study_program_id
 * @property int|null $cohort_year
 * @property bool $is_active
 * @property string|null $profile_photo_path
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'role', 'nim', 'study_program_id', 'cohort_year', 'is_active', 'profile_photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isStaff(): bool
    {
        return in_array($this->role, ['lecturer', 'admin', 'superadmin'], true);
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'superadmin' => 'Superadmin',
            'admin' => 'Administrator',
            'lecturer' => 'Dosen Pengampu',
            'student' => 'Mahasiswa',
            default => 'Pengguna Umum',
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'cohort_year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<StudyProgram, $this> */
    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    /** @return HasOne<LecturerProfile, $this> */
    public function lecturerProfile(): HasOne
    {
        return $this->hasOne(LecturerProfile::class);
    }

    /** @return HasMany<Course, $this> */
    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'lecturer_id');
    }

    /** @return BelongsToMany<Course, $this> */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_students', 'student_id', 'course_id')
            ->withPivot(['nim', 'attendance_rate', 'final_grade', 'letter_grade'])
            ->withTimestamps();
    }

    /** @return HasMany<CourseSessionAttendance, $this> */
    public function sessionAttendances(): HasMany
    {
        return $this->hasMany(CourseSessionAttendance::class);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
