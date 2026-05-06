<?php

namespace Database\Seeders;
use App\Models\Campaign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Campaign::factory()->create([
            'name' => 'Campagne Télévente Assurance',
            'status' => 'active',
        ]);

        Campaign::factory()->create([
            'name' => 'Campagne Sondage Énergie',
            'status' => 'active',
        ]);

        // Génération de 3 campagnes aléatoires supplémentaires
        Campaign::factory(3)->create();
    }
}
