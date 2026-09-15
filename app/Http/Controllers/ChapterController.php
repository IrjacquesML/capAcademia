<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Course;
use App\Services\AuditLogger;
use App\Services\ChapterUnlockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function __construct(
        private readonly ChapterUnlockService $unlock,
        private readonly AuditLogger $audit,
    ) {}

    public function show(Request $request, Course $course, Chapter $chapter): View|RedirectResponse
    {
        abort_unless($request->user()->can('view', $course), 404);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);
        abort_if($request->user()->isStudent() && ! $chapter->is_published, 404);

        if ($redirect = $this->guardLockedChapter($request, $course, $chapter)) {
            return $redirect;
        }

        $chapter->load(['quiz.questions.answers']);

        $progress = $chapter->progressFor($request->user());

        $latestAttempt = $chapter->quiz
            ? $chapter->quiz->attempts()
                ->where('user_id', $request->user()->id)
                ->latest('submitted_at')
                ->first()
            : null;

        $nextChapter = $this->unlock->nextPublished($chapter);
        $nextUnlocked = $nextChapter !== null
            && $this->unlock->isUnlocked($request->user(), $nextChapter);

        return view('chapters.show', compact(
            'course',
            'chapter',
            'progress',
            'latestAttempt',
            'nextChapter',
            'nextUnlocked',
        ));
    }

    public function markAsRead(Request $request, Course $course, Chapter $chapter): RedirectResponse
    {
        abort_unless($request->user()->can('view', $course), 404);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);
        abort_if($request->user()->isStudent() && ! $chapter->is_published, 404);

        if ($redirect = $this->guardLockedChapter($request, $course, $chapter)) {
            return $redirect;
        }

        $progress = ChapterProgress::query()->firstOrNew([
            'user_id' => $request->user()->id,
            'chapter_id' => $chapter->id,
        ]);
        $progress->read_at ??= now();
        $progress->save();
        $progress->setRelation('chapter', $chapter);
        $progress->syncCompletion();

        $this->audit->record($request->user(), AuditAction::ChapterRead, [
            'chapter_id' => $chapter->id,
            'chapter_title' => $chapter->title,
            'course_title' => $course->title,
        ]);

        return back()->with('status', 'Chapitre marqué comme lu.');
    }

    private function guardLockedChapter(Request $request, Course $course, Chapter $chapter): ?RedirectResponse
    {
        if ($this->unlock->isUnlocked($request->user(), $chapter)) {
            return null;
        }

        return redirect()
            ->route('courses.show', $course)
            ->with('error', 'Chapitre verrouillé : soumettez d’abord l’interrogation du chapitre précédent.');
    }
}
