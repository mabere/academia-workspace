<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WritingSubmissionAttempt extends Model
{
    protected $fillable = [
        'submission_id',
        'attempt_number',
        'status',
        'started_at',
        'submitted_at',
        'mcq_score',
        'essay_score',
        'score',
        'graded_by',
        'graded_at',
        'answers_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'mcq_score' => 'float',
            'essay_score' => 'float',
            'score' => 'float',
            'answers_snapshot' => 'array',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(WritingSubmission::class, 'submission_id');
    }
}
