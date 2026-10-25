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
    protected $fillable = ['assignment_id', 'student_id', 'assignment_version_id', 'status'];

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
