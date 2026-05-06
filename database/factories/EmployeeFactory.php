<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'matricule' => 'EMP-' . $this->faker->unique()->numberBetween(10000, 99999),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'birth_date' => $this->faker->date('Y-m-d', '-18 years'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'position_id' => position::inRandomOrder()->first()?->id ?? position::factory(),
            'salary_base' => $this->faker->randomFloat(2, 1500, 5000),
            'status' => $this->faker->randomElement(['actif', 'suspendu', 'inactif']),
        ];
    }
}
