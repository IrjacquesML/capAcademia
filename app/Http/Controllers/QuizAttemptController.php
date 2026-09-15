<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitQuizRequest;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\ChapterUnlockService;
use App\Services\QuizGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizAttemptController extends Controller
{
    public function store(
        SubmitQuizRequest $request,
        Course $course,
        Chapter $chapter,
        Quiz $quiz,
        QuizGradingService $grading,
        ChapterUnlockService $unlock,
    ): RedirectResponse {
        if (! $unlock->isUnlocked($request->user(), $chapter)) {
            return redirect()
                ->route('courses.show', $course)
                ->with('error', 'Chapitre verrouillé : soumettez d’abord l’interrogation du chapitre précédent.');
        }

        $attempt = $grading->grade(
            quiz: $quiz,
            user: $request->user(),
            submittedAnswers: $request->validated('answers'),
        );

        return redirect()
            ->route('quizzes.result', [$course, $chapter, $quiz, $attempt])
            ->with('status', $attempt->passed
                ? "Interrogation réussie : {$attempt->percentage} %."
                : "Score insuffisant : {$attempt->percentage} % (minimum requis : {$quiz->passing_score} %).");
    }

    public function show(
        Request $request,
        Course $course,
        Chapter $chapter,
        Quiz $quiz,
        QuizAttempt $attempt,
        ChapterUnlockService $unlock,
    ): View|RedirectResponse {
        abort_unless($request->user()->can('view', $course), 404);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);
        abort_unless((int) $quiz->chapter_id === (int) $chapter->id, 404);
        abort_unless((int) $attempt->quiz_id === (int) $quiz->id, 404);

        // Un étudiant ne consulte que SON corrigé.
        abort_unless(
            $request->user()->isPrivileged()
            || (int) $attempt->user_id === (int) $request->user()->id,
            404,
        );

        if (! $unlock->isUnlocked($request->user(), $chapter)) {
            return redirect()
                ->route('courses.show', $course)
                ->with('error', 'Chapitre verrouillé : soumettez d’abord l’interrogation du chapitre précédent.');
        }

        $attempt->load('quiz');

        $nextChapter = $unlock->nextPublished($chapter);
        $nextUnlocked = $nextChapter !== null
            && $unlock->isUnlocked($request->user(), $nextChapter);

        return view('quizzes.result', compact('course', 'chapter', 'quiz', 'attempt', 'nextChapter', 'nextUnlocked'));
    }
}
