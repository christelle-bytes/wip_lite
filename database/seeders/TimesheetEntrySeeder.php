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
        TimesheetEntry::factory(12)->create();
    }
}
