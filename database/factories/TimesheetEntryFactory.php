<?php

namespace Database\Factories;

use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimesheetEntry>
 */
class TimesheetEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('08:00:00', '10:00:00');
    // On s'assure que la sortie est 4 à 10 heures après l'entrée
    $checkOut = (clone $checkIn)->modify('+' . fake()->numberBetween(4, 10) . ' hours');

    return [
        'timesheet_id'   => Timesheet::factory(),
        'date'           => fake()->date(),
        'check_in'       => $checkIn,
        'check_out'      => $checkOut,
        // Utilise numberBetween pour des minutes ou randomFloat pour des heures
        'break_duration' => fake()->numberBetween(30, 60), 
        'total_hours'    => fake()->randomFloat(2, 1, 12),
        'planned_hours'  => 8, // Souvent une valeur fixe par défaut
        'overtime_hours' => fake()->randomFloat(2, 0, 4),
        // 'sentence' est peut-être long pour un type d'absence (ex: "Maladie", "Congé")
        'absence_type'   => fake()->randomElement(['Maladie', 'Congé', 'Formation', null]),
        'comment'        => fake()->sentence(),
    ];
    }
}
