<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\ChapterUnlockService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(private readonly ChapterUnlockService $unlock) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $this->authorize('viewAny', Course::class);

        $user->load(['faculty:id,name', 'option:id,name', 'promotion:id,name']);

        // Filtre automatique sur faculté + option + promotion de l'étudiant connecté.
        // Un Course::all() ici casserait l'isolation multi-tenant académique.
        $courses = Course::query()
            ->visibleTo($user)
            ->with(['faculty:id,name', 'option:id,name', 'promotion:id,name'])
            ->withCount(['chapters as published_chapters_count' => fn ($query) => $query->published()])
            ->orderBy('title')
            ->get();

        return view('courses.index', compact('courses', 'user'));
    }

    public function show(Request $request, Course $course): View
    {
        // 404 (et non 403) : ne pas confirmer l'existence d'un cours hors périmètre.
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

        $this->unlock->decorate($request->user(), $course->chapters);

        return view('courses.show', compact('course'));
    }
}
