<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Corpus extends Model
{
    protected $table = 'corpora';

    protected $fillable = [
        'project_id', 'created_by', 'title', 'language', 'research_purpose', 'participant_population',
        'sampling_method', 'consent_process', 'ethics_authority', 'ethics_reference', 'ethics_approved_on',
        'anonymization_plan', 'privacy_plan', 'retention_until', 'status', 'reviewed_by', 'reviewed_at',
        'review_notes', 'purged_at',
    ];

    protected function casts(): array
    {
        return ['ethics_approved_on' => 'date', 'retention_until' => 'date', 'reviewed_at' => 'datetime', 'purged_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CorpusEntry::class);
    }

    public function isCollecting(): bool
    {
        return $this->status === 'approved' && $this->retention_until !== null && $this->retention_until->greaterThanOrEqualTo(today());
    }
}
