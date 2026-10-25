<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpertValidationResponse extends Model
{
    protected $fillable = ['overall_comment', 'consented_at', 'submitted_at'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime', 'submitted_at' => 'datetime'];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(ExpertInvitation::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ExpertValidationRating::class);
    }
}
