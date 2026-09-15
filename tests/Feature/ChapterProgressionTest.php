<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Enums\UserRole;
use App\Models\Answer;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class ChapterProgressionTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_the_first_chapter_is_open_and_the_next_one_is_locked(): void
    {
        [$world, $chapterTwo] = $this->worldWithTwoChapters();

        $this->actingAs($world['student'])
            ->get(route('chapters.show', [$world['course'], $world['chapter']]))
            ->assertOk()
            ->assertSee($world['chapter']->title);

        $this->actingAs($world['student'])
            ->get(route('chapters.show', [$world['course'], $chapterTwo]))
            ->assertRedirect(route('courses.show', $world['course']));

        $this->actingAs($world['student'])
            ->get(route('courses.show', $world['course']))
            ->assertOk()
            ->assertSee('Verrouillé')
            ->assertSee($chapterTwo->title);
    }

    public function test_word_html_formatting_is_rendered_in_the_chapter(): void
    {
        $world = $this->createAcademicWorld();
        $world['chapter']->update([
            'content' => '<p>Un <strong>algorithme</strong> est une suite.</p>',
        ]);

        $this->actingAs($world['student'])
            ->get(route('chapters.show', [$world['course'], $world['chapter']]))
            ->assertOk()
            ->assertSee('<strong>algorithme</strong>', false)
            ->assertDontSee('&lt;strong&gt;')
            ->assertSee('text-align: justify', false);
    }

    public function test_submitting_the_previous_quiz_unlocks_the_next_chapter(): void
    {
        [$world, $chapterTwo] = $this->worldWithTwoChapters();

        $this->actingAs($world['student'])
            ->post(route('quizzes.submit', [
                $world['course'],
                $world['chapter'],
                $world['quiz'],
            ]), [
                'answers' => [
                    $world['question']->id => $world['correctAnswer']->id,
                ],
            ])
            ->assertRedirect();

        $this->actingAs($world['student'])
            ->get(route('chapters.show', [$world['course'], $chapterTwo]))
            ->assertOk()
            ->assertSee($chapterTwo->title);
    }

    public function test_a_failed_quiz_still_unlocks_the_next_chapter(): void
    {
        [$world, $chapterTwo] = $this->worldWithTwoChapters();
        $wrongAnswer = $world['question']->answers()->where('is_correct', false)->first();

        $this->actingAs($world['student'])
            ->post(route('quizzes.submit', [
                $world['course'],
                $world['chapter'],
                $world['quiz'],
            ]), [
                'answers' => [
                    $world['question']->id => $wrongAnswer->id,
                ],
            ])
            ->assertRedirect();

        $this->actingAs($world['student'])
            ->get(route('chapters.show', [$world['course'], $chapterTwo]))
            ->assertOk();
    }

    public function test_a_student_cannot_submit_a_locked_chapter_quiz(): void
    {
        [$world, $chapterTwo] = $this->worldWithTwoChapters();
        $quizTwo = $chapterTwo->quiz;
        $questionTwo = $quizTwo->questions()->first();
        $answerTwo = $questionTwo->answers()->where('is_correct', true)->first();

        $this->actingAs($world['student'])
            ->post(route('quizzes.submit', [
                $world['course'],
                $chapterTwo,
                $quizTwo,
            ]), [
                'answers' => [
                    $questionTwo->id => $answerTwo->id,
                ],
            ])
            ->assertForbidden();
    }

    public function test_a_teacher_can_open_any_chapter(): void
    {
        [$world, $chapterTwo] = $this->worldWithTwoChapters();
        $teacher = $world['student']->replicate();
        $teacher->email = 'teacher-unlock@univ.test';
        $teacher->role = UserRole::Teacher;
        $teacher->save();

        $this->actingAs($teacher)
            ->get(route('chapters.show', [$world['course'], $chapterTwo]))
            ->assertOk();
    }

    /**
     * @return array{0: array<string, mixed>, 1: Chapter}
     */
    private function worldWithTwoChapters(): array
    {
        $world = $this->createAcademicWorld();

        $chapterTwo = Chapter::factory()->create([
            'course_id' => $world['course']->id,
            'position' => 2,
            'is_published' => true,
            'title' => 'Chapitre verrouillé de test',
        ]);

        $quizTwo = Quiz::factory()->create([
            'chapter_id' => $chapterTwo->id,
            'passing_score' => 50,
        ]);

        $questionTwo = Question::factory()->create([
            'quiz_id' => $quizTwo->id,
            'type' => QuestionType::MultipleChoice,
            'points' => 1,
            'position' => 1,
        ]);

        Answer::factory()->correct()->create([
            'question_id' => $questionTwo->id,
            'position' => 1,
        ]);
        Answer::factory()->create([
            'question_id' => $questionTwo->id,
            'position' => 2,
        ]);

        $chapterTwo->load(['quiz.questions.answers']);

        return [$world, $chapterTwo];
    }
}
