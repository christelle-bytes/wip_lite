<?php

namespace Database\Seeders;

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
        PlanningModel::factory(15)->create();
    }
}
