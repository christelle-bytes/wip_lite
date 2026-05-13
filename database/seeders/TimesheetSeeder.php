<?php

namespace Database\Seeders;

use App\Models\Timesheet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = \App\Models\Employee::all();

        if ($employees->isEmpty()) {
            return;
        }

        foreach ($employees as $employee) {
            // Créer une feuille de temps pour chaque employé pour le mois dernier
            Timesheet::factory()->create([
                'employee_id' => $employee->id,
                'validated_by' => rand(0, 1) ? $employees->random()->id : null,
            ]);
        }
    }
}
