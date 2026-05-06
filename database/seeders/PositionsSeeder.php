<?php

namespace Database\Seeders;

use App\Models\positions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $positions = [
            ['name' => 'RH Admin', 'code' => 'RHADM', 'description' => 'Gestion des rôles et accès RH'],
            ['name' => 'Chef Plateau', 'code' => 'CP', 'description' => 'Supervision des employés et validation'],
            ['name' => 'Superviseur', 'code' => 'SUP', 'description' => 'Coordination et suivi opérationnel'],
            ['name' => 'Employé TC', 'code' => 'TC', 'description' => 'Exécution des tâches opérationnelles'],
        ];

        foreach ($positions as $data) {
            positions::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}

