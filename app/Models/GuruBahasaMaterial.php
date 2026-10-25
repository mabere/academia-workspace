<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuruBahasaMaterial extends Model
{
    protected $table = 'gurubahasa_materials';

    protected $fillable = [
        'project_id', 'created_by', 'title', 'topic', 'participant_context', 'cefr_level', 'source_text',
        'learning_objectives', 'draft_text', 'glossary', 'questions', 'worksheet', 'activities', 'rubric',
        'status', 'curated_at', 'curated_by',
    ];

    protected function casts(): array
    {
        return [
            'glossary' => 'array', 'questions' => 'array', 'activities' => 'array', 'rubric' => 'array',
            'curated_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
