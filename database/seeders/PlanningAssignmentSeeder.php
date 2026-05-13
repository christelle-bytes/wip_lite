<?php

namespace Database\Seeders;

use App\Models\PlanningAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanningAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planningModels = \App\Models\PlanningModel::all();
        $employees = \App\Models\Employee::all();

        if ($planningModels->isEmpty() || $employees->isEmpty()) {
            return;
        }

        foreach ($planningModels as $model) {
            // Assigner chaque modèle à 1-3 employés aléatoires
            $assignedEmployees = $employees->random(rand(1, 3));
            
            foreach ($assignedEmployees as $employee) {
                PlanningAssignment::factory()->create([
                    'planning_model_id' => $model->id,
                    'employee_id' => $employee->id,
                    'validated_by' => rand(0, 1) ? $employees->random()->id : null,
                ]);
            }
        }
    }
}
