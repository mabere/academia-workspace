<?php

namespace App\Http\Controllers;

use App\Models\Corpus;
use App\Models\CorpusEntry;
use App\Models\Project;
use App\Models\User;
use App\Services\CorpusLab\CorpusTextAnonymizer;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CorpusLabController extends Controller
{
    public function reviewQueue(Request $request): View
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        $corpora = Corpus::query()->where('status', 'pending_review')->with(['project:id,name', 'creator:id,name'])
            ->latest('updated_at')->paginate(20);

        return view('corpuslab.reviews', compact('corpora'));
    }

    public function index(Request $request, Project $project): View
    {
        $this->authorizeProjectOrReviewer($project);
        abort_unless(in_array('research', $project->pillars ?? [], true), 409, 'IndoEFL CorpusLab hanya tersedia pada proyek dengan pilar penelitian.');
        $corpus = $project->corpus;
        $entries = new LengthAwarePaginator([], 0, 20);
        $stats = ['entries' => 0, 'pending' => 0, 'included' => 0, 'excluded' => 0, 'words' => 0];
        $query = $request->query('q', '');
        $query = is_string($query) ? mb_substr(trim($query), 0, 100) : '';

        if ($corpus && $corpus->isCollecting()) {
            $baseQuery = $corpus->entries()->withCount('annotations');
            $stats = [
                'entries' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
                'included' => (clone $baseQuery)->where('status', 'included')->count(),
                'excluded' => (clone $baseQuery)->where('status', 'excluded')->count(),
                'words' => (clone $baseQuery)->sum('word_count'),
            ];
            $entryQuery = $corpus->entries()->withCount('annotations')->latest();
            if ($query !== '') {
                $entryQuery->where(function ($entriesQuery) use ($query) {
                    $entriesQuery->where('anonymized_text', 'like', '%'.$query.'%')
                        ->orWhere('participant_code', 'like', '%'.$query.'%');
                });
            }
            $entries = $entryQuery->paginate(20)->withQueryString();
            if ($query !== '') {
                $entries->getCollection()->transform(function (CorpusEntry $entry) use ($query) {
                    $entry->setAttribute('kwic', $this->kwic($entry->anonymized_text, $query));

                    return $entry;
                });
            }
        }

        $canManageProtocol = $project->isOwnedBy($request->user()) && $project->archived_at === null;
        $canReview = $request->user()->role === 'superadmin' && $corpus?->status === 'pending_review';
        $canEditEntries = $this->canAccessProject($project, $request->user()) && $project->archived_at === null && $corpus?->isCollecting();

        return view('corpuslab.index', compact('project', 'corpus', 'entries', 'stats', 'query', 'canManageProtocol', 'canReview', 'canEditEntries'));
    }

    public function storeProtocol(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeOwner($project);
        abort_unless(in_array('research', $project->pillars ?? [], true), 409);
        abort_if($project->corpus()->exists(), 409, 'Proyek ini sudah memiliki protokol korpus.');
        $data = $request->validate($this->protocolRules());
        abort_if($data['retention_until'] < $data['ethics_approved_on'], 422, 'Tanggal retensi harus setelah persetujuan etik.');

        $corpus = DB::transaction(function () use ($project, $request, $data) {
            $corpus = $project->corpus()->create($data + ['created_by' => $request->user()->id]);
            $this->audit($request, $project, 'corpuslab.protocol_created', 'corpus', $corpus->id);

            return $corpus;
        });

        return redirect()->route('corpuslab.index', $project)->with('status', 'Draf protokol korpus dibuat. Periksa kesesuaian persetujuan etik dan rencana privasi sebelum mengajukan review.');
    }

    public function updateProtocol(Request $request, Project $project, Corpus $corpus): RedirectResponse
    {
        $this->authorizeOwner($project);
        $this->assertCorpus($project, $corpus);
        abort_unless(in_array($corpus->status, ['draft', 'rejected'], true), 409, 'Protokol yang sedang ditinjau atau sudah disetujui tidak dapat diubah.');
        $data = $request->validate($this->protocolRules());
        abort_if($data['retention_until'] < $data['ethics_approved_on'], 422, 'Tanggal retensi harus setelah persetujuan etik.');

        DB::transaction(function () use ($corpus, $request, $project, $data) {
            $corpus->update($data + ['status' => 'draft', 'review_notes' => null, 'reviewed_by' => null, 'reviewed_at' => null]);
            $this->audit($request, $project, 'corpuslab.protocol_updated', 'corpus', $corpus->id);
        });

        return back()->with('status', 'Protokol diperbarui sebagai draf.');
    }

    public function submitProtocol(Request $request, Project $project, Corpus $corpus): RedirectResponse
    {
        $this->authorizeOwner($project);
        $this->assertCorpus($project, $corpus);
        abort_unless(in_array($corpus->status, ['draft', 'rejected'], true), 409);
        abort_if($project->archived_at !== null, 409, 'Proyek yang diarsipkan tidak dapat mengajukan protokol.');
        abort_if($corpus->ethics_approved_on->isFuture(), 422, 'Persetujuan etik harus sudah berlaku sebelum protokol diajukan.');
        abort_if($corpus->retention_until->isPast(), 422, 'Tanggal retensi harus berada di masa depan.');

        DB::transaction(function () use ($corpus, $request, $project) {
            $corpus->update(['status' => 'pending_review', 'review_notes' => null]);
            $this->audit($request, $project, 'corpuslab.protocol_submitted', 'corpus', $corpus->id);
        });

        return back()->with('status', 'Protokol diajukan untuk pemeriksaan superadmin. Pengumpulan data tetap terkunci sampai disetujui.');
    }

    public function decideProtocol(Request $request, Project $project, Corpus $corpus): RedirectResponse
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        $this->assertCorpus($project, $corpus);
        abort_if($corpus->created_by === $request->user()->id, 403, 'Pemilik/pembuat protokol tidak dapat menyetujui protokolnya sendiri.');
        abort_unless($corpus->status === 'pending_review', 409);
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'review_notes' => ['nullable', 'string', 'max:4000', 'required_if:decision,rejected'],
            'ethics_confirmed' => ['required_if:decision,approved', 'accepted'],
        ]);
        abort_if($data['decision'] === 'approved' && ($corpus->ethics_approved_on->isFuture() || $corpus->retention_until->isPast()), 422, 'Tanggal persetujuan etik atau retensi tidak memenuhi syarat.');

        DB::transaction(function () use ($corpus, $request, $project, $data) {
            $corpus->update([
                'status' => $data['decision'], 'review_notes' => $data['review_notes'] ?? null,
                'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
            ]);
            $this->audit($request, $project, 'corpuslab.protocol_'.$data['decision'], 'corpus', $corpus->id, [
                'ethics_reference' => $corpus->ethics_reference,
            ]);
        });

        return back()->with('status', $data['decision'] === 'approved'
            ? 'Protokol disetujui. Pengumpulan lokal hanya boleh dilakukan sesuai persetujuan etik yang tercatat.'
            : 'Protokol dikembalikan kepada pemilik proyek beserta catatan review.');
    }

    public function storeEntry(Request $request, Project $project, Corpus $corpus, CorpusTextAnonymizer $anonymizer): RedirectResponse
    {
        $this->authorizeProject($project, true);
        $this->assertCorpus($project, $corpus);
        abort_unless($corpus->isCollecting(), 409, 'Pengumpulan data terkunci atau masa retensi berakhir.');
        $validator = Validator::make($request->all(), [
            'participant_code' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'consent_confirmed' => ['required', 'accepted'],
            'consent_reference' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9._-]+$/'],
            'collected_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'text' => ['required', 'string', 'max:30000'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except(['text', '_token']));
        }

        $data = $validator->validated();
        abort_if($data['collected_on'] < $corpus->ethics_approved_on->toDateString(), 422, 'Data tidak boleh dikumpulkan sebelum persetujuan etik berlaku.');
        if (! mb_check_encoding($data['text'], 'UTF-8')) {
            return back()->withErrors(['text' => 'Teks harus menggunakan encoding UTF-8 yang valid.'])
                ->withInput($request->except(['text', '_token']));
        }
        $text = trim($anonymizer->redact($data['text']));
        abort_if($text === '', 422, 'Teks kosong setelah proses pembersihan otomatis.');
        $wordCount = count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        try {
            DB::transaction(function () use ($request, $project, $corpus, $data, $text, $wordCount) {
                $entry = $corpus->entries()->create([
                    'created_by' => $request->user()->id,
                    'participant_code' => $data['participant_code'],
                    'consent_reference' => $data['consent_reference'] ?? null,
                    'consent_confirmed' => true,
                    'collected_on' => $data['collected_on'],
                    'anonymized_text' => $text,
                    'word_count' => $wordCount,
                    'status' => 'pending',
                ]);
                $this->audit($request, $project, 'corpuslab.entry_added', 'corpus_entry', $entry->id, ['word_count' => $wordCount]);
            });
        } catch (QueryException $exception) {
            Log::error('CorpusLab entry transaction failed; text payload omitted.', [
                'corpus_id' => $corpus->id, 'exception_type' => $exception::class,
            ]);

            return back()->withErrors(['text' => 'Entri gagal disimpan. Teks tidak dicatat pada log aplikasi; coba lagi atau hubungi administrator.'])
                ->withInput($request->except(['text', '_token']));
        }

        return back()->with('status', 'Entri tersimpan setelah pembersihan pola email, URL, dan nomor yang menyerupai telepon. Tinjau anonimisasi manual sebelum memasukkannya ke analisis.');
    }

    public function annotate(Request $request, Project $project, Corpus $corpus, CorpusEntry $entry, CorpusTextAnonymizer $anonymizer): RedirectResponse
    {
        $this->authorizeProject($project, true);
        $this->assertEntry($corpus, $entry);
        abort_unless($corpus->isCollecting(), 409);
        $validator = Validator::make($request->all(), [
            'label' => ['required', 'string', 'max:100'],
            'span_text' => ['nullable', 'string', 'max:2000'],
            'comment' => ['nullable', 'string', 'max:4000'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except(['span_text', 'comment', '_token']));
        }

        $data = $validator->validated();
        foreach (['label', 'span_text', 'comment'] as $field) {
            if (isset($data[$field]) && ! mb_check_encoding($data[$field], 'UTF-8')) {
                return back()->withErrors([$field => 'Anotasi harus menggunakan encoding UTF-8 yang valid.'])
                    ->withInput($request->except(['span_text', 'comment', '_token']));
            }
        }
        try {
            DB::transaction(function () use ($entry, $request, $project, $anonymizer, $data) {
                $entry->annotations()->create([
                    'created_by' => $request->user()->id,
                    'label' => $anonymizer->redact($data['label']),
                    'span_text' => isset($data['span_text']) ? $anonymizer->redact($data['span_text']) : null,
                    'comment' => isset($data['comment']) ? $anonymizer->redact($data['comment']) : null,
                ]);
                $this->audit($request, $project, 'corpuslab.annotation_added', 'corpus_entry', $entry->id);
            });
        } catch (QueryException $exception) {
            Log::error('CorpusLab annotation transaction failed; text payload omitted.', [
                'entry_id' => $entry->id, 'exception_type' => $exception::class,
            ]);

            return back()->withErrors(['comment' => 'Anotasi gagal disimpan. Isi anotasi tidak dicatat pada log aplikasi.'])
                ->withInput($request->except(['span_text', 'comment', '_token']));
        }

        return back()->with('status', 'Anotasi ditambahkan.');
    }

    public function updateEntryStatus(Request $request, Project $project, Corpus $corpus, CorpusEntry $entry): RedirectResponse
    {
        $this->authorizeProject($project, true);
        $this->assertEntry($corpus, $entry);
        abort_unless($corpus->isCollecting(), 409);
        $data = $request->validate(['status' => ['required', 'in:included,excluded']]);
        $entry->update(['status' => $data['status']]);
        $this->audit($request, $project, 'corpuslab.entry_'.$data['status'], 'corpus_entry', $entry->id);

        return back()->with('status', 'Status inklusi entri diperbarui.');
    }

    public function destroyEntry(Request $request, Project $project, Corpus $corpus, CorpusEntry $entry): RedirectResponse
    {
        $this->authorizeProject($project, true);
        $this->assertEntry($corpus, $entry);
        abort_unless($corpus->isCollecting(), 409);
        $data = $request->validate(['reason' => ['required', 'in:participant_withdrew,entered_in_error']]);

        DB::transaction(function () use ($entry, $request, $project, $data) {
            $entryId = $entry->id;
            $entry->delete();
            $this->audit($request, $project, 'corpuslab.entry_deleted', 'corpus_entry', $entryId, ['reason' => $data['reason']]);
        });

        return back()->with('status', 'Entri dan seluruh anotasinya dihapus.');
    }

    public function export(Request $request, Project $project, Corpus $corpus): StreamedResponse
    {
        $this->authorizeProject($project);
        $this->assertCorpus($project, $corpus);
        abort_unless($corpus->isCollecting(), 409, 'Ekspor dinonaktifkan setelah masa retensi berakhir atau protokol ditutup.');
        $this->audit($request, $project, 'corpuslab.exported', 'corpus', $corpus->id, ['format' => 'csv']);

        return response()->streamDownload(function () use ($corpus) {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['participant_code', 'collected_on', 'status', 'text_anonymized', 'annotations'], ',', '"', '\\');
            $corpus->entries()->with('annotations')->orderBy('id')->chunkById(200, function ($entries) use ($stream) {
                foreach ($entries as $entry) {
                    $annotations = $entry->annotations->map(fn ($annotation) => implode(': ', array_filter([
                        $annotation->label, $annotation->span_text, $annotation->comment,
                    ])))->implode(' | ');
                    fputcsv($stream, [
                        $this->csvSafe($entry->participant_code), $entry->collected_on->toDateString(), $entry->status,
                        $this->csvSafe($entry->anonymized_text), $this->csvSafe($annotations),
                    ], ',', '"', '\\');
                }
            });
            fclose($stream);
        }, str($corpus->title)->slug('-').'-corpus.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    /** @return array<string, string|array<int, string>> */
    private function protocolRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'language' => ['required', 'string', 'max:80'],
            'research_purpose' => ['required', 'string', 'max:5000'],
            'participant_population' => ['required', 'string', 'max:3000'],
            'sampling_method' => ['required', 'string', 'max:3000'],
            'consent_process' => ['required', 'string', 'max:3000'],
            'ethics_authority' => ['required', 'string', 'max:180'],
            'ethics_reference' => ['required', 'string', 'max:120'],
            'ethics_approved_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'anonymization_plan' => ['required', 'string', 'max:4000'],
            'privacy_plan' => ['required', 'string', 'max:4000'],
            'retention_until' => ['required', 'date_format:Y-m-d', 'after:today'],
        ];
    }

    private function authorizeProject(Project $project, bool $write = false): void
    {
        $user = request()->user();
        abort_unless($this->canAccessProject($project, $user), 403);
        if ($write) {
            abort_if($project->archived_at !== null, 409, 'Proyek yang diarsipkan hanya dapat dibaca.');
        }
    }

    private function authorizeProjectOrReviewer(Project $project): void
    {
        $user = request()->user();
        abort_unless($user->isStaff() && ($user->role === 'superadmin' || $this->canAccessProject($project, $user)), 403);
    }

    private function canAccessProject(Project $project, User $user): bool
    {
        return $user->isStaff() && ($project->isOwnedBy($user) || $project->hasEditor($user));
    }

    private function authorizeOwner(Project $project): void
    {
        abort_unless(request()->user()->isStaff() && $project->isOwnedBy(request()->user()), 403);
        abort_if($project->archived_at !== null, 409, 'Proyek yang diarsipkan tidak dapat diubah.');
    }

    private function assertCorpus(Project $project, Corpus $corpus): void
    {
        abort_unless($corpus->project_id === $project->id, 404);
    }

    private function assertEntry(Corpus $corpus, CorpusEntry $entry): void
    {
        abort_unless($entry->corpus_id === $corpus->id, 404);
    }

    private function audit(Request $request, Project $project, string $action, string $targetType, int $targetId, array $metadata = []): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()?->id,
            'project_id' => $project->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'created_at' => now(),
        ]);
    }

    private function kwic(string $text, string $term): string
    {
        $position = mb_stripos($text, $term);
        if ($position === false) {
            return mb_substr($text, 0, 240);
        }
        $start = max(0, $position - 90);
        $snippet = mb_substr($text, $start, mb_strlen($term) + 180);

        return ($start > 0 ? '…' : '').$snippet.(mb_strlen($text) > $start + mb_strlen($snippet) ? '…' : '');
    }

    private function csvSafe(string $value): string
    {
        return preg_match('/^[\s]*[=+@\-\t\r]/u', $value) ? "'".$value : $value;
    }
}
