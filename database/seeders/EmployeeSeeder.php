<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        // 1. Créer d'abord quelques positions si elles n'existent pas
        // (Optionnel si ton PositionSeeder tourne déjà)
        $positions = Position::all();
        if ($positions->isEmpty()) {
            $positions = Position::factory()->count(5)->create();
        }

        // 2. Créer 50 employés aléatoires
        Employee::factory()->count(50)->create([
            'position_id' => fn() => $positions->random()->id,
        ]);

        // 3. Exemple : Créer un employé spécifique pour tes tests
        Employee::factory()->create([
            'first_name' => 'Christelle',
            'last_name'  => 'Dev',
            'email'      => 'christelle@example.com',
            'status'     => 'actif',
        ]);
    }
}
