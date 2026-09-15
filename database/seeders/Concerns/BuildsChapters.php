<?php

namespace Database\Seeders\Concerns;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;

trait BuildsChapters
{
    /**
     * @param  array{title: string, passing_score?: int, questions: list<array<string, mixed>>}  $quizData
     */
    protected function chapterWithQuiz(Course $course, int $position, string $title, string $content, array $quizData): void
    {
        $chapter = Chapter::query()->updateOrCreate(
            ['course_id' => $course->id, 'slug' => str()->slug($title)],
            [
                'title' => $title,
                'content' => trim($content),
                'position' => $position,
                'is_published' => true,
            ],
        );

        $quiz = Quiz::query()->updateOrCreate(
            ['chapter_id' => $chapter->id],
            [
                'title' => $quizData['title'],
                'description' => 'Répondez à partir du contenu du chapitre. La correction est automatique.',
                'passing_score' => $quizData['passing_score'] ?? 50,
            ],
        );

        foreach ($quizData['questions'] as $index => $questionData) {
            $type = $questionData['type'] ?? QuestionType::MultipleChoice;
            $question = Question::query()->updateOrCreate(
                ['quiz_id' => $quiz->id, 'position' => $index + 1],
                [
                    'type' => $type,
                    'prompt' => $questionData['prompt'],
                    'points' => $questionData['points'] ?? 1,
                ],
            );

            if ($type === QuestionType::Text) {
                foreach (array_values($questionData['accepted']) as $answerIndex => $label) {
                    Answer::query()->updateOrCreate(
                        ['question_id' => $question->id, 'position' => $answerIndex + 1],
                        ['label' => $label, 'is_correct' => true],
                    );
                }

                continue;
            }

            foreach ($questionData['answers'] as $answerIndex => $answer) {
                Answer::query()->updateOrCreate(
                    ['question_id' => $question->id, 'position' => $answerIndex + 1],
                    [
                        'label' => $answer[0],
                        'is_correct' => $answer[1],
                    ],
                );
            }
        }
    }

    /**
     * @param  list<array{title: string, content: string, questions: list<array<string, mixed>>}>  $lessons
     */
    protected function appendLessons(Course $course, array $lessons): void
    {
        $position = (int) $course->chapters()->max('position');

        foreach ($lessons as $lesson) {
            $position++;
            $this->chapterWithQuiz($course, $position, $lesson['title'], $lesson['content'], [
                'title' => 'Interrogation — '.$lesson['title'],
                'passing_score' => $lesson['passing_score'] ?? 50,
                'questions' => $lesson['questions'],
            ]);
        }
    }

    /**
     * @return array{title: string, content: string, questions: list<array<string, mixed>>}
     */
    protected function lesson(
        string $title,
        string $content,
        string $prompt,
        string $good,
        string $bad1,
        string $bad2,
        ?string $textPrompt = null,
        array $accepted = [],
    ): array {
        $questions = [[
            'prompt' => $prompt,
            'answers' => [
                [$good, true],
                [$bad1, false],
                [$bad2, false],
            ],
        ]];

        if ($textPrompt !== null) {
            $questions[] = [
                'type' => QuestionType::Text,
                'prompt' => $textPrompt,
                'accepted' => $accepted,
            ];
        }

        return [
            'title' => $title,
            'content' => $content,
            'questions' => $questions,
        ];
    }
}
