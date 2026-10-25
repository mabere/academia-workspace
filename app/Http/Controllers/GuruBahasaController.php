<?php

namespace App\Http\Controllers;

use App\Models\GuruBahasaMaterial;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GuruBahasaController extends Controller
{
    public function index(Project $project): View
    {
        $this->authorizeProject($project);
        $materials = $project->guruBahasaMaterials()->with('creator:id,name')->latest()->paginate(12);

        return view('gurubahasa.index', compact('project', 'materials'));
    }

    public function create(Project $project): View
    {
        $this->authorizeProject($project, true);
        $material = new GuruBahasaMaterial;

        return view('gurubahasa.create', compact('project', 'material'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($project, true);
        $content = $this->validatedContent($request);
        $material = $project->guruBahasaMaterials()->create($content + ['created_by' => $request->user()->id, 'status' => 'draft']);
        $this->audit($request, $project, 'gurubahasa.material_created', $material);

        return redirect()->route('gurubahasa.show', [$project, $material])->with('status', 'Draf materi tersimpan. Tinjau dan isi bagian yang diperlukan sebelum kurasi.');
    }

    public function show(Project $project, GuruBahasaMaterial $material): View
    {
        $this->authorizeMaterial($project, $material);

        return view('gurubahasa.show', compact('project', 'material'));
    }

    public function edit(Project $project, GuruBahasaMaterial $material): View
    {
        $this->authorizeMaterial($project, $material, true);

        return view('gurubahasa.edit', compact('project', 'material'));
    }

    public function update(Request $request, Project $project, GuruBahasaMaterial $material): RedirectResponse
    {
        $this->authorizeMaterial($project, $material, true);
        $material->update($this->validatedContent($request) + ['status' => 'draft', 'curated_at' => null, 'curated_by' => null]);
        $this->audit($request, $project, 'gurubahasa.material_updated', $material);

        return back()->with('status', 'Perubahan tersimpan sebagai draf. Status kurasi direset agar perubahan ditinjau kembali.');
    }

    public function curate(Request $request, Project $project, GuruBahasaMaterial $material): RedirectResponse
    {
        $this->authorizeMaterial($project, $material, true);
        $missing = collect([
            'teks pembelajaran' => filled($material->draft_text),
            'glosarium' => ! empty($material->glossary),
            'soal' => ! empty($material->questions),
            'worksheet' => filled($material->worksheet),
            'aktivitas' => ! empty($material->activities),
            'rubrik' => ! empty($material->rubric),
        ])->filter(fn (bool $complete) => ! $complete)->keys();
        if ($missing->isNotEmpty()) {
            return back()->withErrors(['curation' => 'Lengkapi sebelum kurasi: '.$missing->implode(', ').'.']);
        }
        $material->update(['status' => 'curated', 'curated_at' => now(), 'curated_by' => $request->user()->id]);
        $this->audit($request, $project, 'gurubahasa.material_curated', $material);

        return back()->with('status', 'Materi ditandai selesai dikurasi oleh Anda.');
    }

    public function publish(Request $request, Project $project, GuruBahasaMaterial $material): RedirectResponse
    {
        $this->authorizeMaterial($project, $material, true);
        abort_unless($material->status === 'curated', 409, 'Kurasi materi terlebih dahulu sebelum menandai siap dibagikan.');
        $material->update(['status' => 'published']);
        $this->audit($request, $project, 'gurubahasa.material_published', $material);

        return back()->with('status', 'Materi ditandai siap dibagikan. Halaman ini tetap berada di dalam akses proyek.');
    }

    public function export(Project $project, GuruBahasaMaterial $material): Response
    {
        $this->authorizeMaterial($project, $material);
        $filename = str($material->title)->slug('-')->append('.html');

        return response()->view('gurubahasa.export', compact('project', 'material'))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function destroy(Request $request, Project $project, GuruBahasaMaterial $material): RedirectResponse
    {
        $this->authorizeMaterial($project, $material, true);
        $id = $material->id;
        $material->delete();
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id, 'project_id' => $project->id,
            'action' => 'gurubahasa.material_deleted', 'target_type' => 'gurubahasa_material', 'target_id' => $id,
            'metadata' => null, 'created_at' => now(),
        ]);

        return redirect()->route('gurubahasa.index', $project)->with('status', 'Draf materi dihapus.');
    }

    /** @return array<string, mixed> */
    private function validatedContent(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'topic' => ['required', 'string', 'max:180'],
            'participant_context' => ['required', 'string', 'max:4000'],
            'cefr_level' => ['required', 'in:A1,A2,B1,B2,C1,C2'],
            'source_text' => ['nullable', 'string', 'max:40000'],
            'learning_objectives' => ['nullable', 'string', 'max:10000'],
            'draft_text' => ['nullable', 'string', 'max:40000'],
            'glossary_text' => ['nullable', 'string', 'max:16000'],
            'questions_text' => ['nullable', 'string', 'max:16000'],
            'worksheet' => ['nullable', 'string', 'max:30000'],
            'activities_text' => ['nullable', 'string', 'max:16000'],
            'rubric_text' => ['nullable', 'string', 'max:16000'],
        ]);

        return [
            'title' => $data['title'], 'topic' => $data['topic'],
            'participant_context' => $data['participant_context'], 'cefr_level' => $data['cefr_level'],
            'source_text' => $data['source_text'] ?? null, 'learning_objectives' => $data['learning_objectives'] ?? null,
            'draft_text' => $data['draft_text'] ?? null,
            'glossary' => $this->parseRows($data['glossary_text'] ?? '', 3, 'Istilah | arti | contoh'),
            'questions' => $this->parseRows($data['questions_text'] ?? '', 2, 'Pertanyaan | rambu jawaban'),
            'worksheet' => $data['worksheet'] ?? null,
            'activities' => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['activities_text'] ?? '') ?: []))),
            'rubric' => $this->parseRows($data['rubric_text'] ?? '', 4, 'Kriteria | awal | berkembang | mahir'),
        ];
    }

    /** @return array<int, array<string, string>> */
    private function parseRows(string $text, int $columns, string $format): array
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line));
            if (count($parts) !== $columns || in_array('', $parts, true)) {
                throw ValidationException::withMessages([
                    $columns === 3 ? 'glossary_text' : ($columns === 2 ? 'questions_text' : 'rubric_text') => 'Setiap baris harus mengikuti format: '.$format,
                ]);
            }
            $rows[] = $parts;
        }

        return $rows;
    }

    private function authorizeProject(Project $project, bool $write = false): void
    {
        abort_unless(request()->user()->isStaff() && ($project->isOwnedBy(request()->user()) || $project->hasEditor(request()->user())), 403);
        if ($write) {
            abort_if($project->archived_at !== null, 409, 'Proyek yang diarsipkan hanya dapat dibaca.');
        }
    }

    private function authorizeMaterial(Project $project, GuruBahasaMaterial $material, bool $write = false): void
    {
        abort_unless($material->project_id === $project->id, 404);
        $this->authorizeProject($project, $write);
    }

    private function audit(Request $request, Project $project, string $action, GuruBahasaMaterial $material): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id, 'project_id' => $project->id,
            'action' => $action, 'target_type' => 'gurubahasa_material', 'target_id' => $material->id,
            'metadata' => json_encode(['status' => $material->status, 'cefr_level' => $material->cefr_level]),
            'created_at' => now(),
        ]);
    }
}
