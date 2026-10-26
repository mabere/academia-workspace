<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Project::class);

        $projects = Project::query()->whereNull('archived_at')
            ->where(fn ($query) => $query->where('owner_id', Auth::id())->orWhereHas('members', fn ($members) => $members->where('users.id', Auth::id())->where('project_members.role', 'editor')))
            ->with(['owner:id,name'])->withCount('assignments')->latest()->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        return view('projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $project = DB::transaction(function () use ($data) {
            $project = Project::create([...$data, 'owner_id' => Auth::id()]);
            DB::table('audit_logs')->insert(['actor_id' => Auth::id(), 'project_id' => $project->id, 'action' => 'project.created', 'target_type' => 'project', 'target_id' => $project->id, 'metadata' => json_encode(['status' => $project->status]), 'created_at' => now()]);

            return $project;
        });

        return redirect()->route('projects.show', $project)->with('status', 'Proyek berhasil dibuat.');
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        $project->load([
            'owner:id,name',
            'members:id,name,email',
            'assignments' => fn ($q) => $q->withCount(['students', 'submissions'])->latest(),
        ]);
        $eligibleMembers = User::query()->whereIn('role', ['lecturer', 'admin'])->where('id', '!=', $project->owner_id)->orderBy('name')->get(['id', 'name', 'email']);

        return view('projects.show', compact('project', 'eligibleMembers'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        abort_if($project->archived_at !== null, 409, 'Proyek yang diarsipkan tidak dapat diubah.');

        $data = $request->validated();
        DB::transaction(function () use ($project, $data) {
            $project->update($data);
            DB::table('audit_logs')->insert(['actor_id' => Auth::id(), 'project_id' => $project->id, 'action' => 'project.updated', 'target_type' => 'project', 'target_id' => $project->id, 'metadata' => null, 'created_at' => now()]);
        });

        return back()->with('status', 'Proyek diperbarui.');
    }

    public function archive(Project $project): RedirectResponse
    {
        Gate::authorize('archive', $project);
        abort_if($project->archived_at !== null, 409, 'Proyek sudah diarsipkan.');

        $project->update(['archived_at' => now(), 'status' => 'archived']);
        DB::table('audit_logs')->insert(['actor_id' => Auth::id(), 'project_id' => $project->id, 'action' => 'project.archived', 'target_type' => 'project', 'target_id' => $project->id, 'metadata' => null, 'created_at' => now()]);

        return redirect()->route('projects.index')->with('status', 'Proyek diarsipkan.');
    }

    public function addMember(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('manageMembers', $project);
        abort_if($project->archived_at !== null, 409, 'Anggota tidak dapat diubah pada proyek yang diarsipkan.');

        $data = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $member = User::query()->findOrFail($data['user_id']);
        if (! $member->isStaff() || $project->isOwnedBy($member)) {
            throw ValidationException::withMessages(['user_id' => 'Pilih dosen/admin selain pemilik proyek.']);
        }
        $project->members()->syncWithoutDetaching([$member->id => ['role' => 'editor']]);
        DB::table('audit_logs')->insert(['actor_id' => Auth::id(), 'project_id' => $project->id, 'action' => 'project.member_added', 'target_type' => 'user', 'target_id' => $member->id, 'metadata' => json_encode(['role' => 'editor']), 'created_at' => now()]);

        return back()->with('status', 'Editor proyek ditambahkan.');
    }

    public function removeMember(Project $project, User $member): RedirectResponse
    {
        Gate::authorize('manageMembers', $project);
        abort_if($project->archived_at !== null, 409, 'Anggota tidak dapat diubah pada proyek yang diarsipkan.');
        abort_unless($project->members()->whereKey($member->id)->exists(), 404);

        $project->members()->detach($member->id);
        DB::table('audit_logs')->insert(['actor_id' => Auth::id(), 'project_id' => $project->id, 'action' => 'project.member_removed', 'target_type' => 'user', 'target_id' => $member->id, 'metadata' => null, 'created_at' => now()]);

        return back()->with('status', 'Editor proyek dihapus.');
    }
}
