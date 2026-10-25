<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorpusAnnotation extends Model
{
    protected $fillable = ['corpus_entry_id', 'created_by', 'label', 'span_text', 'comment'];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(CorpusEntry::class, 'corpus_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
