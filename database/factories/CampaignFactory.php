<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+6 months');

        return [
            'name'        => $this->faker->company() . ' Campaign',
            'description' => $this->faker->paragraph(),
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'status'      => $this->faker->randomElement(['active', 'inactive', 'terminée']),
        ];
    }
}
