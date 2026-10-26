<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'study_program_id',
        'nidn',
        'nip',
        'functional_rank',
        'highest_education',
        'is_certified',
        'target_sks',
    ];

    protected function casts(): array
    {
        return [
            'is_certified' => 'boolean',
            'target_sks' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function functionalRankLabel(): string
    {
        return match ($this->functional_rank) {
            'asisten_ahli' => 'Asisten Ahli',
            'lektor' => 'Lektor',
            'lektor_kepala' => 'Lektor Kepala',
            'guru_besar' => 'Guru Besar',
            default => 'Tenaga Pengajar',
        };
    }
}
