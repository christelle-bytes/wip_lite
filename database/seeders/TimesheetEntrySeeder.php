<?php

namespace Database\Seeders;

use App\Models\TimesheetEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimesheetEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timesheets = \App\Models\Timesheet::all();

        if ($timesheets->isEmpty()) {
            return;
        }

        foreach ($timesheets as $timesheet) {
            // Créer 5 entrées pour chaque feuille de temps (ex: du lundi au vendredi)
            for ($i = 0; $i < 5; $i++) {
                TimesheetEntry::factory()->create([
                    'timesheet_id' => $timesheet->id,
                    'employee_id'  => $timesheet->employee_id,
                    'date'         => $timesheet->period_start->copy()->addDays($i),
                ]);
            }
        }
    }
}
