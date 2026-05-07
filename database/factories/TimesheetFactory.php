<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Timesheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timesheet>
 */
class TimesheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', 'now');
        // On s'assure que la fin est 7 jours après le début
        $endDate = (clone $startDate)->modify('+7 days');

        $status = fake()->randomElement(['draft', 'submitted', 'validated']);

        return [
            'employee_id'  => Employee::factory(),
            'period_start' => $startDate,
            'period_end'   => $endDate,
            'status'       => $status,
            'validated_by' => $status === 'validated' ? Employee::factory() : null,
        ];
    }
}
