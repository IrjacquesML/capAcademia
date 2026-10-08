<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_available_from_the_login_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Créer un compte étudiant');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('register'));
    }

    public function test_web_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post(route('login'), [
                'email' => 'unknown@capacademia.test',
                'password' => 'incorrect-password',
            ])->assertRedirect();
        }

        $this->post(route('login'), [
            'email' => 'unknown@capacademia.test',
            'password' => 'incorrect-password',
        ])->assertStatus(429);
    }

    public function test_a_student_can_register_and_is_signed_in(): void
    {
        $faculty = Faculty::factory()->create();
        $option = Option::factory()->create(['faculty_id' => $faculty->id]);
        $promotion = Promotion::factory()->create();
        Course::factory()->create([
            'faculty_id' => $faculty->id,
            'option_id' => $option->id,
            'promotion_id' => $promotion->id,
            'is_published' => true,
        ]);

        $this->post(route('register'), [
            'name' => 'Nouvel étudiant',
            'email' => 'etudiant@capacademia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'faculty_id' => $faculty->id,
            'option_id' => $option->id,
            'promotion_id' => $promotion->id,
            'role' => UserRole::SuperAdmin->value,
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'etudiant@capacademia.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertSame($faculty->id, $user->faculty_id);
        $this->assertSame($option->id, $user->option_id);
        $this->assertSame($promotion->id, $user->promotion_id);
    }

    public function test_registration_choices_only_include_published_course_combinations(): void
    {
        $faculty = Faculty::factory()->create(['name' => 'Faculté disponible']);
        $option = Option::factory()->create(['faculty_id' => $faculty->id, 'name' => 'Option disponible']);
        $promotion = Promotion::factory()->create(['name' => 'Promotion disponible']);
        Course::factory()->create([
            'faculty_id' => $faculty->id,
            'option_id' => $option->id,
            'promotion_id' => $promotion->id,
            'is_published' => true,
        ]);

        $unusedFaculty = Faculty::factory()->create(['name' => 'Faculté sans cours']);
        $unusedOption = Option::factory()->create(['faculty_id' => $unusedFaculty->id, 'name' => 'Option sans cours']);
        $unusedPromotion = Promotion::factory()->create(['name' => 'Promotion sans cours']);
        Course::factory()->create([
            'faculty_id' => $unusedFaculty->id,
            'option_id' => $unusedOption->id,
            'promotion_id' => $unusedPromotion->id,
            'is_published' => false,
        ]);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Faculté disponible')
            ->assertSee('Option disponible')
            ->assertSee('Promotion disponible')
            ->assertDontSee('Faculté sans cours')
            ->assertDontSee('Option sans cours')
            ->assertDontSee('Promotion sans cours');
    }

    public function test_registration_rejects_an_option_from_another_faculty(): void
    {
        $faculty = Faculty::factory()->create();
        $otherFaculty = Faculty::factory()->create();
        $option = Option::factory()->create(['faculty_id' => $otherFaculty->id]);
        $promotion = Promotion::factory()->create();

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Nouvel étudiant',
                'email' => 'etudiant@capacademia.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'faculty_id' => $faculty->id,
                'option_id' => $option->id,
                'promotion_id' => $promotion->id,
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('option_id');

        $this->assertDatabaseMissing('users', ['email' => 'etudiant@capacademia.test']);
    }

    public function test_registration_rejects_a_promotion_without_a_published_course_for_the_selected_option(): void
    {
        $faculty = Faculty::factory()->create();
        $option = Option::factory()->create(['faculty_id' => $faculty->id]);
        $availablePromotion = Promotion::factory()->create();
        $otherPromotion = Promotion::factory()->create();
        Course::factory()->create([
            'faculty_id' => $faculty->id,
            'option_id' => $option->id,
            'promotion_id' => $availablePromotion->id,
            'is_published' => true,
        ]);

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Nouvel étudiant',
                'email' => 'etudiant@capacademia.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'faculty_id' => $faculty->id,
                'option_id' => $option->id,
                'promotion_id' => $otherPromotion->id,
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('promotion_id');

        $this->assertDatabaseMissing('users', ['email' => 'etudiant@capacademia.test']);
    }
}
