<?php

namespace App\Http\Controllers;

use App\Models\Corpus;
use App\Models\GuruBahasaMaterial;
use App\Models\InstrumentBlueprint;
use App\Models\InstrumentBlueprintVersion;
use App\Models\LiteratureSource;
use App\Models\Project;
use App\Models\WritingAssignment;
use App\Models\WritingFeedbackReport;
use App\Services\LitSynth\ContentValidityCalculator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OutputEvidenceController extends Controller
{
    private const TYPES = [
        'literature_source' => 'Sumber literatur',
        'instrument_blueprint' => 'Blueprint instrumen',
        'expert_validation' => 'Validasi pakar',
        'gurubahasa_material' => 'Materi GuruBahasa',
        'writewise_assignment' => 'Tugas WriteWise',
        'writewise_feedback' => 'Feedback WriteWise',
        'corpuslab_protocol' => 'Protokol IndoEFL CorpusLab',
    ];

    private const STATUSES = [
        'uploaded' => 'Diunggah', 'needs_review' => 'Perlu review', 'extraction_unavailable' => 'Ekstraksi manual',
        'verified' => 'Terverifikasi', 'draft' => 'Draf', 'reviewed' => 'Ditinjau', 'expert_review' => 'Ada respons pakar',
        'curated' => 'Terkurasi', 'published' => 'Terbit', 'approved' => 'Disetujui',
    ];

    public function index(Request $request): View
    {
        $this->authorizeStaff();
        $projects = $this->accessibleProjects();
        $projectIds = $projects->pluck('id');
        $data = $request->validate([
            'project_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:literature_source,instrument_blueprint,expert_validation,gurubahasa_material,writewise_assignment,writewise_feedback,corpuslab_protocol'],
            'status' => ['nullable', 'in:uploaded,needs_review,extraction_unavailable,verified,draft,reviewed,expert_review,curated,published,approved'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        if (! empty($data['project_id']) && ! $projectIds->contains((int) $data['project_id'])) {
            abort(404);
        }
        $scopeIds = ! empty($data['project_id']) ? collect([(int) $data['project_id']]) : $projectIds;
        $projectNames = $projects->pluck('name', 'id');
        $items = collect();

        if ($this->includeType($data, 'literature_source')) {
            $items = $items->concat(LiteratureSource::query()->whereIn('project_id', $scopeIds)->with('matrix')->latest()->get()->map(fn ($record) => $this->row(
                'literature_source', $record->id, $record->project_id, $projectNames, $record->title ?: $record->original_name,
                trim(($record->authors ?: 'Penulis belum dicatat').($record->publication_year ? ' · '.$record->publication_year : '').' · '.number_format($record->file_size / 1024, 0).' KB'),
                $record->status, $record->updated_at
            )));
        }

        if ($this->includeType($data, 'instrument_blueprint')) {
            $items = $items->concat(InstrumentBlueprint::query()->whereIn('project_id', $scopeIds)->latest()->get()->map(fn ($record) => $this->row(
                'instrument_blueprint', $record->id, $record->project_id, $projectNames,
                $record->title.' · versi '.$record->current_version,
                $record->purpose,
                $record->status,
                $record->updated_at
            )));
        }

        if ($this->includeType($data, 'expert_validation')) {
            $versions = InstrumentBlueprintVersion::query()->whereHas('blueprint', fn ($query) => $query->whereIn('project_id', $scopeIds))
                ->whereHas('invitations', fn ($query) => $query->whereNotNull('completed_at'))
                ->with(['blueprint:id,project_id,title'])
                ->withCount(['invitations as response_count' => fn ($query) => $query->whereNotNull('completed_at')])
                ->latest('created_at')->get();
            $items = $items->concat($versions->map(fn ($record) => $this->row(
                'expert_validation', $record->id, $record->blueprint->project_id, $projectNames,
                $record->title.' · versi '.$record->version_number,
                $record->response_count.' respons pakar · '.$record->blueprint->title,
                'expert_review',
                $record->created_at
            )));
        }

        if ($this->includeType($data, 'gurubahasa_material')) {
            $items = $items->concat(GuruBahasaMaterial::query()->whereIn('project_id', $scopeIds)->latest()->get()->map(fn ($record) => $this->row(
                'gurubahasa_material', $record->id, $record->project_id, $projectNames,
                $record->title,
                $record->topic.' · target CEFR '.$record->cefr_level.' (perkiraan)',
                $record->status,
                $record->updated_at
            )));
        }

        if ($this->includeType($data, 'writewise_assignment')) {
            $items = $items->concat(WritingAssignment::query()->whereIn('project_id', $scopeIds)->where('status', 'published')->latest()->get()->map(fn ($record) => $this->row(
                'writewise_assignment', $record->id, $record->project_id, $projectNames,
                $record->title,
                'Tugas WriteWise terbit · '.$record->feedback_language,
                $record->status,
                $record->updated_at
            )));
        }

        if ($this->includeType($data, 'writewise_feedback')) {
            $reports = WritingFeedbackReport::query()->where('status', 'published')
                ->whereHas('revision.submission.assignment', fn ($query) => $query->whereIn('project_id', $scopeIds))
                ->with('revision.submission.assignment:id,project_id,title')
                ->latest('published_at')->get();
            $items = $items->concat($reports->map(fn ($record) => $this->row(
                'writewise_feedback', $record->id, $record->revision->submission->assignment->project_id, $projectNames,
                $record->revision->submission->assignment->title.' · revisi '.$record->revision->revision_number,
                'Feedback dosen terbit · '.$record->published_at?->format('d M Y'),
                $record->status,
                $record->published_at ?? $record->updated_at
            )));
        }

        if ($this->includeType($data, 'corpuslab_protocol')) {
            $items = $items->concat(Corpus::query()->whereIn('project_id', $scopeIds)->where('status', 'approved')
                ->whereDate('retention_until', '>=', today())->latest()->get()->map(fn ($record) => $this->row(
                    'corpuslab_protocol', $record->id, $record->project_id, $projectNames, $record->title,
                    $record->language.' · protokol penelitian · retensi sampai '.$record->retention_until->format('d M Y'),
                    'approved', $record->reviewed_at ?? $record->updated_at
                )));
        }

        $needle = trim($data['q'] ?? '');
        $items = $items->filter(function (array $item) use ($needle, $data) {
            if (! empty($data['status']) && $item['status'] !== $data['status']) {
                return false;
            }
            if ($needle !== '' && ! str_contains(mb_strtolower($item['title'].' '.$item['description'].' '.$item['project_name']), mb_strtolower($needle))) {
                return false;
            }

            return true;
        })->sortByDesc('created_at')->values();

        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $results = new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);

        return view('outputs.index', ['items' => $results, 'projects' => $projects, 'filters' => $data, 'types' => self::TYPES, 'statuses' => self::STATUSES]);
    }

    public function show(string $type, int $id, ContentValidityCalculator $calculator): View
    {
        $this->authorizeStaff();
        $artifact = $this->resolveArtifact($type, $id);
        if ($type === 'expert_validation') {
            $artifact['statistics'] = $calculator->calculate($artifact['record']);
            $artifact['record']->load('invitations.response.ratings');
        }

        return view('outputs.show', compact('artifact'));
    }

    public function download(string $type, int $id): Response
    {
        $this->authorizeStaff();
        $artifact = $this->resolveArtifact($type, $id);
        if ($type === 'literature_source') {
            abort_unless(Storage::disk('local')->exists($artifact['record']->storage_path), 404);

            return response()->download(Storage::disk('local')->path($artifact['record']->storage_path), $artifact['record']->original_name, ['Content-Type' => 'application/pdf']);
        }

        return response()->view('outputs.export', compact('artifact'))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.str($artifact['title'])->slug('-')->append('.html').'"');
    }

    /** @return array<string, mixed> */
    private function resolveArtifact(string $type, int $id): array
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);
        $projects = $this->accessibleProjects();
        $projectIds = $projects->pluck('id');
        $record = match ($type) {
            'literature_source' => LiteratureSource::query()->whereIn('project_id', $projectIds)->with(['project', 'matrix', 'pages'])->findOrFail($id),
            'instrument_blueprint' => InstrumentBlueprint::query()->whereIn('project_id', $projectIds)->with('project')->findOrFail($id),
            'expert_validation' => InstrumentBlueprintVersion::query()->whereHas('blueprint', fn ($query) => $query->whereIn('project_id', $projectIds))
                ->whereHas('invitations', fn ($query) => $query->whereNotNull('completed_at'))
                ->with(['blueprint.project', 'invitations.response.ratings'])->findOrFail($id),
            'gurubahasa_material' => GuruBahasaMaterial::query()->whereIn('project_id', $projectIds)->with('project')->findOrFail($id),
            'writewise_assignment' => WritingAssignment::query()->whereIn('project_id', $projectIds)->where('status', 'published')->with('project')->findOrFail($id),
            'writewise_feedback' => WritingFeedbackReport::query()->where('status', 'published')
                ->whereHas('revision.submission.assignment', fn ($query) => $query->whereIn('project_id', $projectIds))
                ->with('revision.submission.assignment.project')->findOrFail($id),
            'corpuslab_protocol' => Corpus::query()->whereIn('project_id', $projectIds)->where('status', 'approved')
                ->whereDate('retention_until', '>=', today())->with('project')->findOrFail($id),
        };
        $project = match ($type) {
            'expert_validation' => $record->blueprint->project,
            'writewise_feedback' => $record->revision->submission->assignment->project,
            default => $record->project,
        };
        $title = match ($type) {
            'literature_source' => $record->title ?: $record->original_name,
            'instrument_blueprint' => $record->title.' · versi '.$record->current_version,
            'expert_validation' => $record->title.' · versi '.$record->version_number,
            'gurubahasa_material' => $record->title,
            'writewise_assignment' => $record->title,
            'writewise_feedback' => $record->revision->submission->assignment->title.' · revisi '.$record->revision->revision_number,
            'corpuslab_protocol' => $record->title,
        };
        $status = match ($type) {
            'expert_validation' => 'expert_review',
            'writewise_feedback', 'writewise_assignment' => 'published',
            'corpuslab_protocol' => 'approved',
            default => $record->status,
        };

        return [
            'type' => $type, 'type_label' => self::TYPES[$type], 'id' => $record->id, 'record' => $record,
            'project' => $project, 'title' => $title, 'status' => $status, 'status_label' => self::STATUSES[$status] ?? $status,
        ];
    }

    private function includeType(array $filters, string $type): bool
    {
        return empty($filters['type']) || $filters['type'] === $type;
    }

    private function row(string $type, int $id, int $projectId, Collection $projectNames, string $title, string $description, string $status, mixed $createdAt): array
    {
        return [
            'type' => $type, 'type_label' => self::TYPES[$type], 'id' => $id, 'project_id' => $projectId,
            'project_name' => $projectNames[$projectId] ?? 'Proyek', 'title' => $title, 'description' => $description,
            'status' => $status, 'status_label' => self::STATUSES[$status] ?? $status, 'created_at' => $createdAt,
            'show_url' => route('outputs.show', [$type, $id]), 'download_url' => route('outputs.download', [$type, $id]),
        ];
    }

    private function accessibleProjects(): Collection
    {
        $user = request()->user();
        abort_unless($user->isStaff(), 403);

        return Project::query()->where(fn ($query) => $query->where('owner_id', $user->id)
            ->orWhereHas('members', fn ($members) => $members->where('users.id', $user->id)->where('project_members.role', 'editor')))
            ->orderBy('name')->get(['id', 'name', 'owner_id', 'archived_at']);
    }

    private function authorizeStaff(): void
    {
        abort_unless(request()->user()->isStaff(), 403);
    }
}
