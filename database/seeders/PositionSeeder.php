<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprime toutes les positions existantes pour garantir qu'il n'y en ait QUE 4
        Position::query()->delete();

        $positions = [
            [
                'name' => 'Ressource Humaine',
                'code' => 'RH'
            ],
            [
                'name' => 'Chef Plateau',
                'code' => 'CP'
            ],
            [
                'name' => 'Superviseur',
                'code' => 'SUP'
            ],
            [
                'name' => 'Teleconseiller',
                'code' => 'TC'
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }

        $this->command->info('✅ Positions initialisées avec succès (4 positions uniquement)');
    }
}