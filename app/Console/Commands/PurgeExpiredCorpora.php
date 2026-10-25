<?php

namespace App\Console\Commands;

use App\Models\Corpus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredCorpora extends Command
{
    protected $signature = 'corpuslab:purge-expired {--dry-run : List expired corpora without deleting data}';

    protected $description = 'Delete corpus entries and annotations after the approved retention date';

    public function handle(): int
    {
        $expired = Corpus::query()->where('status', 'approved')->whereDate('retention_until', '<', today());
        if ($this->option('dry-run')) {
            $expired->chunkById(50, function ($corpora) {
                foreach ($corpora as $corpus) {
                    $this->line("{$corpus->id}: {$corpus->title} · {$corpus->entries()->count()} entri");
                }
            });

            return self::SUCCESS;
        }

        $purged = 0;

        $expired->chunkById(50, function ($corpora) use (&$purged) {
            foreach ($corpora as $corpus) {
                DB::transaction(function () use ($corpus, &$purged) {
                    $entryCount = $corpus->entries()->count();
                    $corpus->entries()->delete();
                    $corpus->update(['status' => 'expired', 'purged_at' => now()]);
                    DB::table('audit_logs')->insert([
                        'actor_id' => null,
                        'project_id' => $corpus->project_id,
                        'action' => 'corpuslab.retention_purged',
                        'target_type' => 'corpus',
                        'target_id' => $corpus->id,
                        'metadata' => json_encode(['entry_count' => $entryCount, 'retention_until' => $corpus->retention_until->toDateString()]),
                        'created_at' => now(),
                    ]);
                });

                $purged++;
            }
        });

        $this->info("Korpus kedaluwarsa dibersihkan: {$purged}.");

        return self::SUCCESS;
    }
}
