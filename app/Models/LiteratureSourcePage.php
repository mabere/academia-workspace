<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiteratureSourcePage extends Model
{
    protected $fillable = ['page_number', 'extracted_text'];

    public function source(): BelongsTo
    {
        return $this->belongsTo(LiteratureSource::class, 'literature_source_id');
    }
}
