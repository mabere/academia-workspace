<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id @property int $assignment_id @property int $version @property string $title @property string $instructions */
class WritingAssignmentVersion extends Model
{
    protected $fillable = ['assignment_id', 'version', 'title', 'instructions', 'feedback_language', 'feedback_level', 'status', 'due_at', 'edited_by'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime'];
    }

    /** @return BelongsTo<WritingAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(WritingAssignment::class, 'assignment_id');
    }
}
