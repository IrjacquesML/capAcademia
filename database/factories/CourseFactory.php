<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'faculty_id' => Faculty::factory(),
            'option_id' => fn (array $attributes) => Option::factory()->create([
                'faculty_id' => $attributes['faculty_id'],
            ])->id,
            'promotion_id' => Promotion::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->paragraph(),
            'is_published' => true,
        ];
    }
}
