<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_a_student_is_redirected_away_from_the_admin_area(): void
    {
        $world = $this->createAcademicWorld();

        $this->actingAs($world['student'])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_a_super_admin_reaches_the_admin_dashboard_after_login(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Administration CapAcademia')
            ->assertSee('Facultés')
            ->assertSee('Nouvelle faculté')
            ->assertSee('Nouvelle option')
            ->assertSee('Nouvelle promotion');
    }

    public function test_an_admin_cannot_open_faculty_management(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.faculties.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.options.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.promotions.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Nouvelle faculté');
    }

    public function test_a_faculty_admin_only_sees_courses_from_their_faculty(): void
    {
        $world = $this->createAcademicWorld();
        $foreign = Course::factory()->create(['is_published' => true]);

        $admin = User::factory()->admin()->create([
            'faculty_id' => $world['faculty']->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee($world['course']->title)
            ->assertDontSee($foreign->title);
    }

    public function test_an_admin_cannot_create_a_super_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $faculty = Faculty::factory()->create();
        $option = Option::factory()->create(['faculty_id' => $faculty->id]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Intrus',
                'email' => 'intrus@capacademia.test',
                'password' => 'password',
                'role' => UserRole::SuperAdmin->value,
                'faculty_id' => $faculty->id,
                'option_id' => $option->id,
            ])
            ->assertSessionHasErrors('role');
    }

    public function test_a_super_admin_can_create_an_admin(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->post(route('admin.users.store'), [
                'name' => 'Admin facultaire',
                'email' => 'nouveau.admin@capacademia.test',
                'password' => 'password123',
                'role' => UserRole::Admin->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'nouveau.admin@capacademia.test',
            'role' => UserRole::Admin->value,
        ]);
    }

    public function test_a_super_admin_can_create_faculties_options_and_promotions(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->post(route('admin.faculties.store'), [
                'name' => 'Faculté de test',
                'code' => 'FTEST',
            ])
            ->assertRedirect(route('admin.faculties.index'));

        $faculty = Faculty::query()->where('code', 'FTEST')->firstOrFail();

        $this->post(route('admin.options.store'), [
            'faculty_id' => $faculty->id,
            'name' => 'Option de test',
        ])->assertRedirect(route('admin.options.index'));

        $this->post(route('admin.promotions.store'), [
            'name' => 'Promotion de test',
            'level' => 1,
        ])->assertRedirect(route('admin.promotions.index'));

        $this->assertDatabaseHas('faculties', ['name' => 'Faculté de test']);
        $this->assertDatabaseHas('options', [
            'faculty_id' => $faculty->id,
            'name' => 'Option de test',
        ]);
        $this->assertDatabaseHas('promotions', [
            'name' => 'Promotion de test',
            'level' => 1,
        ]);
    }
}
