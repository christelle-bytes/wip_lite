<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CampaignStatsExport;
use Barryvdh\DomPDF\Facade\Pdf;


class CampaignStatsController extends Controller
{
    public function index()
    {
        // Pour chaque campagne : employés affectés, heures réelles, heures planifiées
        $campaigns = DB::table('campaigns')
            ->leftJoin('assignments', 'campaigns.id', '=', 'assignments.campaign_id')
            ->leftJoin('timesheets', 'assignments.employee_id', '=', 'timesheets.employee_id')
            ->leftJoin('timesheet_entries', 'timesheets.id', '=', 'timesheet_entries.timesheet_id')
            ->select(
                'campaigns.id',
                'campaigns.name',
                'campaigns.status',
                'campaigns.start_date',
                'campaigns.end_date',
                DB::raw('COUNT(DISTINCT assignments.employee_id) as total_employees'),
                DB::raw('SUM(timesheet_entries.total_hours) as real_hours'),
                DB::raw('SUM(timesheet_entries.planned_hours) as planned_hours')
            )
            ->groupBy('campaigns.id', 'campaigns.name', 'campaigns.status', 'campaigns.start_date', 'campaigns.end_date')
            ->orderByDesc('campaigns.created_at')
            ->get()
            ->map(function ($c) {
                // Calcul de l'écart en %
                $c->gap = $c->planned_hours > 0
                    ? round((($c->real_hours - $c->planned_hours) / $c->planned_hours) * 100, 1)
                    : 0;
                $c->real_hours    = round($c->real_hours ?? 0, 1);
                $c->planned_hours = round($c->planned_hours ?? 0, 1);
                return $c;
            });

        return Inertia::render('Campaigns/CampaignStats', [
            'campaigns' => $campaigns,
        ]);
    }

    // Télécharge le fichier Excel
    public function export()
    {
        return Excel::download(new CampaignStatsExport, 'statistiques-campagnes.xlsx');
    }



//pdf
    public function exportPdf()
{
    $campaigns = DB::table('campaigns')
        ->leftJoin('assignments', 'campaigns.id', '=', 'assignments.campaign_id')
        ->leftJoin('timesheets', 'assignments.employee_id', '=', 'timesheets.employee_id')
        ->leftJoin('timesheet_entries', 'timesheets.id', '=', 'timesheet_entries.timesheet_id')
        ->select(
            'campaigns.name',
            'campaigns.status',
            DB::raw('COUNT(DISTINCT assignments.employee_id) as total_employees'),
            DB::raw('ROUND(SUM(timesheet_entries.total_hours), 1) as real_hours'),
            DB::raw('ROUND(SUM(timesheet_entries.planned_hours), 1) as planned_hours')
        )
        ->groupBy('campaigns.id', 'campaigns.name', 'campaigns.status')
        ->get()
        ->map(function ($c) {
            $c->gap = $c->planned_hours > 0
                ? round((($c->real_hours - $c->planned_hours) / $c->planned_hours) * 100, 1)
                : 0;
            return $c;
        });

    $pdf = Pdf::loadView('exports.campaigns-pdf', ['campaigns' => $campaigns]);

    return $pdf->download('statistiques-campagnes.pdf');
}

}
