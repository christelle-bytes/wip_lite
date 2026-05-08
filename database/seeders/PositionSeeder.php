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
            ['name' => 'Ressource Humaine', 'code' => 'RH'],
            ['name' => 'Chef Plateau',       'code' => 'CP'],
            ['name' => 'Superviseur',        'code' => 'SUP'],
            ['name' => 'Teleconseiller',     'code' => 'TC'],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }

        $this->command->info('✅ 4 positions ont été créées avec succès.');
    }
}