<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $submission_id
 * @property int $created_by
 * @property int $revision_number
 * @property string $body_text
 * @property-read WritingSubmission $submission
 */
class WritingRevision extends Model
{
    protected $fillable = ['submission_id', 'created_by', 'assignment_version_id', 'rubric_version_id', 'revision_number', 'body_text', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    /** @return BelongsTo<WritingSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(WritingSubmission::class, 'submission_id');
    }

    /** @return HasMany<WritingFeedbackReport, $this> */
    public function feedbackReports(): HasMany
    {
        return $this->hasMany(WritingFeedbackReport::class, 'revision_id');
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
