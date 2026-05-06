<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PlanningModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanningModel>
 */
class PlanningModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hours = fake()->numberBetween(5, 12);
        $total = $hours * 5;
                return [
            'name'=>fake()->name(),
            'description'=>fake()->sentence(),
            'monday_hours'=>$hours,
            'tuesday_hours'=>$hours,
            'wednesday_hours'=>$hours,
            'thursday_hours'=>$hours,
            'friday_hours'=>$hours,
            'saturday_hours'=>0,
            'sunday_hours'=>0,
            'total_hours'=>$total,
            'created_by'=>Employee::factory(),
        ];
    }
}
