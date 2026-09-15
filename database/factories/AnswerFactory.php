<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'label' => fake()->words(3, true),
            'is_correct' => false,
            'position' => fake()->numberBetween(1, 6),
        ];
    }

    public function correct(): static
    {
        return $this->state(['is_correct' => true]);
    }
}
