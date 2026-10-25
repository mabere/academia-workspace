<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentBlueprintVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'version_number', 'title', 'purpose', 'target_constructs', 'respondent_context',
        'items', 'literature_source_ids', 'created_by', 'created_at',
    ];

    protected function casts(): array
    {
        return ['items' => 'array', 'literature_source_ids' => 'array', 'created_at' => 'datetime'];
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(InstrumentBlueprint::class, 'instrument_blueprint_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ExpertInvitation::class, 'instrument_blueprint_version_id');
    }
}
