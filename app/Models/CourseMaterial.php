<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseMaterial extends Model
{
    protected $fillable = [
        'course_id',
        'uploaded_by',
        'session_number',
        'title',
        'material_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'external_url',
        'content_text',
        'description',
        'is_published',
        'download_count',
    ];

    protected function casts(): array
    {
        return [
            'session_number' => 'integer',
            'file_size' => 'integer',
            'is_published' => 'boolean',
            'download_count' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isAudio(): bool
    {
        return $this->material_type === 'audio';
    }

    public function isVideo(): bool
    {
        return $this->material_type === 'video';
    }

    public function isDocument(): bool
    {
        return $this->material_type === 'document';
    }

    public function isLink(): bool
    {
        return $this->material_type === 'link';
    }

    public function isText(): bool
    {
        return $this->material_type === 'text';
    }

    public function isArchive(): bool
    {
        return $this->material_type === 'archive';
    }

    public function formattedFileSize(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($this->file_size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    public function getYouTubeEmbedUrl(): ?string
    {
        if (! $this->external_url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $this->external_url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        return null;
    }

    public function typeLabel(): string
    {
        return match ($this->material_type) {
            'audio' => 'Audio / Listening',
            'video' => 'Video Perkuliahan',
            'document' => 'Dokumen / Slide',
            'link' => 'Tautan Eksternal',
            'text' => 'Catatan / Artikel',
            'archive' => 'Paket Berkas (ZIP)',
            default => 'Materi',
        };
    }
}
