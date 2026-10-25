<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $assignment_id
 * @property int $version
 * @property array<int, array<string, mixed>> $criteria
 */
class WritingRubric extends Model
{
    protected $fillable = ['assignment_id', 'version', 'name', 'scale_min', 'scale_max', 'criteria'];

    protected function casts(): array
    {
        return ['criteria' => 'array'];
    }

    /** @return BelongsTo<WritingAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(WritingAssignment::class);
    }
}
