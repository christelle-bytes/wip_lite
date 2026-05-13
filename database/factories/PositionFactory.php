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

     *
     * @return array<string, mixed>


     */
    public function definition(): array
    {
        $positions = [
            ['name' => 'Ressource Humaine', 'code' => 'RH'],
            ['name' => 'Chef Plateau',       'code' => 'CP'],
            ['name' => 'Superviseur',        'code' => 'SUP'],
            ['name' => 'Teleconseiller',     'code' => 'TC'],
        ];

        $pos = fake()->randomElement($positions);

        return [
            'name' => $pos['name'],
            'code' => $pos['code'],
            'description' => fake()->sentence(10),
        ];
    }
}

