<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $revision_id
 * @property int|null $edited_by
 * @property array<string, mixed> $feedback_json
 * @property string $status
 * @property-read WritingRevision $revision
 */
class WritingFeedbackReport extends Model
{
    protected $fillable = ['revision_id', 'edited_by', 'feedback_json', 'provider', 'model', 'prompt_version', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['feedback_json' => 'array', 'published_at' => 'datetime'];
    }

    /** @return BelongsTo<WritingRevision, $this> */
    public function revision(): BelongsTo
    {
        return $this->belongsTo(WritingRevision::class, 'revision_id');
    }
}
