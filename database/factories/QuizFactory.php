<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'title' => 'Interrogation : '.fake()->words(3, true),
            'description' => fake()->sentence(),
            'passing_score' => 50,
        ];
    }
}
