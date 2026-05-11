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
        $positions = Position::all();
        if ($positions->isEmpty()) {
            return;
        }

        // Créer 20 employés sans compte utilisateur
        Employee::factory()->count(20)->create([
            'position_id' => fn() => $positions->random()->id,
            'status' => 'actif',
        ]);
    }
}
