<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Supprime toutes les positions existantes
        Position::query()->delete();

        $positions = [
            ['name' => 'Ressource Humaine', 'code' => 'RH', 'description' => 'L\'Administrateur du système et chargé des affectations, la création de compte...'],
            ['name' => 'Chef Plateau',       'code' => 'CP', 'description' => 'Dirige les campagnes et controlle les anctions des superviseurs et des tc...'],
            ['name' => 'Superviseur',        'code' => 'SUP', 'description' => 'Supervise tous les téléconseillers sous sont tutel...'],
            ['name' => 'Teleconseiller',     'code' => 'TC', 'description' => 'Chargé de la relation client...'],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }

        $this->command->info('✅ 4 positions ont été créées avec succès.');
    }
}