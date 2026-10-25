<?php

namespace App\Services\LitSynth;

use App\Models\InstrumentBlueprintVersion;

class ContentValidityCalculator
{
    /**
     * Calculate descriptive item and scale indices from completed expert ratings.
     * Aiken's V uses the 1–4 category scale; CVI counts ratings of 3 or 4 as relevant/clear.
     *
     * @return array{respondents:int, items:array<int, array<string, mixed>>, scales:array<string, array<string, float|int|null>>}
     */
    public function calculate(InstrumentBlueprintVersion $version): array
    {
        $version->loadMissing(['invitations.response.ratings']);
        $responses = $version->invitations->filter(fn ($invitation) => $invitation->response !== null)->pluck('response');
        $dimensions = ['relevance_rating' => 'relevance', 'clarity_rating' => 'clarity'];
        $items = [];
        $scaleItemIndices = ['relevance' => [], 'clarity' => []];
        $scaleUniversal = ['relevance' => [], 'clarity' => []];

        foreach ($version->items ?? [] as $item) {
            $itemResult = ['key' => $item['key'], 'text' => $item['text']];
            foreach ($dimensions as $ratingField => $name) {
                $ratings = $responses->flatMap(fn ($response) => $response->ratings->where('item_key', $item['key']))
                    ->pluck($ratingField)->map(fn ($rating) => (int) $rating)->values();
                $count = $ratings->count();
                $sum = $ratings->sum(fn (int $rating) => $rating - 1);
                $itemCvi = $count > 0 ? $ratings->filter(fn (int $rating) => $rating >= 3)->count() / $count : null;
                $itemResult[$name] = [
                    'n' => $count,
                    'aiken_v' => $count > 0 ? $sum / ($count * 3) : null,
                    'i_cvi' => $itemCvi,
                ];
                if ($itemCvi !== null) {
                    $scaleItemIndices[$name][] = $itemCvi;
                    $scaleUniversal[$name][] = $itemCvi === 1.0;
                }
            }
            $items[] = $itemResult;
        }

        $scales = [];
        foreach ($dimensions as $name) {
            $indices = $scaleItemIndices[$name];
            $scales[$name] = [
                's_cvi_ave' => $indices !== [] ? array_sum($indices) / count($indices) : null,
                's_cvi_ua' => $indices !== [] ? count(array_filter($scaleUniversal[$name])) / count($indices) : null,
                'items_with_ratings' => count($indices),
            ];
        }

        return ['respondents' => $responses->count(), 'items' => $items, 'scales' => $scales];
    }
}
