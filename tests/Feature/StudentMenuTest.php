<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class StudentMenuTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_a_student_sees_the_main_menu_on_the_dashboard(): void
    {
        $world = $this->createAcademicWorld();

        $this->actingAs($world['student'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Accueil')
            ->assertSee('Mes cours')
            ->assertSee('Ma progression')
            ->assertSee('Mon profil')
            ->assertSee('Navigation principale')
            ->assertSee('viewport-fit=cover', false)
            ->assertDontSee('fixed inset-x-0 bottom-0', false)
            ->assertSee($world['student']->name)
            ->assertSee($world['course']->title)
            ->assertDontSee('Administration')
            ->assertDontSee(Course::factory()->create(['is_published' => true])->title);
    }

    public function test_progress_and_profile_pages_are_reachable(): void
    {
        $world = $this->createAcademicWorld();

        $this->actingAs($world['student'])
            ->get(route('progress'))
            ->assertOk()
            ->assertSee($world['course']->title);

        $this->actingAs($world['student'])
            ->get(route('profile'))
            ->assertOk()
            ->assertSee($world['student']->email)
            ->assertSee('Faculté');
    }

    public function test_guests_are_redirected_from_the_student_menu(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('progress'))->assertRedirect(route('login'));
        $this->get(route('profile'))->assertRedirect(route('login'));
    }
}
