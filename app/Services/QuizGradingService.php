<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\QuestionType;
use App\Models\ChapterProgress;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizGradingService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * Corrige côté serveur uniquement. Le score envoyé par le client est ignoré.
     *
     * @param  array<int|string, mixed>  $submittedAnswers
     */
    public function grade(Quiz $quiz, User $user, array $submittedAnswers): QuizAttempt
    {
        $quiz->load(['questions.answers', 'chapter']);

        $earned = 0;
        $max = 0;
        $breakdown = [];

        foreach ($quiz->questions as $question) {
            $max += $question->points;

            // On ne note que les questions de CE quiz : les IDs injectés par le client sont ignorés.
            $given = $submittedAnswers[$question->id] ?? $submittedAnswers[(string) $question->id] ?? null;
            $isCorrect = $this->isCorrect($question, $given);

            if ($isCorrect) {
                $earned += $question->points;
            }

            $breakdown[] = [
                'question_id' => $question->id,
                'prompt' => $question->prompt,
                'type' => $question->type->value,
                'points' => $question->points,
                'earned' => $isCorrect ? $question->points : 0,
                'correct' => $isCorrect,
                'student_answer' => $this->studentAnswerLabel($question, $given),
                'expected_answers' => $this->expectedAnswerLabels($question),
            ];
        }

        $percentage = $max > 0 ? round(($earned / $max) * 100, 2) : 0.0;
        $passed = $percentage >= $quiz->passing_score;

        $attempt = DB::transaction(function () use ($quiz, $user, $earned, $max, $percentage, $passed, $breakdown): QuizAttempt {
            $attempt = QuizAttempt::query()->create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => $earned,
                'max_score' => $max,
                'percentage' => $percentage,
                'passed' => $passed,
                'breakdown' => $breakdown,
                'submitted_at' => now(),
            ]);

            if ($passed) {
                $progress = ChapterProgress::query()->firstOrNew([
                    'user_id' => $user->id,
                    'chapter_id' => $quiz->chapter_id,
                ]);
                $progress->quiz_passed_at ??= now();
                $progress->save();
                $progress->syncCompletion();
            }

            return $attempt;
        });

        $quiz->loadMissing('chapter.course:id,title');

        $this->audit->record($user, AuditAction::QuizSubmitted, [
            'quiz_id' => $quiz->id,
            'quiz_title' => $quiz->title,
            'chapter_title' => $quiz->chapter?->title,
            'course_title' => $quiz->chapter?->course?->title,
            'percentage' => $percentage,
            'passed' => $passed,
        ], $user);

        return $attempt;
    }

    private function isCorrect(Question $question, mixed $given): bool
    {
        if ($given === null || $given === '') {
            return false;
        }

        return match ($question->type) {
            QuestionType::MultipleChoice => $this->gradeMultipleChoice($question, $given),
            QuestionType::Text => $this->gradeText($question, $given),
        };
    }

    /**
     * Instantané pédagogique : ce que l'étudiant a répondu, figé au moment de la correction.
     */
    private function studentAnswerLabel(Question $question, mixed $given): string
    {
        if ($given === null || $given === '') {
            return 'Non répondu';
        }

        if ($question->type === QuestionType::Text) {
            return is_scalar($given) ? trim((string) $given) : 'Non répondu';
        }

        $labels = $question->answers
            ->whereIn('id', collect(Arr::wrap($given))->map(fn (mixed $id): int => (int) $id))
            ->pluck('label')
            ->filter()
            ->values();

        return $labels->isEmpty() ? 'Réponse invalide' : $labels->implode(', ');
    }

    /**
     * @return list<string>
     */
    private function expectedAnswerLabels(Question $question): array
    {
        return $question->answers
            ->where('is_correct', true)
            ->pluck('label')
            ->map(fn (mixed $label): string => (string) $label)
            ->values()
            ->all();
    }

    private function gradeMultipleChoice(Question $question, mixed $given): bool
    {
        $correctIds = $question->answers
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->sort()
            ->values();

        $givenIds = collect(Arr::wrap($given))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->sort()
            ->values();

        return $correctIds->isNotEmpty() && $correctIds->all() === $givenIds->all();
    }

    private function gradeText(Question $question, mixed $given): bool
    {
        if (! is_string($given) && ! is_numeric($given)) {
            return false;
        }

        $normalized = $this->normalize((string) $given);

        return $question->answers
            ->where('is_correct', true)
            ->contains(fn ($answer): bool => $this->normalize($answer->label) === $normalized);
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->squish()->lower()->ascii()->toString();
    }
}
