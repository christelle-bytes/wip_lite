<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            // Lie à un utilisateur ou en crée un
            'user_id'     => User::factory(), 
            
            // Génère un matricule unique type EMP-00123
            'matricule'   => 'EMP-' . $this->faker->unique()->numberBetween(1000, 9999),
            
            'first_name'  => $this->faker->firstName(),
            'last_name'   => $this->faker->lastName(),
            
            // Date de naissance (entre 20 et 55 ans)
            'birth_date'  => $this->faker->dateTimeBetween('-55 years', '-20 years')->format('Y-m-d'),
            
            'phone'       => $this->faker->phoneNumber(),
            'email'       => $this->faker->unique()->safeEmail(),
            'address'     => $this->faker->address(),
            
            // Lie à un poste (Position) existant ou en crée un
            'position_id' => Position::factory(),
            
            'salary_base' => $this->faker->randomFloat(2, 1200, 4500),
            
            // Statuts probables
            'status'      => $this->faker->randomElement(['actif', 'suspendu', 'inactif']),
        ];
    }
}