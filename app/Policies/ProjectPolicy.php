<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isStaff() && ($project->isOwnedBy($user) || $project->hasEditor($user));
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isStaff() && $project->isOwnedBy($user);
    }

    public function archive(User $user, Project $project): bool
    {
        return $user->isStaff() && $project->isOwnedBy($user);
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $user->isStaff() && $project->isOwnedBy($user);
    }

    public function manageAssignments(User $user, Project $project): bool
    {
        return $user->isStaff() && ($project->isOwnedBy($user) || $project->hasEditor($user)) && $project->archived_at === null;
    }
}
