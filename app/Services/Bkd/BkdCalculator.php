<?php

namespace App\Services\Bkd;

use App\Models\LecturerProfile;
use Illuminate\Support\Collection;

class BkdCalculator
{
    /**
     * Menghitung estimasi bobot SKS berdasarkan jenis artefak dan statusnya.
     */
    public function calculateItemSks(string $type, string $status): float
    {
        return match ($type) {
            'writewise_assignment' => $status === 'published' ? 2.0 : 0.5,
            'writewise_feedback' => $status === 'published' ? 1.0 : 0.25,
            'literature_source' => $status === 'verified' ? 1.0 : 0.5,
            'instrument_blueprint' => $status === 'reviewed' ? 1.5 : 0.5,
            'expert_validation' => 2.0,
            'corpuslab_protocol' => $status === 'approved' ? 2.0 : 0.5,
            'gurubahasa_material' => in_array($status, ['curated', 'published'], true) ? 1.5 : 0.5,
            default => 0.5,
        };
    }

    /**
     * Menentukan pilar Tri Dharma dari jenis artefak.
     */
    public function determinePillar(string $type): string
    {
        return match ($type) {
            'writewise_assignment', 'writewise_feedback' => 'teaching',
            'literature_source', 'instrument_blueprint', 'expert_validation', 'corpuslab_protocol' => 'research',
            'gurubahasa_material' => 'community',
            default => 'other',
        };
    }

    public function pillarLabel(string $pillar): string
    {
        return match ($pillar) {
            'teaching' => 'Pendidikan',
            'research' => 'Penelitian',
            'community' => 'Pengabdian',
            default => 'Penunjang',
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function calculateSummary(Collection $items, ?LecturerProfile $profile = null): array
    {
        $teachingSks = 0.0;
        $researchSks = 0.0;
        $communitySks = 0.0;

        foreach ($items as $item) {
            $sks = (float) ($item['sks'] ?? 0.0);
            $pillar = (string) ($item['pillar'] ?? '');

            match ($pillar) {
                'teaching' => $teachingSks += $sks,
                'research' => $researchSks += $sks,
                'community' => $communitySks += $sks,
                default => null,
            };
        }

        $totalSks = $teachingSks + $researchSks + $communitySks;
        $targetSks = $profile?->target_sks ?? 12;

        // Standar PO BKD Dikti: Minimal 12 SKS total, dengan pemenuhan minimum per dharma
        $teachingMet = $teachingSks >= 3.0;
        $researchMet = $researchSks >= 1.0;
        $communityMet = $communitySks >= 0.5;

        $isEligible = $totalSks >= $targetSks && $teachingMet && $researchMet;

        return [
            'teaching_sks' => $teachingSks,
            'research_sks' => $researchSks,
            'community_sks' => $communitySks,
            'total_sks' => $totalSks,
            'target_sks' => $targetSks,
            'teaching_met' => $teachingMet,
            'research_met' => $researchMet,
            'community_met' => $communityMet,
            'is_eligible' => $isEligible,
            'status_label' => $isEligible ? 'MEMENUHI (M)' : 'BELUM MEMENUHI (TM)',
            'status_tone' => $isEligible ? 'green' : 'amber',
        ];
    }
}
