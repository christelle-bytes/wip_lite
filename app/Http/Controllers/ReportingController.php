<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;




class ReportingController extends Controller
{
    public function index()
    {
        // Compteurs simples
        $totalEmployees  = DB::table('employees')->count();
        $activeCampaigns = DB::table('campaigns')->where('status', 'active')->count();
        $totalRealHours  = DB::table('timesheet_entries')->sum('total_hours') ?? 0;
        $plannedHours    = DB::table('timesheet_entries')->sum('planned_hours') ?? 0;

        // Calcul de l'écart en %
        $gap = 0;
        if ($plannedHours > 0) {
            $gap = (($totalRealHours - $plannedHours) / $plannedHours) * 100;
        }



        
        // Répartition des utilisateurs par rôle (pour le graphique)
        $rolesChart = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.name as label', DB::raw('count(*) as value'))
            ->groupBy('roles.name')
            ->get();

        return Inertia::render('Reporting', [
            'stats' => [
                'totalEmployees'  => $totalEmployees,
                'activeCampaigns' => $activeCampaigns,
                'totalHours'      => round($totalRealHours, 1),
                'gap'             => round($gap, 1),
            ],
            'rolesChart' => $rolesChart,
        ]);
    }
}
