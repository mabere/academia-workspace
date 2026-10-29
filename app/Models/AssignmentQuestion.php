<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $assignment_id
 * @property string $question_type
 * @property string|null $stimulus_type
 * @property string $prompt
 * @property array|null $options_json
 * @property string|null $answer_key
 * @property int $points
 * @property int $order
 * @property string|null $explanation
 * @property-read WritingAssignment $assignment
 */
class AssignmentQuestion extends Model
{
    protected $fillable = [
        'assignment_id',
        'question_type',
        'stimulus_type',
        'prompt',
        'options_json',
        'answer_key',
        'points',
        'order',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'options_json' => 'array',
            'points' => 'integer',
            'order' => 'integer',
        ];
    }

    /** @return BelongsTo<WritingAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(WritingAssignment::class, 'assignment_id');
    }

    /** @return HasMany<AssignmentQuestionAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(AssignmentQuestionAnswer::class, 'question_id');
    }

    public function isMultipleChoice(): bool
    {
        return $this->question_type === 'multiple_choice';
    }

    public function isShortAnswer(): bool
    {
        return $this->question_type === 'short_answer';
    }

    public function isEssay(): bool
    {
        return $this->question_type === 'essay';
    }
}
