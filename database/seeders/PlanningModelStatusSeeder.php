<?php

namespace Database\Seeders;

use App\Models\PlanningModel;
use Illuminate\Database\Seeder;

class PlanningModelStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlanningModel::with('planningAssignment')->each(function ($model) {
            $status = $model->planningAssignment->contains('status', 'validé') ? 'actif' : 'inactif';
            $model->update(['status' => $status]);
        });
    }
}
