<?php

namespace App\Services\LitSynth;

use App\Models\InstrumentBlueprint;
use Illuminate\Support\Str;

class InstrumentVersionManager
{
    public function snapshot(InstrumentBlueprint $blueprint, int $version, int $actorId): void
    {
        $items = collect($blueprint->items ?? [])->map(function ($item) {
            return [
                'key' => (string) Str::uuid(),
                'text' => is_array($item) ? (string) ($item['text'] ?? '') : (string) $item,
            ];
        })->filter(fn (array $item) => trim($item['text']) !== '')->values()->all();

        $blueprint->versions()->create([
            'version_number' => $version,
            'title' => $blueprint->title,
            'purpose' => $blueprint->purpose,
            'target_constructs' => $blueprint->target_constructs,
            'respondent_context' => $blueprint->respondent_context,
            'items' => $items,
            'literature_source_ids' => $blueprint->literature_source_ids,
            'created_by' => $actorId,
            'created_at' => now(),
        ]);
    }
}
