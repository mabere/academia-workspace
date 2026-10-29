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
    protected $fillable = [
        'assignment_id',
        'student_id',
        'assignment_version_id',
        'rubric_version_id',
        'status',
        'score',
        'mcq_score',
        'essay_score',
        'artifact_url',
        'current_milestone_id',
        'notes',
        'started_at',
        'submitted_at',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'mcq_score' => 'float',
            'essay_score' => 'float',
            'current_milestone_id' => 'integer',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    public function currentMilestoneTitle(): ?string
    {
        if (! $this->current_milestone_id || ! $this->assignment) {
            return null;
        }

        $milestones = $this->assignment->config_json['milestones'] ?? [];
        foreach ($milestones as $m) {
            if (($m['id'] ?? null) == $this->current_milestone_id) {
                return $m['title'] ?? ('Milestone '.$this->current_milestone_id);
            }
        }

        return 'Milestone '.$this->current_milestone_id;
    }

    /** @return HasMany<AssignmentQuestionAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(AssignmentQuestionAnswer::class, 'submission_id');
    }

    /**
     * Hitung dan simpan nilai komposit akhir berdasarkan tipe tugas
     */
    public function calculateCompositeScore(): float
    {
        $assignment = $this->assignment;
        $final = 0.0;

        if ($assignment->isStimulusExam()) {
            $final = (float) ($this->mcq_score ?? 0.0);
        } elseif ($assignment->isHybrid()) {
            $mcqPart = ((float) ($this->mcq_score ?? 0.0)) * ($assignment->mcq_weight_percentage / 100);
            $essayPart = ((float) ($this->essay_score ?? 0.0)) * ($assignment->essay_weight_percentage / 100);
            $final = round($mcqPart + $essayPart, 2);
        } else {
            $final = (float) ($this->essay_score ?? $this->score ?? 0.0);
        }

        $this->update(['score' => $final]);

        return $final;
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
