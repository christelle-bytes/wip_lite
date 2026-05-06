<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\User;
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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        // 1. Créer une campagne
        $campaign = Campaign::factory()->create(['status' => 'active']);

        // 2. Créer un Chef de Plateau (CP) sur cette campagne
        $cp = Employee::factory()->create();
        Assignment::factory()->create([
            'employee_id' => $cp->id,
            'campaign_id' => $campaign->id,
            'manager_id' => null,
            'position_id' => 1, // ID pour CP
        ]);

        // 3. Créer un Superviseur rattaché à ce CP
        $sup = Employee::factory()->create();
        Assignment::factory()->create([
            'employee_id' => $sup->id,
            'campaign_id' => $campaign->id,
            'manager_id' => $cp->id,
            'position_id' => 2, // ID pour SUP
        ]);
    }
}
