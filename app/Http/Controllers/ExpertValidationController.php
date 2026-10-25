<?php

namespace App\Http\Controllers;

use App\Models\ExpertInvitation;
use App\Models\InstrumentBlueprint;
use App\Models\Project;
use App\Services\LitSynth\ContentValidityCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpertValidationController extends Controller
{
    public function manage(Request $request, Project $project, InstrumentBlueprint $blueprint, ContentValidityCalculator $calculator): View
    {
        $this->authorizeBlueprint($project, $blueprint);
        $versions = $blueprint->versions()->with(['invitations.response.ratings'])->orderByDesc('version_number')->get();
        abort_if($versions->isEmpty(), 404);
        $requested = $request->integer('version', (int) $blueprint->current_version);
        $version = $versions->firstWhere('version_number', $requested) ?? $versions->first();
        $statistics = $calculator->calculate($version);
        $invitations = $version->invitations()->with('response.ratings')->latest('created_at')->get();

        return view('litsynth.validation-manage', compact('project', 'blueprint', 'versions', 'version', 'statistics', 'invitations'));
    }

    public function createInvitation(Request $request, Project $project, InstrumentBlueprint $blueprint, int $version): RedirectResponse
    {
        $this->authorizeBlueprint($project, $blueprint, true);
        $data = $request->validate(['expert_email' => ['required', 'email', 'max:255'], 'valid_days' => ['required', 'integer', 'min:1', 'max:90']]);
        $instrumentVersion = $blueprint->versions()->where('version_number', $version)->firstOrFail();
        abort_if(count($instrumentVersion->items ?? []) === 0, 422, 'Tambahkan setidaknya satu butir instrumen sebelum mengundang pakar.');
        $token = Str::random(64);
        $invitation = $instrumentVersion->invitations()->create([
            'invited_by' => $request->user()->id,
            'expert_email' => mb_strtolower($data['expert_email']),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays($data['valid_days']),
        ]);
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id, 'project_id' => $project->id,
            'action' => 'litsynth.expert_invited', 'target_type' => 'expert_invitation', 'target_id' => $invitation->id,
            'metadata' => json_encode(['blueprint_id' => $blueprint->id, 'version' => $version]), 'created_at' => now(),
        ]);

        return redirect()->route('litsynth.validation.manage', [$project, $blueprint, 'version' => $version])
            ->with('status', 'Undangan dibuat. Salin tautan berikut dan kirimkan kepada pakar secara aman.')
            ->with('expert_invitation_url', route('expert-validation.show', $token));
    }

    public function revokeInvitation(Request $request, Project $project, InstrumentBlueprint $blueprint, ExpertInvitation $invitation): RedirectResponse
    {
        $this->authorizeBlueprint($project, $blueprint, true);
        abort_unless($invitation->version?->instrument_blueprint_id === $blueprint->id, 404);
        abort_if($invitation->completed_at !== null, 409, 'Respons yang sudah dikirim tidak dapat dicabut.');
        $invitation->update(['revoked_at' => now()]);
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id, 'project_id' => $project->id,
            'action' => 'litsynth.expert_invitation_revoked', 'target_type' => 'expert_invitation', 'target_id' => $invitation->id,
            'metadata' => json_encode(['blueprint_id' => $blueprint->id, 'version' => $invitation->version->version_number]), 'created_at' => now(),
        ]);

        return back()->with('status', 'Tautan undangan dicabut.');
    }

    public function showExpertForm(string $token): Response
    {
        $invitation = $this->findValidInvitation($token);
        $invitation->load('version.blueprint');

        return response()->view('litsynth.expert-form', compact('invitation'))
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function submitExpertResponse(Request $request, string $token): Response
    {
        $invitation = $this->findValidInvitation($token);
        $version = $invitation->version;
        $items = collect($version->items ?? [])->keyBy('key');
        $data = $request->validate([
            'ratings' => ['required', 'array', 'size:'.$items->count()],
            'ratings.*.relevance' => ['required', 'integer', 'between:1,4'],
            'ratings.*.clarity' => ['required', 'integer', 'between:1,4'],
            'ratings.*.comment' => ['nullable', 'string', 'max:5000'],
            'overall_comment' => ['nullable', 'string', 'max:12000'],
            'consent' => ['accepted'],
        ]);
        if ($items->isEmpty() || array_diff(array_keys($data['ratings']), $items->keys()->all()) || array_diff($items->keys()->all(), array_keys($data['ratings']))) {
            throw ValidationException::withMessages(['ratings' => 'Pastikan setiap butir versi instrumen ini dinilai tepat satu kali.']);
        }
        foreach ($data['ratings'] as $rating) {
            if ((int) $rating['relevance'] < 1 || (int) $rating['relevance'] > 4 || (int) $rating['clarity'] < 1 || (int) $rating['clarity'] > 4) {
                throw ValidationException::withMessages(['ratings' => 'Penilaian harus berada pada skala 1–4.']);
            }
        }

        DB::transaction(function () use ($invitation, $data) {
            $response = $invitation->response()->create(['overall_comment' => $data['overall_comment'] ?? null, 'consented_at' => now(), 'submitted_at' => now()]);
            foreach ($data['ratings'] as $key => $rating) {
                $response->ratings()->create([
                    'item_key' => $key,
                    'relevance_rating' => (int) $rating['relevance'],
                    'clarity_rating' => (int) $rating['clarity'],
                    'comment' => $rating['comment'] ?? null,
                ]);
            }
            $invitation->update(['completed_at' => now()]);
            DB::table('audit_logs')->insert([
                'actor_id' => null,
                'project_id' => $invitation->version->blueprint->project_id,
                'action' => 'litsynth.expert_response_submitted',
                'target_type' => 'expert_invitation',
                'target_id' => $invitation->id,
                'metadata' => json_encode(['version' => $invitation->version->version_number, 'rating_count' => count($data['ratings'])]),
                'created_at' => now(),
            ]);
        });

        return response()->view('litsynth.expert-complete')
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function findValidInvitation(string $token): ExpertInvitation
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token) === 1, 404);
        $invitation = ExpertInvitation::query()->with(['version.blueprint'])->where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_if($invitation->revoked_at !== null || $invitation->expires_at->isPast(), 410, 'Tautan undangan telah berakhir atau dicabut.');
        abort_if($invitation->completed_at !== null, 410, 'Respons untuk undangan ini sudah dikirim.');

        return $invitation;
    }

    private function authorizeBlueprint(Project $project, InstrumentBlueprint $blueprint, bool $write = false): void
    {
        abort_unless(request()->user()->isStaff() && ($project->isOwnedBy(request()->user()) || $project->hasEditor(request()->user())), 403);
        abort_unless($blueprint->project_id === $project->id, 404);
        if ($write) {
            abort_if($project->archived_at !== null, 409, 'Proyek yang diarsipkan hanya dapat dibaca.');
        }
    }
}
