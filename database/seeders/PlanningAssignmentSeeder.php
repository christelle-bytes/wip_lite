<?php

namespace Database\Seeders;

use App\Models\PlanningAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanningAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlanningAssignment::factory(15)->create();
    }
}
