<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WritingAssignment;
use App\Models\WritingSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $accessibleProjects = Project::query()->whereNull('archived_at')
            ->where(fn ($query) => $query->where('owner_id', $user->id)->orWhereHas('members', fn ($members) => $members->where('users.id', $user->id)->where('project_members.role', 'editor')));
        $projectIds = $user->isStaff() ? (clone $accessibleProjects)->select('projects.id') : collect();
        $projects = $user->isStaff()
            ? (clone $accessibleProjects)->withCount(['assignments', 'members'])->latest()->take(5)->get()
            : collect();
        $assignments = $user->isStaff()
            ? WritingAssignment::query()->whereIn('project_id', $projectIds)->with('project')->withCount(['students', 'submissions'])->latest()->take(8)->get()
            : ($user->role === 'student'
                ? WritingAssignment::query()->where('status', 'published')->whereHas('students', fn ($query) => $query->where('users.id', $user->id))->with('project')->latest()->take(10)->get()
                : collect());

        $dashboardStats = $user->isStaff() ? [
            'projects' => (clone $accessibleProjects)->count(),
            'assignments' => WritingAssignment::query()->whereIn('project_id', $projectIds)->count(),
            'submissions' => WritingSubmission::query()->whereHas('assignment', fn ($query) => $query->whereIn('project_id', $projectIds))->count(),
        ] : ($user->role === 'student' ? [
            'assignments' => WritingAssignment::query()->where('status', 'published')->whereHas('students', fn ($query) => $query->where('users.id', $user->id))->count(),
            'submissions' => WritingSubmission::query()->where('student_id', $user->id)->count(),
        ] : []);

        return view('dashboard', compact('projects', 'assignments', 'dashboardStats'));
    }
}
