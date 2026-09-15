<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class AdminUserActivityNotificationTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_a_student_login_does_not_notify_the_admin(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create();

        $this->post(route('login'), [
            'email' => $world['student']->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertSame(0, $admin->fresh()->notifications()->count());
    }

    public function test_opening_a_course_does_not_notify_the_admin(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create();

        $this->actingAs($world['student'])
            ->get(route('courses.show', $world['course']))
            ->assertOk();

        $this->assertSame(0, $admin->fresh()->notifications()->count());
    }

    public function test_marking_a_chapter_as_read_notifies_the_faculty_admin_only(): void
    {
        $world = $this->createAcademicWorld();
        $facultyAdmin = User::factory()->admin()->create(['faculty_id' => $world['faculty']->id]);
        $otherAdmin = User::factory()->admin()->create(['faculty_id' => $this->createForeignStudent()->faculty_id]);

        $this->actingAs($world['student'])
            ->post(route('chapters.read', [$world['course'], $world['chapter']]))
            ->assertRedirect();

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $world['student']->id,
            'action' => AuditAction::ChapterRead->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $facultyAdmin->id,
            'notifiable_type' => User::class,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $otherAdmin->id,
            'notifiable_type' => User::class,
        ]);

        $this->actingAs($facultyAdmin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Chapitre lu')
            ->assertSee($world['student']->name);
    }

    public function test_a_faculty_admin_is_not_notified_of_another_faculty_student(): void
    {
        Notification::fake();

        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create(['faculty_id' => $world['faculty']->id]);
        $foreign = $this->createForeignStudent();
        $foreignCourse = Course::factory()->create([
            'faculty_id' => $foreign->faculty_id,
            'option_id' => $foreign->option_id,
            'promotion_id' => $foreign->promotion_id,
            'is_published' => true,
        ]);
        $foreignChapter = Chapter::factory()->create([
            'course_id' => $foreignCourse->id,
            'position' => 1,
            'is_published' => true,
        ]);

        $this->actingAs($foreign)
            ->post(route('chapters.read', [$foreignCourse, $foreignChapter]))
            ->assertRedirect();

        Notification::assertNotSentTo($admin, UserActivityNotification::class);
    }

    public function test_a_student_cannot_open_admin_notifications(): void
    {
        $world = $this->createAcademicWorld();

        $this->actingAs($world['student'])
            ->get(route('admin.notifications.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_an_admin_can_mark_notifications_as_read(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create();

        $this->actingAs($world['student'])
            ->post(route('chapters.read', [$world['course'], $world['chapter']]))
            ->assertRedirect();

        $this->assertSame(1, $admin->fresh()->unreadNotifications()->count());

        $notification = $admin->fresh()->unreadNotifications()->first();

        $this->actingAs($admin)
            ->get(route('admin.notifications.show', $notification))
            ->assertRedirect(route('admin.users.audit', $world['student']));

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_a_quiz_submission_notifies_the_admin(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create();

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

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Derniers mouvements')
            ->assertSee('Interrogation soumise')
            ->assertSee($world['student']->name);
    }

    public function test_an_admin_is_not_notified_of_their_own_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertSame(0, $admin->fresh()->notifications()->count());
    }
}
