<?php

namespace App\Support;

use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

class StudentApi
{
    /**
     * @return array<string, mixed>
     */
    public static function user(User $user): array
    {
        $user->loadMissing(['faculty:id,name', 'option:id,name', 'promotion:id,name']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'role_label' => $user->roleLabel(),
            'faculty' => $user->faculty ? ['id' => $user->faculty->id, 'name' => $user->faculty->name] : null,
            'option' => $user->option ? ['id' => $user->option->id, 'name' => $user->option->name] : null,
            'promotion' => $user->promotion ? ['id' => $user->promotion->id, 'name' => $user->promotion->name] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function courseCard(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'faculty' => $course->faculty?->name,
            'option' => $course->option?->name,
            'promotion' => $course->promotion?->name,
            'progress_done' => (int) ($course->progress_done ?? 0),
            'progress_total' => (int) ($course->progress_total ?? $course->published_chapters_count ?? 0),
            'progress_percent' => (int) ($course->progress_percent ?? 0),
            'resume_chapter' => $course->resume_chapter
                ? [
                    'id' => $course->resume_chapter->id,
                    'title' => $course->resume_chapter->title,
                    'position' => $course->resume_chapter->position,
                ]
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function chapterSummary(Chapter $chapter): array
    {
        $progress = $chapter->progress->first();
        $attempts = $chapter->quiz?->attempts;

        return [
            'id' => $chapter->id,
            'title' => $chapter->title,
            'position' => $chapter->position,
            'is_unlocked' => (bool) $chapter->is_unlocked,
            'has_quiz' => $chapter->quiz !== null,
            'quiz_id' => $chapter->quiz?->id,
            'read' => $progress?->read_at !== null,
            'completed' => $progress?->completed_at !== null,
            'quiz_submitted' => $attempts !== null && $attempts->isNotEmpty(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function chapterDetail(Chapter $chapter, ?ChapterProgress $progress, ?QuizAttempt $latestAttempt): array
    {
        $quiz = $chapter->quiz;

        return [
            'id' => $chapter->id,
            'title' => $chapter->title,
            'position' => $chapter->position,
            'content' => $chapter->content,
            'read' => $progress?->read_at !== null,
            'completed' => $progress?->completed_at !== null,
            'latest_attempt' => $latestAttempt ? self::attempt($latestAttempt) : null,
            'quiz' => $quiz ? self::quizForStudent($quiz) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function quizForStudent(Quiz $quiz): array
    {
        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'passing_score' => $quiz->passing_score,
            'questions' => $quiz->questions->map(fn ($question): array => [
                'id' => $question->id,
                'type' => $question->type->value,
                'prompt' => $question->prompt,
                'points' => $question->points,
                'answers' => $question->answers->map(fn ($answer): array => [
                    'id' => $answer->id,
                    'label' => $answer->label,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function attempt(QuizAttempt $attempt): array
    {
        return [
            'id' => $attempt->id,
            'score' => $attempt->score,
            'max_score' => $attempt->max_score,
            'percentage' => (float) $attempt->percentage,
            'passed' => $attempt->passed,
            'breakdown' => $attempt->breakdown ?? [],
            'submitted_at' => $attempt->submitted_at?->toIso8601String(),
        ];
    }
}
