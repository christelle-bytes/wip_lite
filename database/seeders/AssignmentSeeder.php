<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Récupérer les données nécessaires
        $campaign = Campaign::where('status', 'active')->first();

        // On récupère les positions par leur code (plus sûr que l'ID)
        $posCP = Position::where('code', 'CP')->first();
        $posSUP = Position::where('code', 'SUP')->first();
        $posTC = Position::where('code', 'TC')->first();

        if (!$campaign || !$posCP) return;

        // 2. Créer un Chef de Plateau (CP)
        $cp = Employee::factory()->create();
        $cpAssignment = Assignment::factory()->create([
            'employee_id' => $cp->id,
            'campaign_id' => $campaign->id,
            'position_id' => $posCP->id,
            'manager_id'  => null, // Le CP est en haut de la pyramide
            'status'      => 'actif'
        ]);

        // 3. Créer 2 Superviseurs pour ce CP
        Employee::factory(2)->create()->each(function ($sup) use ($campaign, $cp, $posSUP) {
            Assignment::factory()->create([
                'employee_id' => $sup->id,
                'campaign_id' => $campaign->id,
                'position_id' => $posSUP->id,
                'manager_id'  => $cp->id, // Rattaché au CP
                'status'      => 'actif'
            ]);

            // 4. Créer 5 Téléconseillers pour chaque Superviseur
            Employee::factory(5)->create()->each(function ($tc) use ($campaign, $sup, $posTC) {
                Assignment::factory()->create([
                    'employee_id' => $tc->id,
                    'campaign_id' => $campaign->id,
                    'position_id' => $posTC->id,
                    'manager_id'  => $sup->id, // Hérite du SUP
                    'status'      => 'actif'
                ]);
            });
        });
    }
}
