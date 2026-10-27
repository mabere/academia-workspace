<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $assignment_id
 * @property int $student_id
 * @property-read WritingAssignment $assignment
 * @property-read User $student
 */
class WritingSubmission extends Model
{
    protected $fillable = ['assignment_id', 'student_id', 'assignment_version_id', 'rubric_version_id', 'status', 'score', 'graded_by', 'graded_at'];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'graded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function letterGrade(): string
    {
        if ($this->score === null) {
            return '-';
        }

        return match (true) {
            $this->score >= 85.0 => 'A',
            $this->score >= 80.0 => 'A-',
            $this->score >= 75.0 => 'B+',
            $this->score >= 70.0 => 'B',
            $this->score >= 65.0 => 'B-',
            $this->score >= 60.0 => 'C+',
            $this->score >= 55.0 => 'C',
            $this->score >= 40.0 => 'D',
            default => 'E',
        };
    }

    /** @return BelongsTo<WritingAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(WritingAssignment::class, 'assignment_id');
    }

    /** @return BelongsTo<User, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** @return HasMany<WritingRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(WritingRevision::class, 'submission_id')->orderBy('revision_number');
    }

    /** @return BelongsTo<WritingAssignmentVersion, $this> */
    public function assignmentVersion(): BelongsTo
    {
        return $this->belongsTo(WritingAssignmentVersion::class, 'assignment_version_id');
    }

    /** @return BelongsTo<WritingRubric, $this> */
    public function rubricVersion(): BelongsTo
    {
        return $this->belongsTo(WritingRubric::class, 'rubric_version_id');
    }
}
