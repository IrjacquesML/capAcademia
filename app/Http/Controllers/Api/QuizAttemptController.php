<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitQuizRequest;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\ChapterUnlockService;
use App\Services\QuizGradingService;
use App\Support\StudentApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizAttemptController extends Controller
{
    public function store(
        SubmitQuizRequest $request,
        Course $course,
        Chapter $chapter,
        Quiz $quiz,
        QuizGradingService $grading,
        ChapterUnlockService $unlock,
    ): JsonResponse {
        $attempt = $grading->grade(
            quiz: $quiz,
            user: $request->user(),
            submittedAnswers: $request->validated('answers'),
        );

        $nextChapter = $unlock->nextPublished($chapter);
        $nextUnlocked = $nextChapter !== null
            && $unlock->isUnlocked($request->user(), $nextChapter);

        return response()->json([
            'status' => $attempt->passed
                ? "Interrogation réussie : {$attempt->percentage} %."
                : "Score insuffisant : {$attempt->percentage} % (minimum requis : {$quiz->passing_score} %).",
            'attempt' => StudentApi::attempt($attempt),
            'next_chapter' => $nextChapter && $nextUnlocked
                ? ['id' => $nextChapter->id, 'title' => $nextChapter->title, 'position' => $nextChapter->position]
                : null,
        ]);
    }

    public function show(
        Request $request,
        Course $course,
        Chapter $chapter,
        Quiz $quiz,
        QuizAttempt $attempt,
        ChapterUnlockService $unlock,
    ): JsonResponse {
        abort_unless($request->user()->can('view', $course), 404);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);
        abort_unless((int) $quiz->chapter_id === (int) $chapter->id, 404);
        abort_unless((int) $attempt->quiz_id === (int) $quiz->id, 404);
        abort_unless(
            $request->user()->isPrivileged()
            || (int) $attempt->user_id === (int) $request->user()->id,
            404,
        );
        abort_unless(
            $unlock->isUnlocked($request->user(), $chapter),
            403,
            'Chapitre verrouillé : soumettez d’abord l’interrogation du chapitre précédent.',
        );

        $nextChapter = $unlock->nextPublished($chapter);
        $nextUnlocked = $nextChapter !== null
            && $unlock->isUnlocked($request->user(), $nextChapter);

        return response()->json([
            'course' => ['id' => $course->id, 'title' => $course->title],
            'chapter' => ['id' => $chapter->id, 'title' => $chapter->title],
            'quiz' => ['id' => $quiz->id, 'title' => $quiz->title, 'passing_score' => $quiz->passing_score],
            'attempt' => StudentApi::attempt($attempt),
            'next_chapter' => $nextChapter && $nextUnlocked
                ? ['id' => $nextChapter->id, 'title' => $nextChapter->title, 'position' => $nextChapter->position]
                : null,
        ]);
    }
}
