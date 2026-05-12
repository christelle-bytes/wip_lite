<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CampaignStatsExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return DB::table('campaigns')
            ->leftJoin('assignments', 'campaigns.id', '=', 'assignments.campaign_id')
            ->leftJoin('timesheets', 'assignments.employee_id', '=', 'timesheets.employee_id')
            ->leftJoin('timesheet_entries', 'timesheets.id', '=', 'timesheet_entries.timesheet_id')
            ->select(
                'campaigns.name as Campagne',
                'campaigns.status as Statut',
                'campaigns.start_date as Début',
                'campaigns.end_date as Fin',
                DB::raw('COUNT(DISTINCT assignments.employee_id) as Employes'),
                DB::raw('ROUND(SUM(timesheet_entries.total_hours), 1) as Heures_reelles'),
                DB::raw('ROUND(SUM(timesheet_entries.planned_hours), 1) as Heures_planifiees')
            )
            ->groupBy('campaigns.id', 'campaigns.name', 'campaigns.status', 'campaigns.start_date', 'campaigns.end_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Campagne', 'Statut', 'Début', 'Fin', 'Employés', 'Heures réelles', 'Heures planifiées'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
