<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $courses = Course::query()->visibleTo($user);

        $stats = [
            'faculties' => Faculty::query()->count(),
            'users' => User::query()
                ->when($user->isAdmin() && $user->faculty_id, fn ($query) => $query->where('faculty_id', $user->faculty_id))
                ->when($user->isAdmin(), fn ($query) => $query->where('role', '!=', 'super_admin'))
                ->count(),
            'courses' => (clone $courses)->count(),
            'published' => (clone $courses)->where('is_published', true)->count(),
        ];

        $latestCourses = Course::query()
            ->visibleTo($user)
            ->with(['faculty:id,name', 'option:id,name', 'promotion:id,name'])
            ->latest()
            ->limit(6)
            ->get();

        $latestActivity = AuditEvent::query()
            ->with('user:id,name,role')
            ->whereIn('action', [AuditAction::ChapterRead->value, AuditAction::QuizSubmitted->value])
            ->when(
                ! $user->isSuperAdmin(),
                fn ($query) => $query->whereHas('user', fn ($users) => $users->manageableBy($user)),
            )
            ->latest()
            ->limit(12)
            ->get();

        return view('admin.dashboard', compact('user', 'stats', 'latestCourses', 'latestActivity'));
    }
}
