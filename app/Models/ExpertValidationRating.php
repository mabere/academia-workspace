<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertValidationRating extends Model
{
    protected $fillable = ['item_key', 'relevance_rating', 'clarity_rating', 'comment'];

    public function response(): BelongsTo
    {
        return $this->belongsTo(ExpertValidationResponse::class, 'expert_validation_response_id');
    }
}
