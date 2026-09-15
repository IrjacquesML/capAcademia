<?php

namespace App\Http\Controllers;

use App\Services\StudentCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, StudentCatalogService $catalog): View
    {
        $user = $request->user();
        $user->loadMissing(['faculty:id,name', 'option:id,name', 'promotion:id,name']);

        $courses = $catalog->coursesWithProgress($user);
        $attemptCount = $catalog->quizAttemptCount($user);
        $chaptersDone = $courses->sum('progress_done');
        $chaptersTotal = $courses->sum('progress_total');
        $resume = $courses
            ->map(fn ($course) => $course->resume_chapter
                ? ['course' => $course, 'chapter' => $course->resume_chapter]
                : null)
            ->filter()
            ->first();

        return view('dashboard', compact(
            'user',
            'courses',
            'attemptCount',
            'chaptersDone',
            'chaptersTotal',
            'resume',
        ));
    }
}
