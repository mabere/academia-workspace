<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WritingAssignment;

class WritingAssignmentPolicy
{
    public function view(User $user, WritingAssignment $assignment): bool
    {
        if ($this->manage($user, $assignment)) {
            return true;
        }

        return $user->role === 'student'
            && $assignment->status === 'published'
            && $assignment->students()->whereKey($user->id)->exists();
    }

    public function update(User $user, WritingAssignment $assignment): bool
    {
        return $this->manage($user, $assignment) && $assignment->project->archived_at === null;
    }

    public function submitRevision(User $user, WritingAssignment $assignment): bool
    {
        return $user->role === 'student'
            && $assignment->status === 'published'
            && $assignment->project->archived_at === null
            && $assignment->students()->whereKey($user->id)->exists();
    }

    public function manage(User $user, WritingAssignment $assignment): bool
    {
        return $user->isStaff() && (
            $assignment->project->isOwnedBy($user) || $assignment->project->hasEditor($user)
        );
    }
}
