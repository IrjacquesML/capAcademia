<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class ChapterQuizWriter
{
    /**
     * @param  array{
     *     title: string,
     *     description: ?string,
     *     passing_score: int,
     *     questions: list<array{
     *         prompt: string,
     *         type: QuestionType,
     *         points: int,
     *         choices?: list<array{label: string, is_correct: bool}>,
     *         accepted?: list<string>
     *     }>
     * }  $data
     */
    public function save(Chapter $chapter, array $data): Quiz
    {
        return DB::transaction(function () use ($chapter, $data): Quiz {
            $quiz = Quiz::query()->updateOrCreate(
                ['chapter_id' => $chapter->id],
                [
                    'title' => $data['title'],
                    'description' => $data['description'] ?: null,
                    'passing_score' => $data['passing_score'],
                ],
            );

            $quiz->questions()->delete();

            foreach ($data['questions'] as $index => $item) {
                $question = Question::query()->create([
                    'quiz_id' => $quiz->id,
                    'type' => $item['type'],
                    'prompt' => $item['prompt'],
                    'points' => $item['points'],
                    'position' => $index + 1,
                ]);

                if ($item['type'] === QuestionType::Text) {
                    foreach (array_values($item['accepted'] ?? []) as $answerIndex => $label) {
                        Answer::query()->create([
                            'question_id' => $question->id,
                            'label' => $label,
                            'is_correct' => true,
                            'position' => $answerIndex + 1,
                        ]);
                    }

                    continue;
                }

                foreach (array_values($item['choices'] ?? []) as $answerIndex => $choice) {
                    Answer::query()->create([
                        'question_id' => $question->id,
                        'label' => $choice['label'],
                        'is_correct' => $choice['is_correct'],
                        'position' => $answerIndex + 1,
                    ]);
                }
            }

            return $quiz->load('questions.answers');
        });
    }

    public function delete(Chapter $chapter): void
    {
        $chapter->loadMissing('quiz');
        $chapter->quiz?->delete();
    }
}
