<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentCatalogService;
use App\Support\StudentApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, StudentCatalogService $catalog): JsonResponse
    {
        $user = $request->user();
        $courses = $catalog->coursesWithProgress($user);
        $resume = $courses
            ->map(fn ($course) => $course->resume_chapter
                ? [
                    'course' => StudentApi::courseCard($course),
                    'chapter' => [
                        'id' => $course->resume_chapter->id,
                        'title' => $course->resume_chapter->title,
                        'position' => $course->resume_chapter->position,
                    ],
                ]
                : null)
            ->filter()
            ->first();

        return response()->json([
            'user' => StudentApi::user($user),
            'courses' => $courses->map(fn ($course) => StudentApi::courseCard($course))->values(),
            'attempt_count' => $catalog->quizAttemptCount($user),
            'chapters_done' => $courses->sum('progress_done'),
            'chapters_total' => $courses->sum('progress_total'),
            'resume' => $resume,
        ]);
    }
}
