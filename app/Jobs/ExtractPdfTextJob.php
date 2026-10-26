<?php

namespace App\Jobs;

use App\Models\LiteratureSource;
use App\Services\LitSynth\PdfTextExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExtractPdfTextJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(public int $literatureSourceId) {}

    public function handle(PdfTextExtractor $extractor): void
    {
        $source = LiteratureSource::query()->find($this->literatureSourceId);
        if (! $source) {
            return;
        }

        $absolutePath = Storage::disk('local')->path($source->storage_path);
        if (! file_exists($absolutePath)) {
            $source->update([
                'status' => 'extraction_unavailable',
                'extraction_message' => 'Berkas PDF fisik tidak ditemukan pada penyimpanan privat.',
            ]);

            return;
        }

        try {
            $pages = $extractor->extractPages($absolutePath);

            DB::transaction(function () use ($source, $pages): void {
                $source->pages()->delete();

                $now = now();
                $rows = [];
                foreach ($pages as $index => $text) {
                    $rows[] = [
                        'literature_source_id' => $source->id,
                        'page_number' => $index + 1,
                        'extracted_text' => $text,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! empty($rows)) {
                    foreach (array_chunk($rows, 50) as $chunk) {
                        DB::table('literature_source_pages')->insert($chunk);
                    }
                }

                $source->update([
                    'status' => 'needs_review',
                    'extraction_message' => null,
                ]);

                DB::table('audit_logs')->insert([
                    'actor_id' => $source->uploaded_by,
                    'project_id' => $source->project_id,
                    'action' => 'litsynth.source_extracted',
                    'target_type' => 'literature_source',
                    'target_id' => $source->id,
                    'metadata' => json_encode(['pages_count' => count($pages)]),
                    'created_at' => $now,
                ]);
            });
        } catch (Throwable $exception) {
            Log::warning('Ekstraksi PDF gagal untuk sumber ID '.$source->id.': '.$exception->getMessage());

            $source->update([
                'status' => 'extraction_unavailable',
                'extraction_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);

            DB::table('audit_logs')->insert([
                'actor_id' => $source->uploaded_by,
                'project_id' => $source->project_id,
                'action' => 'litsynth.source_extraction_failed',
                'target_type' => 'literature_source',
                'target_id' => $source->id,
                'metadata' => json_encode(['error' => mb_substr($exception->getMessage(), 0, 500)]),
                'created_at' => now(),
            ]);
        }
    }
}
