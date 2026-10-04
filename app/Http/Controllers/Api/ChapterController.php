<?php

namespace App\Http\Controllers\Api;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Course;
use App\Services\AuditLogger;
use App\Services\ChapterUnlockService;
use App\Support\StudentApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function __construct(
        private readonly ChapterUnlockService $unlock,
        private readonly AuditLogger $audit,
    ) {}

    public function show(Request $request, Course $course, Chapter $chapter): JsonResponse
    {
        $this->assertAccessible($request, $course, $chapter);

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

        return response()->json([
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
            ],
            'chapter' => StudentApi::chapterDetail($chapter, $progress, $latestAttempt),
            'next_chapter' => $nextChapter && $nextUnlocked
                ? ['id' => $nextChapter->id, 'title' => $nextChapter->title, 'position' => $nextChapter->position]
                : null,
        ]);
    }

    public function markAsRead(Request $request, Course $course, Chapter $chapter): JsonResponse
    {
        $this->assertAccessible($request, $course, $chapter);

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

        return response()->json([
            'status' => 'Chapitre marqué comme lu.',
            'read' => true,
            'completed' => $progress->completed_at !== null,
        ]);
    }

    private function assertAccessible(Request $request, Course $course, Chapter $chapter): void
    {
        abort_unless($request->user()->can('view', $course), 404);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);
        abort_if($request->user()->isStudent() && ! $chapter->is_published, 404);
        abort_unless(
            $this->unlock->isUnlocked($request->user(), $chapter),
            403,
            'Chapitre verrouillé : soumettez d’abord l’interrogation du chapitre précédent.',
        );
    }
}
