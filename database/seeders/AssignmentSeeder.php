<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Récupérer les données nécessaires
        $campaigns = Campaign::where('status', 'active')->get();
        if ($campaigns->isEmpty()) {
            $campaigns = collect([Campaign::factory()->create(['status' => 'active', 'name' => 'Campagne de Test'])]);
        }

        $posCP = Position::where('code', 'CP')->first();
        $posSUP = Position::where('code', 'SUP')->first();
        $posTC = Position::where('code', 'TC')->first();

        $roleCP = Role::where('name', 'CP')->first();
        $roleSUP = Role::where('name', 'SUP')->first();
        $roleTC = Role::where('name', 'TC')->first();

        foreach ($campaigns as $campaign) {
            // 2. Créer un Chef de Plateau (CP) et son compte utilisateur pour chaque campagne
            $cpEmployee = Employee::factory()->create([
                'position_id' => $posCP->id,
                'status' => 'actif'
            ]);
            $cpUser = User::create([
                'email' => $cpEmployee->email,
                'password' => \Hash::make('password'),
                'role_id' => $roleCP->id,
                'must_change_password' => false,
                'is_active' => true,
            ]);
            $cpEmployee->update(['user_id' => $cpUser->id]);

            Assignment::create([
                'employee_id' => $cpEmployee->id,
                'campaign_id' => $campaign->id,
                'position_id' => $posCP->id,
                'manager_id'  => null,
                'status'      => 'actif',
                'start_date'  => now(),
            ]);

            // 3. Créer 1-2 Superviseurs pour ce CP
            $supCount = rand(1, 2);
            for ($i = 0; $i < $supCount; $i++) {
                $supEmployee = Employee::factory()->create([
                    'position_id' => $posSUP->id,
                    'status' => 'actif'
                ]);
                $supUser = User::create([
                    'email' => $supEmployee->email,
                    'password' => \Hash::make('password'),
                    'role_id' => $roleSUP->id,
                    'must_change_password' => false,
                    'is_active' => true,
                ]);
                $supEmployee->update(['user_id' => $supUser->id]);

                Assignment::create([
                    'employee_id' => $supEmployee->id,
                    'campaign_id' => $campaign->id,
                    'position_id' => $posSUP->id,
                    'manager_id'  => $cpEmployee->id,
                    'status'      => 'actif',
                    'start_date'  => now(),
                ]);

                // 4. Créer 3-5 Téléconseillers pour chaque Superviseur
                $tcCount = rand(3, 5);
                for ($j = 0; $j < $tcCount; $j++) {
                    $tcEmployee = Employee::factory()->create([
                        'position_id' => $posTC->id,
                        'status' => 'actif'
                    ]);
                    $tcUser = User::create([
                        'email' => $tcEmployee->email,
                        'password' => \Hash::make('password'),
                        'role_id' => $roleTC->id,
                        'must_change_password' => false,
                        'is_active' => true,
                    ]);
                    $tcEmployee->update(['user_id' => $tcUser->id]);

                    Assignment::create([
                        'employee_id' => $tcEmployee->id,
                        'campaign_id' => $campaign->id,
                        'position_id' => $posTC->id,
                        'manager_id'  => $supEmployee->id,
                        'status'      => 'actif',
                        'start_date'  => now(),
                    ]);
                }
            }
        }
    }
}
