<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
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
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $student->id,
            'action' => AuditAction::Login->value,
        ]);
        $token = $login->json('token');
        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/courses')
            ->assertOk()
            ->assertJsonFragment(['title' => $course->title])
            ->assertJsonMissing(['title' => $foreignCourse->title]);
    }

    public function test_an_invalid_api_password_is_rejected_and_audited(): void
    {
        $student = $this->createAcademicWorld()['student'];

        $this->postJson('/api/login', [
            'email' => $student->email,
            'password' => 'incorrect-password',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Identifiants incorrects.');

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $student->id,
            'action' => AuditAction::LoginFailed->value,
        ]);
    }

    public function test_a_student_can_log_out_and_revoke_their_api_token(): void
    {
        $student = $this->createAcademicWorld()['student'];

        $token = $this->postJson('/api/login', [
            'email' => $student->email,
            'password' => 'password',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token),
        ]);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)
            ->getJson('/api/me')
            ->assertUnauthorized();
    }

    public function test_course_api_reports_completed_progress_and_resume_chapter_consistently(): void
    {
        $world = $this->createAcademicWorld();
        $token = $this->postJson('/api/login', [
            'email' => $world['student']->email,
            'password' => 'password',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->getJson('/api/courses')
            ->assertOk()
            ->assertJsonPath('courses.0.progress_done', 0)
            ->assertJsonPath('courses.0.progress_total', 1)
            ->assertJsonPath('courses.0.progress_percent', 0)
            ->assertJsonPath('courses.0.resume_chapter.id', $world['chapter']->id);

        $this->withToken($token)
            ->getJson('/api/courses/'.$world['course']->id)
            ->assertOk()
            ->assertJsonPath('course.progress_total', 1)
            ->assertJsonPath('course.resume_chapter.id', $world['chapter']->id);

        $this->withToken($token)
            ->postJson('/api/courses/'.$world['course']->id.'/chapters/'.$world['chapter']->id.'/read')
            ->assertOk()
            ->assertJsonPath('completed', false);

        $wrongAnswer = $world['question']->answers()->where('is_correct', false)->firstOrFail();
        $this->withToken($token)
            ->postJson('/api/courses/'.$world['course']->id.'/chapters/'.$world['chapter']->id.'/quizzes/'.$world['quiz']->id, [
                'answers' => [$world['question']->id => $wrongAnswer->id],
            ])
            ->assertOk()
            ->assertJsonPath('attempt.passed', false);

        $this->withToken($token)
            ->getJson('/api/progress')
            ->assertOk()
            ->assertJsonPath('courses.0.progress_done', 0)
            ->assertJsonPath('courses.0.resume_chapter.id', $world['chapter']->id);

        $this->withToken($token)
            ->postJson('/api/courses/'.$world['course']->id.'/chapters/'.$world['chapter']->id.'/quizzes/'.$world['quiz']->id, [
                'answers' => [$world['question']->id => $world['correctAnswer']->id],
            ])
            ->assertOk()
            ->assertJsonPath('attempt.passed', true);

        $this->withToken($token)
            ->getJson('/api/courses/'.$world['course']->id)
            ->assertOk()
            ->assertJsonPath('course.progress_done', 1)
            ->assertJsonPath('course.progress_percent', 100)
            ->assertJsonPath('course.resume_chapter', null);
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
