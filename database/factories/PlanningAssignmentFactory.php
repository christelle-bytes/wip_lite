<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PlanningAssignment;
use App\Models\PlanningModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanningAssignment>
 */
class PlanningAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['en attente', 'validé', 'suspendu', 'terminé']);
        return [
            'planning_model_id'=>PlanningModel::factory(),
            'employee_id'=>Employee::factory(),
            'start_date'=>fake()->date(),
            'status'=>$status,
            'validated_by'=>$status == 'validé' ? Employee::factory() : null,
        ];
    }
}
