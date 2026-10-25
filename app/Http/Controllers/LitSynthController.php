<?php

namespace App\Http\Controllers;

use App\Models\InstrumentBlueprint;
use App\Models\LiteratureSource;
use App\Models\Project;
use App\Services\LitSynth\InstrumentVersionManager;
use App\Services\LitSynth\PdfTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class LitSynthController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        $this->authorizeProject($project);
        $project->load(['owner:id,name']);
        $query = $request->validate(['q' => ['nullable', 'string', 'max:120']])['q'] ?? null;
        $sourceQuery = $project->literatureSources()->with(['uploader:id,name', 'matrix'])->withCount('pages');
        if ($query !== null && trim($query) !== '') {
            $term = '%'.trim($query).'%';
            $sourceQuery->where(function ($sources) use ($term) {
                $sources->where('title', 'like', $term)
                    ->orWhere('authors', 'like', $term)
                    ->orWhere('abstract', 'like', $term)
                    ->orWhere('notes', 'like', $term)
                    ->orWhereHas('matrix', fn ($matrix) => $matrix->where('research_question', 'like', $term)->orWhere('key_findings', 'like', $term)->orWhere('relevance_notes', 'like', $term))
                    ->orWhereHas('pages', fn ($pages) => $pages->where('extracted_text', 'like', $term));
            });
        }
        $sources = $sourceQuery->latest()->paginate(20)->withQueryString();
        $allSources = $project->literatureSources()->orderBy('title')->get(['id', 'title', 'original_name']);
        $blueprints = $project->instrumentBlueprints()->latest()->get();

        return view('litsynth.index', compact('project', 'sources', 'allSources', 'blueprints', 'query'));
    }

    public function storeSource(Request $request, Project $project, PdfTextExtractor $extractor): RedirectResponse
    {
        $this->authorizeProject($project, true);
        $data = $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:'.config('litsynth.max_pdf_kilobytes', 25600)],
            'title' => ['nullable', 'string', 'max:500'],
        ]);
        $file = $data['pdf'];
        $handle = fopen($file->getRealPath(), 'rb');
        $signature = $handle ? fread($handle, 5) : '';
        if ($handle) {
            fclose($handle);
        }
        if ($signature !== '%PDF-') {
            return back()->withErrors(['pdf' => 'Berkas tidak memiliki signature PDF yang valid.'])->withInput();
        }

        $storedPath = $file->store('litsynth/'.$project->id, 'local');
        if (! $storedPath) {
            return back()->withErrors(['pdf' => 'PDF gagal disimpan di penyimpanan privat.']);
        }

        $sha256 = hash_file('sha256', Storage::disk('local')->path($storedPath));
        if ($sha256 === false) {
            Storage::disk('local')->delete($storedPath);

            return back()->withErrors(['pdf' => 'Hash PDF tidak dapat dihitung; berkas tidak disimpan.']);
        }

        try {
            $source = DB::transaction(function () use ($project, $request, $file, $data, $storedPath, $sha256) {
                $source = $project->literatureSources()->create([
                    'uploaded_by' => $request->user()->id,
                    'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                    'storage_path' => $storedPath,
                    'sha256' => $sha256,
                    'file_size' => $file->getSize(),
                    'title' => $data['title'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'status' => 'processing',
                ]);
                DB::table('audit_logs')->insert([
                    'actor_id' => $request->user()->id, 'project_id' => $project->id,
                    'action' => 'litsynth.source_uploaded', 'target_type' => 'literature_source', 'target_id' => $source->id,
                    'metadata' => json_encode(['sha256' => $source->sha256, 'file_size' => $source->file_size]), 'created_at' => now(),
                ]);

                return $source;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPath);
            throw $exception;
        }

        try {
            $pages = $extractor->extractPages(Storage::disk('local')->path($storedPath));
            DB::transaction(function () use ($source, $pages) {
                foreach ($pages as $index => $text) {
                    $source->pages()->create(['page_number' => $index + 1, 'extracted_text' => $text]);
                }
                $source->matrix()->create([]);
                $source->update(['status' => 'needs_review', 'extraction_message' => null]);
            });
            $message = 'PDF disimpan privat dan teks berhasil diekstrak. Periksa hasil ekstraksi sebelum memverifikasi matriks.';
        } catch (Throwable $exception) {
            $source->matrix()->create([]);
            $source->update([
                'status' => 'extraction_unavailable',
                'extraction_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
            $message = 'PDF tersimpan privat, tetapi ekstraksi otomatis belum tersedia. Lengkapi matriks secara manual atau pasang pdftotext lokal.';
        }

        return redirect()->route('litsynth.sources.show', [$project, $source])->with('status', $message);
    }

    public function showSource(Project $project, LiteratureSource $source): View
    {
        $this->authorizeSource($project, $source);
        $source->load(['pages', 'matrix', 'uploader:id,name']);

        return view('litsynth.source', compact('project', 'source'));
    }

    public function updateSource(Request $request, Project $project, LiteratureSource $source): RedirectResponse
    {
        $this->authorizeSource($project, $source, true);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'authors' => ['nullable', 'string', 'max:2000'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(now()->year + 1)],
            'abstract' => ['nullable', 'string', 'max:12000'],
            'notes' => ['nullable', 'string', 'max:12000'],
            'research_question' => ['nullable', 'string', 'max:12000'],
            'methodology' => ['nullable', 'string', 'max:12000'],
            'participants_context' => ['nullable', 'string', 'max:12000'],
            'key_findings' => ['nullable', 'string', 'max:20000'],
            'limitations' => ['nullable', 'string', 'max:12000'],
            'relevance_notes' => ['nullable', 'string', 'max:12000'],
            'evidence_pages' => ['nullable', 'array:research_question,methodology,participants_context,key_findings,limitations'],
            'evidence_pages.*' => ['nullable', 'integer', 'min:1'],
        ]);
        $matrixData = collect($data)->only([
            'research_question', 'methodology', 'participants_context', 'key_findings', 'limitations', 'relevance_notes',
        ])->all();
        $pages = $data['evidence_pages'] ?? [];
        foreach ($pages as $field => $page) {
            if ($page && ! $source->pages()->where('page_number', $page)->exists()) {
                return back()->withErrors(['evidence_pages.'.$field => 'Nomor halaman tidak tersedia pada hasil ekstraksi dokumen.'])->withInput();
            }
        }

        DB::transaction(function () use ($source, $project, $data, $matrixData, $pages, $request) {
            $source->update(collect($data)->only(['title', 'authors', 'publication_year', 'abstract', 'notes'])->all() + [
                'status' => 'needs_review', 'verified_by' => null, 'verified_at' => null,
            ]);
            $source->matrix()->updateOrCreate([], $matrixData + ['evidence_pages' => $pages, 'updated_by' => $request->user()->id]);
            DB::table('audit_logs')->insert([
                'actor_id' => $request->user()->id, 'project_id' => $project->id,
                'action' => 'litsynth.source_updated', 'target_type' => 'literature_source', 'target_id' => $source->id,
                'metadata' => null, 'created_at' => now(),
            ]);
        });

        return back()->with('status', 'Metadata dan matriks kajian disimpan. Periksa kembali bukti halaman lalu verifikasi.');
    }

    public function verifySource(Request $request, Project $project, LiteratureSource $source): RedirectResponse
    {
        $this->authorizeSource($project, $source, true);
        $matrix = $source->matrix;
        abort_unless($matrix && filled($matrix->research_question) && filled($matrix->key_findings), 422, 'Simpan pertanyaan/tujuan penelitian dan temuan utama sebelum memverifikasi.');
        $source->update(['status' => 'verified', 'verified_by' => $request->user()->id, 'verified_at' => now()]);
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id, 'project_id' => $project->id,
            'action' => 'litsynth.source_verified', 'target_type' => 'literature_source', 'target_id' => $source->id,
            'metadata' => null, 'created_at' => now(),
        ]);

        return back()->with('status', 'Matriks sumber ditandai terverifikasi oleh Anda.');
    }

    public function downloadSource(Project $project, LiteratureSource $source)
    {
        $this->authorizeSource($project, $source);
        abort_unless(Storage::disk('local')->exists($source->storage_path), 404);

        return response()->download(Storage::disk('local')->path($source->storage_path), $source->original_name, ['Content-Type' => 'application/pdf']);
    }

    public function destroySource(Project $project, LiteratureSource $source): RedirectResponse
    {
        $this->authorizeSource($project, $source, true);
        DB::transaction(function () use ($source, $project) {
            foreach ($project->instrumentBlueprints()->get() as $blueprint) {
                $blueprint->update(['literature_source_ids' => array_values(array_diff($blueprint->literature_source_ids ?? [], [$source->id]))]);
            }
            DB::table('audit_logs')->insert([
                'actor_id' => request()->user()->id, 'project_id' => $project->id,
                'action' => 'litsynth.source_deleted', 'target_type' => 'literature_source', 'target_id' => $source->id,
                'metadata' => null, 'created_at' => now(),
            ]);
            $source->delete();
        });
        Storage::disk('local')->delete($source->storage_path);

        return redirect()->route('litsynth.index', $project)->with('status', 'Sumber PDF privat dan data matriksnya dihapus.');
    }

    public function storeBlueprint(Request $request, Project $project, InstrumentVersionManager $versionManager): RedirectResponse
    {
        $this->authorizeProject($project, true);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'purpose' => ['required', 'string', 'max:12000'],
            'target_constructs' => ['nullable', 'string', 'max:12000'],
            'respondent_context' => ['nullable', 'string', 'max:12000'],
            'items_text' => ['nullable', 'string', 'max:30000'],
            'literature_source_ids' => ['nullable', 'array'],
            'literature_source_ids.*' => ['integer', 'distinct'],
        ]);
        $sources = $data['literature_source_ids'] ?? [];
        if ($sources && $project->literatureSources()->whereIn('id', $sources)->count() !== count($sources)) {
            return back()->withErrors(['literature_source_ids' => 'Referensi blueprint harus berasal dari proyek ini.'])->withInput();
        }
        $items = collect(preg_split('/\r\n|\r|\n/', $data['items_text'] ?? '') ?: [])->map(fn ($line) => trim($line))->filter()->values()->all();
        DB::transaction(function () use ($project, $request, $data, $items, $sources, $versionManager) {
            $blueprint = $project->instrumentBlueprints()->create([
                'created_by' => $request->user()->id,
                'title' => $data['title'], 'purpose' => $data['purpose'],
                'target_constructs' => $data['target_constructs'] ?? null,
                'respondent_context' => $data['respondent_context'] ?? null,
                'items' => $items, 'literature_source_ids' => $sources,
            ]);
            $versionManager->snapshot($blueprint, 1, $request->user()->id);
            DB::table('audit_logs')->insert([
                'actor_id' => $request->user()->id, 'project_id' => $project->id,
                'action' => 'litsynth.blueprint_created', 'target_type' => 'instrument_blueprint', 'target_id' => $blueprint->id,
                'metadata' => json_encode(['status' => $blueprint->status, 'version' => 1]), 'created_at' => now(),
            ]);
        });

        return back()->with('status', 'Draf blueprint instrumen berhasil disimpan.');
    }

    public function updateBlueprint(Request $request, Project $project, InstrumentBlueprint $blueprint, InstrumentVersionManager $versionManager): RedirectResponse
    {
        $this->authorizeProject($project, true);
        abort_unless($blueprint->project_id === $project->id, 404);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'purpose' => ['required', 'string', 'max:12000'],
            'target_constructs' => ['nullable', 'string', 'max:12000'],
            'respondent_context' => ['nullable', 'string', 'max:12000'],
            'items_text' => ['nullable', 'string', 'max:30000'],
            'status' => ['required', 'in:draft,reviewed'],
            'literature_source_ids' => ['nullable', 'array'],
            'literature_source_ids.*' => ['integer', 'distinct'],
        ]);
        $sources = $data['literature_source_ids'] ?? [];
        if ($sources && $project->literatureSources()->whereIn('id', $sources)->count() !== count($sources)) {
            return back()->withErrors(['literature_source_ids' => 'Referensi blueprint harus berasal dari proyek ini.'])->withInput();
        }
        $items = collect(preg_split('/\r\n|\r|\n/', $data['items_text'] ?? '') ?: [])->map(fn ($line) => trim($line))->filter()->values()->all();
        DB::transaction(function () use ($blueprint, $request, $data, $items, $sources, $versionManager, $project) {
            $version = $blueprint->current_version + 1;
            $blueprint->update([
                'title' => $data['title'], 'purpose' => $data['purpose'],
                'target_constructs' => $data['target_constructs'] ?? null,
                'respondent_context' => $data['respondent_context'] ?? null,
                'items' => $items, 'literature_source_ids' => $sources,
                'status' => $data['status'], 'current_version' => $version,
            ]);
            $versionManager->snapshot($blueprint, $version, $request->user()->id);
            DB::table('audit_logs')->insert([
                'actor_id' => $request->user()->id, 'project_id' => $project->id,
                'action' => 'litsynth.blueprint_updated', 'target_type' => 'instrument_blueprint', 'target_id' => $blueprint->id,
                'metadata' => json_encode(['status' => $blueprint->status, 'version' => $version]), 'created_at' => now(),
            ]);
        });

        return back()->with('status', 'Blueprint instrumen diperbarui.');
    }

    private function authorizeProject(Project $project, bool $write = false): void
    {
        abort_unless(request()->user()->isStaff() && ($project->isOwnedBy(request()->user()) || $project->hasEditor(request()->user())), 403);
        if ($write) {
            abort_if($project->archived_at !== null, 409, 'Proyek yang diarsipkan hanya dapat dibaca.');
        }
    }

    private function authorizeSource(Project $project, LiteratureSource $source, bool $write = false): void
    {
        abort_unless($source->project_id === $project->id, 404);
        $this->authorizeProject($project, $write);
    }
}
