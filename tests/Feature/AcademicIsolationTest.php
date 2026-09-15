<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class AcademicIsolationTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_a_student_only_sees_courses_from_their_academic_triplet(): void
    {
        ['student' => $student, 'course' => $course] = $this->createAcademicWorld();

        $foreignCourse = Course::factory()->create(['is_published' => true]);

        $response = $this->actingAs($student)->get(route('courses.index'));

        $response->assertOk();
        $response->assertSee($course->title);
        $response->assertDontSee($foreignCourse->title);
    }

    public function test_a_student_cannot_open_a_course_from_another_faculty_option_or_promotion(): void
    {
        ['course' => $course] = $this->createAcademicWorld();
        $intruder = $this->createForeignStudent();

        $this->actingAs($intruder)
            ->get(route('courses.show', $course))
            ->assertNotFound();
    }

    public function test_an_unpublished_course_is_hidden_from_students(): void
    {
        ['student' => $student, 'course' => $course] = $this->createAcademicWorld();
        $course->update(['is_published' => false]);

        $this->actingAs($student)
            ->get(route('courses.show', $course))
            ->assertNotFound();
    }
}
