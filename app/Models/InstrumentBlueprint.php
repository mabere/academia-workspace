<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentBlueprint extends Model
{
    protected $fillable = [
        'project_id', 'created_by', 'title', 'purpose', 'target_constructs', 'respondent_context',
        'items', 'literature_source_ids', 'status', 'current_version',
    ];

    protected function casts(): array
    {
        return ['items' => 'array', 'literature_source_ids' => 'array', 'current_version' => 'integer'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(InstrumentBlueprintVersion::class);
    }
}
