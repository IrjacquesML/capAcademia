<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class StudentCatalogService
{
    public function __construct(private readonly ChapterUnlockService $unlock) {}

    /**
     * Cours visibles par l'étudiant, avec progression et chapitre à reprendre.
     *
     * @return Collection<int, Course>
     */
    public function coursesWithProgress(User $user): Collection
    {
        $courses = Course::query()
            ->visibleTo($user)
            ->with([
                'faculty:id,name',
                'option:id,name',
                'promotion:id,name',
                'chapters' => function ($query) use ($user): void {
                    if ($user->isStudent()) {
                        $query->published();
                    }
                },
                'chapters.quiz',
                'chapters.quiz.attempts' => fn ($query) => $query->where('user_id', $user->id),
                'chapters.progress' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->orderBy('title')
            ->get();

        foreach ($courses as $course) {
            $this->unlock->decorate($user, $course->chapters);
            $total = $course->chapters->count();
            $done = $course->chapters->filter(
                fn (Chapter $chapter): bool => $this->unlock->previousRequirementMet($user, $chapter),
            )->count();

            $course->setAttribute('progress_total', $total);
            $course->setAttribute('progress_done', $done);
            $course->setAttribute('progress_percent', $total > 0 ? (int) round(($done / $total) * 100) : 0);
            $course->setAttribute(
                'resume_chapter',
                $course->chapters->first(
                    fn (Chapter $chapter): bool => $chapter->is_unlocked
                        && ! $this->unlock->previousRequirementMet($user, $chapter),
                ),
            );
        }

        return $courses;
    }

    public function quizAttemptCount(User $user): int
    {
        return QuizAttempt::query()->where('user_id', $user->id)->count();
    }
}
