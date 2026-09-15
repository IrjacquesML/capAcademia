<?php

namespace Tests\Concerns;

use App\Enums\QuestionType;
use App\Enums\UserRole;
use App\Models\Answer;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\Promotion;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

trait CreatesAcademicWorld
{
    /**
     * @return array{
     *     faculty: Faculty,
     *     option: Option,
     *     promotion: Promotion,
     *     student: User,
     *     course: Course,
     *     chapter: Chapter,
     *     quiz: Quiz,
     *     question: Question,
     *     correctAnswer: Answer
     * }
     */
    protected function createAcademicWorld(): array
    {
        $faculty = Faculty::factory()->create();
        $option = Option::factory()->create(['faculty_id' => $faculty->id]);
        $promotion = Promotion::factory()->create();

        $student = User::factory()->student()->create([
            'faculty_id' => $faculty->id,
            'option_id' => $option->id,
            'promotion_id' => $promotion->id,
        ]);

        $course = Course::factory()->create([
            'faculty_id' => $faculty->id,
            'option_id' => $option->id,
            'promotion_id' => $promotion->id,
            'is_published' => true,
        ]);

        $chapter = Chapter::factory()->create([
            'course_id' => $course->id,
            'position' => 1,
            'is_published' => true,
        ]);

        $quiz = Quiz::factory()->create([
            'chapter_id' => $chapter->id,
            'passing_score' => 50,
        ]);

        $question = Question::factory()->create([
            'quiz_id' => $quiz->id,
            'type' => QuestionType::MultipleChoice,
            'points' => 1,
            'position' => 1,
        ]);

        $correctAnswer = Answer::factory()->correct()->create([
            'question_id' => $question->id,
            'position' => 1,
        ]);

        Answer::factory()->create([
            'question_id' => $question->id,
            'position' => 2,
        ]);

        return compact(
            'faculty',
            'option',
            'promotion',
            'student',
            'course',
            'chapter',
            'quiz',
            'question',
            'correctAnswer',
        );
    }

    protected function createForeignStudent(): User
    {
        $faculty = Faculty::factory()->create();
        $option = Option::factory()->create(['faculty_id' => $faculty->id]);
        $promotion = Promotion::factory()->create();

        return User::factory()->student()->create([
            'faculty_id' => $faculty->id,
            'option_id' => $option->id,
            'promotion_id' => $promotion->id,
            'role' => UserRole::Student,
        ]);
    }
}
