<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class UserAuditReportTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_an_admin_can_open_a_student_audit_report(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create();

        AuditEvent::factory()->create([
            'user_id' => $world['student']->id,
            'actor_id' => $world['student']->id,
            'action' => AuditAction::Login,
            'ip_address' => '203.0.113.10',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.audit', $world['student']))
            ->assertOk()
            ->assertSee('Rapport d’audit')
            ->assertSee($world['student']->name)
            ->assertSee($world['student']->email)
            ->assertSee($world['course']->title)
            ->assertSee('Connexion')
            ->assertSee('203.0.113.10');
    }

    public function test_a_student_cannot_open_an_audit_report(): void
    {
        $world = $this->createAcademicWorld();

        $this->actingAs($world['student'])
            ->get(route('admin.users.audit', $world['student']))
            ->assertRedirect(route('dashboard'));
    }

    public function test_a_faculty_admin_cannot_audit_a_student_from_another_faculty(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create(['faculty_id' => $world['faculty']->id]);
        $foreign = $this->createForeignStudent();

        $this->actingAs($admin)
            ->get(route('admin.users.audit', $foreign))
            ->assertForbidden();
    }

    public function test_a_login_and_a_quiz_are_recorded_on_the_user_audit_report(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create();

        $this->post(route('login'), [
            'email' => $world['student']->email,
            'password' => 'password',
        ])->assertRedirect();

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

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $world['student']->id,
            'action' => AuditAction::Login->value,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $world['student']->id,
            'action' => AuditAction::QuizSubmitted->value,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.audit', $world['student']))
            ->assertOk()
            ->assertSee('Interrogation soumise')
            ->assertSee($world['quiz']->title)
            ->assertSee('100');
    }

    public function test_an_admin_cannot_audit_another_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.audit', $other))
            ->assertForbidden();
    }
}
