<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_a_student_can_login_and_list_only_their_courses(): void
    {
        ['student' => $student, 'course' => $course] = $this->createAcademicWorld();
        $foreignCourse = Course::factory()->create(['is_published' => true]);

        $login = $this->postJson('/api/login', [
            'email' => $student->email,
            'password' => 'password',
        ]);

        $login->assertOk()->assertJsonPath('user.email', $student->email);
        $token = $login->json('token');
        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/courses')
            ->assertOk()
            ->assertJsonFragment(['title' => $course->title])
            ->assertJsonMissing(['title' => $foreignCourse->title]);
    }

    public function test_a_student_cannot_open_a_foreign_course_via_the_api(): void
    {
        ['course' => $course] = $this->createAcademicWorld();
        $intruder = $this->createForeignStudent();

        $token = $this->postJson('/api/login', [
            'email' => $intruder->email,
            'password' => 'password',
        ])->json('token');

        $this->withToken($token)
            ->getJson('/api/courses/'.$course->id)
            ->assertNotFound();
    }

    public function test_a_student_can_submit_a_quiz_via_the_api(): void
    {
        $world = $this->createAcademicWorld();

        $token = $this->postJson('/api/login', [
            'email' => $world['student']->email,
            'password' => 'password',
        ])->json('token');

        $this->withToken($token)
            ->postJson('/api/courses/'.$world['course']->id.'/chapters/'.$world['chapter']->id.'/quizzes/'.$world['quiz']->id, [
                'answers' => [
                    $world['question']->id => $world['correctAnswer']->id,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('attempt.passed', true);
    }
}
