<?php

namespace App\Http\Requests;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\ChapterUnlockService;
use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course $course */
        $course = $this->route('course');
        /** @var Chapter $chapter */
        $chapter = $this->route('chapter');
        /** @var Quiz $quiz */
        $quiz = $this->route('quiz');
        $user = $this->user();

        if ($user === null || $course === null || $chapter === null || $quiz === null) {
            return false;
        }

        // Empêche de poster un quiz d'un autre chapitre / cours (IDOR).
        return (int) $quiz->chapter_id === (int) $chapter->id
            && (int) $chapter->course_id === (int) $course->id
            && $user->can('view', $course)
            && ($user->isStudent() ? $chapter->is_published : true)
            && app(ChapterUnlockService::class)->isUnlocked($user, $chapter);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable'],
        ];
    }
}
