<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        $level = fake()->numberBetween(1, 5);

        return [
            'name' => 'L'.$level,
            'slug' => 'l'.$level.'-'.fake()->unique()->numerify('###'),
            'level' => $level,
        ];
    }
}
