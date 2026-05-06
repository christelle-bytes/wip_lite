<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            // On pioche au hasard parmi les types autorisés dans ta migration
            'name' => $this->faker->randomElement(['admin', 'cp', 'sup', 'tc']),
        ];
    }
}