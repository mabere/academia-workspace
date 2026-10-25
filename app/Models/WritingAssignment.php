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
    protected $fillable = ['project_id', 'created_by', 'title', 'instructions', 'feedback_language', 'feedback_level', 'status', 'due_at'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime'];
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
