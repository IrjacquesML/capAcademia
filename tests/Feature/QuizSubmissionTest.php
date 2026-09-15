<?php

namespace Tests\Feature;

use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class QuizSubmissionTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_a_correct_quiz_submission_stores_the_score_and_validates_the_chapter(): void
    {
        $world = $this->createAcademicWorld();

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

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $world['student']->id,
            'quiz_id' => $world['quiz']->id,
            'score' => 1,
            'max_score' => 1,
            'passed' => 1,
        ]);

        $this->assertDatabaseHas('chapter_progress', [
            'user_id' => $world['student']->id,
            'chapter_id' => $world['chapter']->id,
        ]);

        $attempt = QuizAttempt::query()->first();
        $this->assertTrue($attempt->passed);
        $this->assertEquals(100.0, (float) $attempt->percentage);
        $this->assertSame($world['correctAnswer']->label, $attempt->breakdown[0]['student_answer']);
        $this->assertContains($world['correctAnswer']->label, $attempt->breakdown[0]['expected_answers']);
    }

    public function test_a_student_cannot_submit_a_quiz_from_another_academic_context(): void
    {
        $world = $this->createAcademicWorld();
        $intruder = $this->createForeignStudent();

        $this->actingAs($intruder)
            ->post(route('quizzes.submit', [
                $world['course'],
                $world['chapter'],
                $world['quiz'],
            ]), [
                'answers' => [
                    $world['question']->id => $world['correctAnswer']->id,
                ],
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_a_client_supplied_score_is_ignored(): void
    {
        $world = $this->createAcademicWorld();
        $wrongAnswer = $world['question']->answers()->where('is_correct', false)->first();

        $this->actingAs($world['student'])
            ->post(route('quizzes.submit', [
                $world['course'],
                $world['chapter'],
                $world['quiz'],
            ]), [
                'score' => 100,
                'passed' => true,
                'answers' => [
                    $world['question']->id => $wrongAnswer->id,
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $world['student']->id,
            'passed' => 0,
            'score' => 0,
        ]);
    }

    public function test_the_correction_page_shows_the_student_answer_and_the_expected_answer(): void
    {
        $world = $this->createAcademicWorld();
        $wrongAnswer = $world['question']->answers()->where('is_correct', false)->first();

        $response = $this->actingAs($world['student'])
            ->post(route('quizzes.submit', [
                $world['course'],
                $world['chapter'],
                $world['quiz'],
            ]), [
                'answers' => [
                    $world['question']->id => $wrongAnswer->id,
                ],
            ]);

        $attempt = QuizAttempt::query()->first();

        $response->assertRedirect(route('quizzes.result', [
            $world['course'],
            $world['chapter'],
            $world['quiz'],
            $attempt,
        ]));

        $this->actingAs($world['student'])
            ->get(route('quizzes.result', [
                $world['course'],
                $world['chapter'],
                $world['quiz'],
                $attempt,
            ]))
            ->assertOk()
            ->assertSee('Votre réponse')
            ->assertSee('Bonne réponse')
            ->assertSee($wrongAnswer->label)
            ->assertSee($world['correctAnswer']->label)
            ->assertSee($world['question']->prompt);
    }

    public function test_a_student_cannot_view_another_students_correction(): void
    {
        $world = $this->createAcademicWorld();

        $this->actingAs($world['student'])
            ->post(route('quizzes.submit', [
                $world['course'],
                $world['chapter'],
                $world['quiz'],
            ]), [
                'answers' => [
                    $world['question']->id => $world['correctAnswer']->id,
                ],
            ]);

        $attempt = QuizAttempt::query()->first();
        $classmate = $world['student']->replicate();
        $classmate->email = 'classmate@univ.test';
        $classmate->save();

        $this->actingAs($classmate)
            ->get(route('quizzes.result', [
                $world['course'],
                $world['chapter'],
                $world['quiz'],
                $attempt,
            ]))
            ->assertNotFound();
    }
}
