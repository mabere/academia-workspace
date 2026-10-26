<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkdAssessment extends Model
{
    protected $fillable = [
        'academic_period_id',
        'lecturer_id',
        'assessor_id',
        'status',
        'verdict',
        'teaching_sks',
        'research_sks',
        'community_sks',
        'total_sks',
        'notes',
        'submitted_at',
        'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'teaching_sks' => 'float',
            'research_sks' => 'float',
            'community_sks' => 'float',
            'total_sks' => 'float',
            'submitted_at' => 'datetime',
            'evaluated_at' => 'datetime',
        ];
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function verdictLabel(): string
    {
        return match ($this->verdict) {
            'M' => 'Memenuhi (M)',
            'TM' => 'Tidak Memenuhi (TM)',
            default => 'Belum Ditentukan',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'submitted' => 'Menunggu Penilaian Asesor',
            'evaluated' => 'Telah Dinilai Asesor',
            default => 'Draf Kinerja',
        };
    }
}
