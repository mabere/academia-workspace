<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $submission_id
 * @property int $question_id
 * @property string|null $student_answer
 * @property bool|null $is_correct
 * @property float $score_awarded
 * @property-read WritingSubmission $submission
 * @property-read AssignmentQuestion $question
 */
class AssignmentQuestionAnswer extends Model
{
    protected $fillable = [
        'submission_id',
        'question_id',
        'student_answer',
        'is_correct',
        'score_awarded',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'score_awarded' => 'float',
        ];
    }

    /** @return BelongsTo<WritingSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(WritingSubmission::class, 'submission_id');
    }

    /** @return BelongsTo<AssignmentQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(AssignmentQuestion::class, 'question_id');
    }
}
