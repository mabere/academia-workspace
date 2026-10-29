<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $created_by
 * @property string $title
 * @property string $instructions
 * @property string $feedback_language
 * @property string $feedback_level
 * @property string $status
 * @property Carbon|null $due_at
 * @property-read Project $project
 */
class WritingAssignment extends Model
{
    protected $fillable = [
        'project_id',
        'course_id',
        'created_by',
        'title',
        'instructions',
        'assignment_type',
        'time_limit_minutes',
        'mcq_weight_percentage',
        'essay_weight_percentage',
        'config_json',
        'feedback_language',
        'feedback_level',
        'status',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'time_limit_minutes' => 'integer',
            'mcq_weight_percentage' => 'integer',
            'essay_weight_percentage' => 'integer',
            'config_json' => 'array',
        ];
    }

    public function isWriting(): bool
    {
        return ($this->assignment_type ?? 'writing') === 'writing';
    }

    public function isStimulusExam(): bool
    {
        return $this->assignment_type === 'stimulus_exam';
    }

    public function isHybrid(): bool
    {
        return $this->assignment_type === 'hybrid';
    }

    public function isTranslation(): bool
    {
        return $this->assignment_type === 'translation';
    }

    public function isMilestone(): bool
    {
        return $this->assignment_type === 'milestone_artifact';
    }

    public function typeLabel(): string
    {
        return match ($this->assignment_type) {
            'stimulus_exam' => 'Ujian Stimulus / Listening',
            'hybrid' => 'Penilaian Campuran (PG + Esai)',
            'translation' => 'Penerjemahan Teks',
            'milestone_artifact' => 'Proposal & Proyek Bertahap',
            default => 'Esai Akademik (WriteWise)',
        };
    }

    public function typeBadgeColor(): string
    {
        return match ($this->assignment_type) {
            'stimulus_exam' => 'bg-amber-100 text-amber-800 border-amber-300',
            'hybrid' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'translation' => 'bg-purple-100 text-purple-800 border-purple-300',
            'milestone_artifact' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-teal-100 text-teal-800 border-teal-300',
        };
    }

    /** @return HasMany<AssignmentQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(AssignmentQuestion::class, 'assignment_id')->orderBy('order');
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsToMany<User, $this, Pivot, 'pivot'> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignment_students', 'assignment_id', 'student_id')->withTimestamps();
    }

    /** @return HasMany<WritingSubmission, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(WritingSubmission::class, 'assignment_id');
    }

    /** @return HasMany<WritingRubric, $this> */
    public function rubric(): HasMany
    {
        return $this->hasMany(WritingRubric::class, 'assignment_id');
    }

    /** @return HasMany<WritingAssignmentVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(WritingAssignmentVersion::class, 'assignment_id');
    }
}
