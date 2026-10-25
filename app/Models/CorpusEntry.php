<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorpusEntry extends Model
{
    protected $fillable = [
        'corpus_id', 'created_by', 'participant_code', 'consent_reference', 'consent_confirmed',
        'collected_on', 'anonymized_text', 'word_count', 'status',
    ];

    protected function casts(): array
    {
        return ['consent_confirmed' => 'boolean', 'collected_on' => 'date'];
    }

    public function corpus(): BelongsTo
    {
        return $this->belongsTo(Corpus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(CorpusAnnotation::class);
    }
}
