<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcademicHubPilotTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_cannot_assign_privileged_role(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'A Student',
            'email' => 'student@example.test',
            'role' => 'lecturer',
            'password' => 'a-secure-password',
            'password_confirmation' => 'a-secure-password',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'student@example.test']);
    }

    public function test_public_registration_creates_student_after_role_migration(): void
    {
        $this->post(route('register'), [
            'name' => 'Ramli',
            'email' => 'ramli@example.test',
            'role' => 'student',
            'password' => 'a-secure-password',
            'password_confirmation' => 'a-secure-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'ramli@example.test', 'role' => 'student']);
    }

    public function test_only_staff_can_create_projects(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->post(route('projects.store'), [
            'name' => 'Restricted',
            'pillars' => ['teaching'],
        ])->assertForbidden();
        $this->assertDatabaseCount('projects', 0);

        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $this->actingAs($lecturer)->post(route('projects.store'), [
            'name' => 'Writing Pilot',
            'description' => 'Pilot project',
            'pillars' => ['teaching'],
        ])->assertRedirect();
        $this->assertDatabaseHas('projects', ['name' => 'Writing Pilot', 'owner_id' => $lecturer->id]);
    }

    public function test_project_data_is_visible_only_to_the_owner_or_staff_member(): void
    {
        $owner = User::factory()->create(['role' => 'lecturer']);
        $member = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $public = User::factory()->create(['role' => 'public']);
        $otherLecturer = User::factory()->create(['role' => 'lecturer']);
        $project = Project::create(['owner_id' => $owner->id, 'name' => 'Private project', 'pillars' => ['teaching'], 'status' => 'active']);
        $project->members()->attach($member->id, ['role' => 'editor']);
        $project->members()->attach($student->id, ['role' => 'editor']);

        $this->actingAs($owner)->get(route('projects.show', $project))->assertOk();
        $this->actingAs($member)->get(route('projects.show', $project))->assertOk();
        $this->actingAs($student)->get(route('projects.show', $project))->assertForbidden();
        $this->actingAs($public)->get(route('projects.show', $project))->assertForbidden();
        $this->actingAs($otherLecturer)->get(route('projects.show', $project))->assertForbidden();
    }

    public function test_public_users_cannot_create_assignments_or_submissions(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $public = User::factory()->create(['role' => 'public']);
        $project = Project::create(['owner_id' => $lecturer->id, 'name' => 'Pilot', 'pillars' => ['teaching'], 'status' => 'active']);

        $this->actingAs($public)->post(route('projects.assignments.store', $project), [
            'title' => 'Restricted task',
            'instructions' => 'This assignment must be created by the project owner.',
            'feedback_language' => 'en',
            'feedback_level' => 'indirect',
            'status' => 'published',
        ])->assertForbidden();
        $this->assertDatabaseCount('writing_assignments', 0);
    }

    public function test_project_owner_manages_staff_editors_and_only_owner_controls_project_lifecycle(): void
    {
        $owner = User::factory()->create(['role' => 'lecturer']);
        $editor = User::factory()->create(['role' => 'lecturer']);
        $student = User::factory()->create(['role' => 'student']);
        $otherOwner = User::factory()->create(['role' => 'lecturer']);
        $project = Project::create(['owner_id' => $owner->id, 'name' => 'Shared project', 'pillars' => ['research'], 'status' => 'active']);

        $this->actingAs($owner)->post(route('projects.members.store', $project), ['user_id' => $editor->id])->assertSessionHas('status');
        $this->assertDatabaseHas('project_members', ['project_id' => $project->id, 'user_id' => $editor->id, 'role' => 'editor']);
        $this->actingAs($owner)->post(route('projects.members.store', $project), ['user_id' => $student->id])->assertSessionHasErrors('user_id');
        $this->actingAs($editor)->get(route('projects.show', $project))->assertOk();
        $this->actingAs($editor)->put(route('projects.update', $project), ['name' => 'Hijack', 'pillars' => ['teaching']])->assertForbidden();
        $this->actingAs($editor)->post(route('projects.archive', $project))->assertForbidden();
        $this->actingAs($otherOwner)->delete(route('projects.members.destroy', [$project, $editor]))->assertForbidden();

        $this->actingAs($owner)->delete(route('projects.members.destroy', [$project, $editor]))->assertSessionHas('status');
        $this->assertDatabaseMissing('project_members', ['project_id' => $project->id, 'user_id' => $editor->id]);
        $this->actingAs($owner)->post(route('projects.archive', $project))->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'archived']);
        $this->actingAs($owner)->put(route('projects.update', $project), ['name' => 'Changed', 'pillars' => ['teaching']])->assertStatus(409);
    }

    public function test_staff_editor_projects_are_listed_but_archived_projects_are_hidden(): void
    {
        $owner = User::factory()->create(['role' => 'lecturer']);
        $editor = User::factory()->create(['role' => 'admin']);
        $active = Project::create(['owner_id' => $owner->id, 'name' => 'Active shared', 'pillars' => ['teaching'], 'status' => 'active']);
        $active->members()->attach($editor->id, ['role' => 'editor']);
        $archived = Project::create(['owner_id' => $owner->id, 'name' => 'Archived shared', 'pillars' => ['research'], 'status' => 'archived', 'archived_at' => now()]);
        $archived->members()->attach($editor->id, ['role' => 'editor']);

        $this->actingAs($editor)->get(route('projects.index'))->assertOk()->assertSee('Active shared')->assertDontSee('Archived shared');
        $this->actingAs($editor)->get(route('projects.show', $archived))->assertOk()->assertSee('diarsipkan')->assertDontSee('Buat tugas WriteWise');
    }

    public function test_trusted_console_role_assignment_updates_only_existing_accounts(): void
    {
        $user = User::factory()->create(['role' => 'public']);

        $this->artisan('hub:user-role', ['email' => $user->email, 'role' => 'superadmin'])->assertSuccessful();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'superadmin']);
        $this->assertTrue($user->fresh()->isStaff());
        $this->artisan('hub:user-role', ['email' => 'missing@example.test', 'role' => 'admin'])->assertFailed();
        $this->artisan('hub:user-role', ['email' => $user->email, 'role' => 'owner'])->assertExitCode(2);
    }

    public function test_superadmin_account_can_be_created_from_trusted_console(): void
    {
        $this->artisan('hub:superadmin:create', ['email' => 'root@example.test', 'name' => 'Root Operator'])
            ->expectsQuestion('Masukkan kata sandi superadmin (min. 12 karakter)', 'A-very-secure-password-2026')
            ->expectsQuestion('Ulangi kata sandi', 'A-very-secure-password-2026')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'root@example.test', 'role' => 'superadmin']);
        $this->assertTrue(User::query()->where('email', 'root@example.test')->firstOrFail()->isStaff());
    }

    public function test_student_can_access_only_assigned_writing_and_feedback_after_publication(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $project = Project::create(['owner_id' => $lecturer->id, 'name' => 'Pilot', 'pillars' => ['teaching'], 'status' => 'active']);

        $this->actingAs($lecturer)->post(route('projects.assignments.store', $project), [
            'title' => 'Short reflection',
            'instructions' => 'Write a short reflection about learning English.',
            'feedback_language' => 'en',
            'feedback_level' => 'indirect',
            'status' => 'published',
            'student_emails' => $student->email,
        ])->assertRedirect();
        $assignmentId = (int) DB::table('writing_assignments')->value('id');

        $this->actingAs($otherStudent)->get(route('assignments.show', $assignmentId))->assertForbidden();
        $this->actingAs($student)->get(route('assignments.show', $assignmentId))->assertOk();
        $this->actingAs($student)->post(route('assignments.revisions.store', $assignmentId), [
            'body_text' => 'This is my first reflection and I am practicing English writing.',
        ])->assertSessionHas('status');
        $this->assertDatabaseHas('writing_revisions', ['submission_id' => 1, 'revision_number' => 1]);

        $revisionId = (int) DB::table('writing_revisions')->value('id');
        $this->actingAs($lecturer)->post(route('feedback.store', $revisionId), ['overall_summary' => 'Your reflection has a clear main idea.'])->assertSessionHas('status');
        $reportId = (int) DB::table('writing_feedback_reports')->value('id');
        $this->actingAs($student)->get(route('assignments.show', $assignmentId))->assertDontSee('Your reflection has a clear main idea.');
        $this->actingAs($lecturer)->post(route('feedback.publish', $reportId))->assertSessionHas('status');
        $this->actingAs($student)->get(route('assignments.show', $assignmentId))->assertSee('Your reflection has a clear main idea.');
    }
}
