<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class ChapterUnlockService
{
    /**
     * Le premier chapitre publié est libre. Les suivants se débloquent
     * uniquement après soumission de l'interrogation du chapitre précédent.
     */
    public function isUnlocked(User $user, Chapter $chapter): bool
    {
        if (! $user->isStudent()) {
            return true;
        }

        if (! $chapter->is_published) {
            return false;
        }

        $previous = $this->previousPublished($chapter);

        if ($previous === null) {
            return true;
        }

        return $this->previousRequirementMet($user, $previous);
    }

    /**
     * @param  Collection<int, Chapter>  $chapters
     * @return Collection<int, Chapter>
     */
    public function decorate(User $user, Collection $chapters): Collection
    {
        $unlocked = true;

        foreach ($chapters as $chapter) {
            if (! $user->isStudent()) {
                $chapter->setAttribute('is_unlocked', true);

                continue;
            }

            $chapter->setAttribute('is_unlocked', $unlocked);
            $unlocked = $this->previousRequirementMet($user, $chapter);
        }

        return $chapters;
    }

    public function previousPublished(Chapter $chapter): ?Chapter
    {
        return Chapter::query()
            ->published()
            ->where('course_id', $chapter->course_id)
            ->where('position', '<', $chapter->position)
            ->orderByDesc('position')
            ->first();
    }

    public function nextPublished(Chapter $chapter): ?Chapter
    {
        return Chapter::query()
            ->published()
            ->where('course_id', $chapter->course_id)
            ->where('position', '>', $chapter->position)
            ->orderBy('position')
            ->first();
    }

    public function previousRequirementMet(User $user, Chapter $chapter): bool
    {
        $chapter->loadMissing('quiz');

        if ($chapter->quiz === null) {
            return ChapterProgress::query()
                ->where('user_id', $user->id)
                ->where('chapter_id', $chapter->id)
                ->whereNotNull('read_at')
                ->exists();
        }

        if ($chapter->relationLoaded('quiz') && $chapter->quiz->relationLoaded('attempts')) {
            return $chapter->quiz->attempts->isNotEmpty();
        }

        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $chapter->quiz->id)
            ->exists();
    }
}
