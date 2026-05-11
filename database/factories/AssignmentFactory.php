<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'campaign_id' => Campaign::factory(),
            'manager_id' => null,
            'position_id' => Position::factory(),
            'status' => $this->faker->randomElement(['actif', 'suspendu', 'inactif']),
            'start_date' => now(),
            'end_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
    public function forSupervisor($managerId, $campaignId)
    {
        return $this->state(fn(array $attributes) => [
            'manager_id' => $managerId,
            'campaign_id' => $campaignId,

        ]);
    }
}

