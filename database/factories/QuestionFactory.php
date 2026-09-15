<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'type' => QuestionType::MultipleChoice,
            'prompt' => fake()->sentence().' ?',
            'points' => 1,
            'position' => fake()->numberBetween(1, 20),
        ];
    }
}
