<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Ressource Humaine', 'code' => 'RH'],
            ['name' => 'Chef Plateau',       'code' => 'CP'],
            ['name' => 'Superviseur',        'code' => 'SUP'],
            ['name' => 'Teleconseiller',     'code' => 'TC'],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['code' => $position['code']], $position);
        }

        $this->command->info('✅ Les positions ont été synchronisées.');
    }
}