<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LiteratureSource extends Model
{
    protected $fillable = [
        'project_id', 'uploaded_by', 'original_name', 'storage_path', 'sha256', 'file_size', 'status',
        'title', 'authors', 'publication_year', 'abstract', 'notes', 'extraction_message', 'verified_by', 'verified_at',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(LiteratureSourcePage::class);
    }

    public function matrix(): HasOne
    {
        return $this->hasOne(LiteratureMatrixEntry::class);
    }
}
