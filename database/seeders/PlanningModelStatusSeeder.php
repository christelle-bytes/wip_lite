<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanningModelStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\PlanningModel::with('planningAssignment')->each(function ($model) {
            $status = $model->planningAssignment->contains('status', 'validé') ? 'actif' : 'inactif';
            $model->update(['status' => $status]);
        });
    }
}
