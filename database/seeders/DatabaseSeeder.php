<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       
        $this->call([
            UsersSeeder::class,
            PositionSeeder::class,
            EmployeeSeeder::class,
            CampaignSeeder::class,
            AssignmentSeeder::class,
            PlanningModelSeeder::class,
            PlanningAssignmentSeeder::class,
            TimesheetSeeder::class,
            TimesheetEntrySeeder::class,
        
        ]);
      
    }
}

