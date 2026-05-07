<?php

namespace Database\Seeders;


use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //


        $positions = [
            ['name' => 'Ressource Humaine', 'code' => 'RH'],
            ['name' => 'Chef Plateau', 'code' => 'CP'],
            ['name' => 'Superviseur', 'code' => 'SUP'],
            ['name' => 'Teleconseiller', 'code' => 'TC'],
        ];

        foreach ($positions as $pos) {
            Position::firstOrCreate(['code' => $pos['code']], $pos);
        }

    }
}
