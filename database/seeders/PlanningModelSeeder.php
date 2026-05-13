<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\PlanningModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanningModelSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // On récupère les IDs de tous les employés existants
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $employees = Employee::factory(10)->create();
        }

        // On crée les 15 plannings en utilisant des employés existants pour 'created_by'
        PlanningModel::factory(15)->create([
            'created_by' => $employees->random()->id,
        ]);
    }
}
