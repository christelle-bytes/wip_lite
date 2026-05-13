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
        if (Employee::count() === 0) {
            // Ici, on suppose que votre EmployeeFactory gère déjà la création d'une position
            Employee::factory(10)->create();
        }

        // 2. On récupère les IDs de tous les employés existants
        $employeeIds = Employee::pluck('id');

        // 3. On crée les 15 plannings en forçant l'utilisation d'un ID existant
        PlanningModel::factory(15)->create([
            'created_by' => $employeeIds->random(),
        ]);
    }
}
