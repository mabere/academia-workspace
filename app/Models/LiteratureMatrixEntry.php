<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiteratureMatrixEntry extends Model
{
    protected $fillable = [
        'research_question', 'methodology', 'participants_context', 'key_findings', 'limitations',
        'relevance_notes', 'evidence_pages', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['evidence_pages' => 'array'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LiteratureSource::class, 'literature_source_id');
    }
}
