<?php

namespace Tests\Feature;

use App\Jobs\ExtractPdfTextJob;
use App\Models\LiteratureSource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AsyncPdfExtractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploading_pdf_dispatches_async_extraction_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $project = Project::create([
            'owner_id' => $lecturer->id,
            'name' => 'Kajian Linguistik',
            'pillars' => ['research'],
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->createWithContent('sample.pdf', "%PDF-1.4\n%Fake PDF content for test");

        $response = $this->actingAs($lecturer)->post(route('litsynth.sources.store', $project), [
            'pdf' => $file,
            'title' => 'Studi Kasus Pembelajaran Bahasa',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('literature_sources', [
            'project_id' => $project->id,
            'title' => 'Studi Kasus Pembelajaran Bahasa',
            'status' => 'processing',
        ]);

        $source = LiteratureSource::first();
        Queue::assertPushed(ExtractPdfTextJob::class, function (ExtractPdfTextJob $job) use ($source) {
            return $job->literatureSourceId === $source->id;
        });
    }

    public function test_manual_retry_extraction_updates_status_and_dispatches_job(): void
    {
        Queue::fake();

        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $project = Project::create([
            'owner_id' => $lecturer->id,
            'name' => 'Kajian Linguistik',
            'pillars' => ['research'],
            'status' => 'active',
        ]);

        $source = $project->literatureSources()->create([
            'uploaded_by' => $lecturer->id,
            'original_name' => 'manual.pdf',
            'storage_path' => 'litsynth/'.$project->id.'/manual.pdf',
            'sha256' => hash('sha256', 'dummy'),
            'file_size' => 1024,
            'title' => 'Manual Source',
            'status' => 'extraction_unavailable',
            'extraction_message' => 'Gagal sebelumnya',
        ]);

        $response = $this->actingAs($lecturer)->post(route('litsynth.sources.extract', [$project, $source]));
        $response->assertRedirect();
        $response->assertSessionHas('status');

        $source->refresh();
        $this->assertEquals('processing', $source->status);
        $this->assertNull($source->extraction_message);

        Queue::assertPushed(ExtractPdfTextJob::class, function (ExtractPdfTextJob $job) use ($source) {
            return $job->literatureSourceId === $source->id;
        });
    }
}
