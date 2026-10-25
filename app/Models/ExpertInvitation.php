<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExpertInvitation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'instrument_blueprint_version_id', 'invited_by', 'expert_email', 'token_hash', 'expires_at', 'revoked_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'revoked_at' => 'datetime', 'completed_at' => 'datetime', 'created_at' => 'datetime'];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(InstrumentBlueprintVersion::class, 'instrument_blueprint_version_id');
    }

    public function response(): HasOne
    {
        return $this->hasOne(ExpertValidationResponse::class);
    }
}
