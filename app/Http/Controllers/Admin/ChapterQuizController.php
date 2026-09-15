<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveChapterQuizRequest;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\ChapterQuizWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChapterQuizController extends Controller
{
    public function edit(Request $request, Course $course, Chapter $chapter): View
    {
        $this->authorize('update', $course);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);

        $chapter->load('quiz.questions.answers');

        return view('admin.quizzes.form', [
            'course' => $course,
            'chapter' => $chapter,
            'quiz' => $chapter->quiz,
            'questions' => old('questions', $this->questionsForForm($chapter->quiz)),
        ]);
    }

    public function update(SaveChapterQuizRequest $request, Course $course, Chapter $chapter, ChapterQuizWriter $writer): RedirectResponse
    {
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);

        $writer->save($chapter, $request->payload());

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('status', 'Interrogation enregistrée pour « '.$chapter->title.' ».');
    }

    public function destroy(Request $request, Course $course, Chapter $chapter, ChapterQuizWriter $writer): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);

        $writer->delete($chapter);

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('status', 'Interrogation supprimée.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function questionsForForm(?Quiz $quiz): array
    {
        if ($quiz === null || $quiz->questions->isEmpty()) {
            return [$this->blankQuestion()];
        }

        return $quiz->questions->map(function ($question): array {
            if ($question->type === QuestionType::Text) {
                return [
                    'prompt' => $question->prompt,
                    'type' => QuestionType::Text->value,
                    'points' => $question->points,
                    'choices' => ['', '', '', ''],
                    'correct' => '0',
                    'accepted_text' => $question->answers->pluck('label')->implode("\n"),
                ];
            }

            $labels = $question->answers->pluck('label')->values()->all();
            $correctIndex = $question->answers->values()->search(fn ($answer): bool => (bool) $answer->is_correct);
            while (count($labels) < 4) {
                $labels[] = '';
            }

            return [
                'prompt' => $question->prompt,
                'type' => QuestionType::MultipleChoice->value,
                'points' => $question->points,
                'choices' => $labels,
                'correct' => $correctIndex === false ? '0' : (string) $correctIndex,
                'accepted_text' => '',
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function blankQuestion(): array
    {
        return [
            'prompt' => '',
            'type' => QuestionType::MultipleChoice->value,
            'points' => 1,
            'choices' => ['', '', '', ''],
            'correct' => '0',
            'accepted_text' => '',
        ];
    }
}
