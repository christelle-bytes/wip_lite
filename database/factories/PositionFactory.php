<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Ressource Humaine','Chef Plateau', 'Superviseur', 'Teleconseiller']),
            'code' => fake()->randomElement(['RH','CP', 'SUP', 'TC']),
            'description' => fake()->sentence(10),
        ];
    }
}

