<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\StudentCatalogService;
use App\Support\StudentApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(private readonly StudentCatalogService $catalog) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('viewAny', Course::class);

        $courses = $this->catalog->coursesWithProgress($user);

        return response()->json([
            'user' => StudentApi::user($user),
            'courses' => $courses->map(fn (Course $course) => StudentApi::courseCard($course))->values(),
        ]);
    }

    public function show(Request $request, Course $course): JsonResponse
    {
        abort_unless($request->user()->can('view', $course), 404);

        $course->load([
            'faculty:id,name',
            'option:id,name',
            'promotion:id,name',
            'chapters' => function ($query) use ($request): void {
                if ($request->user()->isStudent()) {
                    $query->published();
                }
            },
            'chapters.quiz',
            'chapters.quiz.attempts' => fn ($query) => $query->where('user_id', $request->user()->id),
            'chapters.progress' => fn ($query) => $query->where('user_id', $request->user()->id),
        ]);

        $this->catalog->decorateCourseProgress($request->user(), $course);

        return response()->json([
            'course' => [
                ...StudentApi::courseCard($course),
                'chapters' => $course->chapters->map(fn ($chapter) => StudentApi::chapterSummary($chapter))->values(),
            ],
        ]);
    }
}
