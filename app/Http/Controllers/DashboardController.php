<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $roleName = strtoupper($user?->role?->name ?? '');

        return match ($roleName) {
            'ADMIN' => $this->adminDashboard(),
            'CP'    => $this->cpDashboard(),
            'SUP'   => $this->supDashboard(),
            'TC'    => $this->tcDashboard(),
            default => $this->adminDashboard(),
        };
    }

    // ─── ADMIN ───────────────────────────────────────────────────────────────

    private function adminDashboard()
    {
        // KPIs
        $totalEmployees   = DB::table('employees')->count();
        $activeCampaigns  = DB::table('campaigns')->where('status', 'active')->count();
        $totalUsers       = DB::table('users')->count();
        $totalAssignments = DB::table('assignments')->count();
        $totalRealHours   = DB::table('timesheet_entries')->sum('total_hours') ?? 0;
        $plannedHours     = DB::table('timesheet_entries')->sum('planned_hours') ?? 0;
        
        $gap = $plannedHours > 0
            ? round((($totalRealHours - $plannedHours) / $plannedHours) * 100, 1)
            : 0;

        // Graphe 1 : campagnes créées par mois (12 derniers mois)
        $campaignsByMonth = DB::table('campaigns')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Graphe 2 : évolution des employés actifs par mois (date created_at)
        $employeesByMonth = DB::table('employees')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('DashboardAdmin', [
            'stats' => [
                'totalEmployees'   => $totalEmployees,
                'activeCampaigns'  => $activeCampaigns,
                'totalUsers'       => $totalUsers,
                'totalAssignments' => $totalAssignments,
                'totalHours'       => round($totalRealHours, 1),
                'gap'              => $gap,
            ],
            'charts' => [
                'campaignsByMonth'  => $campaignsByMonth,
                'employeesByMonth'  => $employeesByMonth,
            ],
        ]);
    }

    // ─── CP ──────────────────────────────────────────────────────────────────

    private function cpDashboard()
    {
        // KPIs
        $activeCampaigns  = DB::table('campaigns')->where('status', 'active')->count();
        $totalAssignments = DB::table('assignments')->count();
        $totalEmployees   = DB::table('employees')->count();
        $totalRealHours   = DB::table('timesheet_entries')->sum('total_hours') ?? 0;
        $plannedHours     = DB::table('timesheet_entries')->sum('planned_hours') ?? 0;
        $gap = $plannedHours > 0
            ? round((($totalRealHours - $plannedHours) / $plannedHours) * 100, 1)
            : 0;

        // Graphe 1 : taux de présence par employé
        // présence = entrées avec check_in non null / total entrées
        $presenceByEmployee = DB::table('timesheet_entries')
            ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
            ->join('employees', 'timesheets.employee_id', '=', 'employees.id')
            ->select(
                DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as name"),
                DB::raw('COUNT(*) as total_days'),
                DB::raw('SUM(CASE WHEN timesheet_entries.check_in IS NOT NULL THEN 1 ELSE 0 END) as present_days')
            )
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
            ->orderByDesc('present_days')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                $row->presence_rate = $row->total_days > 0
                    ? round(($row->present_days / $row->total_days) * 100, 1)
                    : 0;
                return $row;
            });

        // Graphe 2 : heures réelles vs planifiées par employé
        $performanceByEmployee = DB::table('timesheet_entries')
            ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
            ->join('employees', 'timesheets.employee_id', '=', 'employees.id')
            ->select(
                DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as name"),
                DB::raw('SUM(timesheet_entries.total_hours) as real_hours'),
                DB::raw('SUM(timesheet_entries.planned_hours) as planned_hours')
            )
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
            ->orderByDesc('real_hours')
            ->limit(8)
            ->get();

        return Inertia::render('DashboardCp', [
            'stats' => [
                'activeCampaigns'  => $activeCampaigns,
                'totalAssignments' => $totalAssignments,
                'totalEmployees'   => $totalEmployees,
                'totalHours'       => round($totalRealHours, 1),
                'gap'              => $gap,
            ],
            'charts' => [
                'presenceByEmployee'    => $presenceByEmployee,
                'performanceByEmployee' => $performanceByEmployee,
            ],
        ]);
    }

    // ─── SUP ─────────────────────────────────────────────────────────────────

    private function supDashboard()
    {
        // KPIs
        $totalEmployees   = DB::table('employees')->count();
        $activeCampaigns  = DB::table('campaigns')->where('status', 'active')->count();
        $totalAssignments = DB::table('assignments')->count();
        $totalRealHours   = DB::table('timesheet_entries')->sum('total_hours') ?? 0;
        $plannedHours     = DB::table('timesheet_entries')->sum('planned_hours') ?? 0;
        $gap = $plannedHours > 0
            ? round((($totalRealHours - $plannedHours) / $plannedHours) * 100, 1)
            : 0;

        // Timesheets en attente de validation
        $pendingTimesheets = DB::table('timesheets')->where('status', 'submitted')->count();

        // Graphe 1 : écart heures réelles vs planifiées par employé
        $gapByEmployee = DB::table('timesheet_entries')
            ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
            ->join('employees', 'timesheets.employee_id', '=', 'employees.id')
            ->select(
                DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as name"),
                DB::raw('SUM(timesheet_entries.total_hours) as real_hours'),
                DB::raw('SUM(timesheet_entries.planned_hours) as planned_hours'),
                DB::raw('SUM(timesheet_entries.total_hours) - SUM(timesheet_entries.planned_hours) as gap_hours')
            )
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
            ->orderBy('gap_hours')
            ->limit(8)
            ->get();

        // Graphe 2 : évolution des heures par mois (6 derniers mois)
        $hoursByMonth = DB::table('timesheet_entries')
            ->select(
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
                DB::raw('SUM(total_hours) as real_hours'),
                DB::raw('SUM(planned_hours) as planned_hours')
            )
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('DashboardSup', [
            'stats' => [
                'totalEmployees'   => $totalEmployees,
                'activeCampaigns'  => $activeCampaigns,
                'totalAssignments' => $totalAssignments,
                'totalHours'       => round($totalRealHours, 1),
                'gap'              => $gap,
                'pendingTimesheets' => $pendingTimesheets,
            ],
            'charts' => [
                'gapByEmployee' => $gapByEmployee,
                'hoursByMonth'  => $hoursByMonth,
            ],
        ]);
    }

    // ─── TC ──────────────────────────────────────────────────────────────────

    private function tcDashboard()
    {
        $user     = Auth::user();
        $employee = DB::table('employees')->where('user_id', $user->id)->first();

        $myAssignments = 0;
        $myHours       = 0;
        $myPlanned     = 0;
        $activeCampaigns = [];
        $weekPlanning    = [];
        $hoursEvolution  = [];

        if ($employee) {
            $myAssignments = DB::table('assignments')
                ->where('employee_id', $employee->id)
                ->where('status', 'actif')
                ->count();

            $myHours = DB::table('timesheet_entries')
                ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
                ->where('timesheets.employee_id', $employee->id)
                ->sum('timesheet_entries.total_hours') ?? 0;

            $myPlanned = DB::table('timesheet_entries')
                ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
                ->where('timesheets.employee_id', $employee->id)
                ->sum('timesheet_entries.planned_hours') ?? 0;

            // Campagnes actives de ce TC
            $activeCampaigns = DB::table('assignments')
                ->join('campaigns', 'assignments.campaign_id', '=', 'campaigns.id')
                ->where('assignments.employee_id', $employee->id)
                ->where('assignments.status', 'actif')
                ->select('campaigns.name', 'campaigns.status', 'assignments.start_date', 'assignments.end_date')
                ->get();

            // Planning de la semaine : heures par jour cette semaine
            $weekPlanning = DB::table('timesheet_entries')
                ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
                ->where('timesheets.employee_id', $employee->id)
                ->whereBetween('timesheet_entries.date', [now()->startOfWeek(), now()->endOfWeek()])
                ->select(
                    DB::raw("DATE_FORMAT(timesheet_entries.date, '%a') as day"),
                    DB::raw('SUM(timesheet_entries.total_hours) as real_hours'),
                    DB::raw('SUM(timesheet_entries.planned_hours) as planned_hours')
                )
                ->groupBy('day', 'timesheet_entries.date')
                ->orderBy('timesheet_entries.date')
                ->get();

            // Évolution de mes heures sur 6 mois
            $hoursEvolution = DB::table('timesheet_entries')
                ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
                ->where('timesheets.employee_id', $employee->id)
                ->where('timesheet_entries.date', '>=', now()->subMonths(6))
                ->select(
                    DB::raw("DATE_FORMAT(timesheet_entries.date, '%Y-%m') as month"),
                    DB::raw('SUM(timesheet_entries.total_hours) as real_hours'),
                    DB::raw('SUM(timesheet_entries.planned_hours) as planned_hours')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        $gap = $myPlanned > 0
            ? round((($myHours - $myPlanned) / $myPlanned) * 100, 1)
            : 0;

        return Inertia::render('DashboardTc', [
            'stats' => [
                'myAssignments' => $myAssignments,
                'myHours'       => round($myHours, 1),
                'myPlanned'     => round($myPlanned, 1),
                'gap'           => $gap,
            ],
            'charts' => [
                'activeCampaigns' => $activeCampaigns,
                'weekPlanning'    => $weekPlanning,
                'hoursEvolution'  => $hoursEvolution,
            ],
        ]);
    }
}
