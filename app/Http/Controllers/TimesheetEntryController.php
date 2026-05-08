<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\PlanningAssignment;
use App\Models\PlanningModel;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use phpDocumentor\Reflection\Types\Null_;

class TimesheetEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // index sup
    public function indexSup(Request $request)
    {
        // Optionnel : Récupérer le mois depuis la requête (ex: 2026-05)
        // Si vide, on peut décider de prendre le mois en cours ou tous les mois
        $targetMonth = $request->input('month', Carbon::now()->format('Y-m'));

        $supervisor = Employee::with([
            'position',
            'timesheet' => function ($query) use ($targetMonth) {
                // On peut filtrer les feuilles de temps qui chevauchent le mois
                $query->with(['entries' => function ($entryQuery) use ($targetMonth) {
                    // On filtre les entrées précises pour le mois choisi
                    $entryQuery->where('date', 'like', "$targetMonth%");
                }]);
            }
        ])
            ->whereHas('position', function ($query) {
                $query->where('code', 'SUP');
            })
            ->whereHas('timesheet')
            ->where('status', 'actif')
            ->get();

        return Inertia::render('TimesheetsEntry/IndexSup', [
            'supervisor' => $supervisor,
            'currentMonth' => $targetMonth
        ]);
    }

    // index telecon
    public function indexTelecon(Request $request)
    {
        $targetMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $telecon = Employee::with('position', 'timesheet')
            ->whereHas('position', function ($query) use ($targetMonth) {
                
                $query->with(['entries' => function ($entryQuery) use ($targetMonth) {
                    // On filtre les entrées précises pour le mois choisi
                    $entryQuery->where('date', 'like', "$targetMonth%");
                }])->where('code', 'TC');
            })
            ->whereHas('timesheet')
            ->where('status', 'actif')
            ->get();

        return Inertia::render('TimesheetsEntry/IndexTelecon', [
            'telecon' => $telecon,
            'currentMonth' => $targetMonth
        ]);
    }

    // entry sup
    public function entrySup()
    {
        $superior = Employee::with('position', 'timesheet')
            ->whereHas('position', function ($query) {
                $query->where('code', 'SUP');
            })
            ->whereHas('timesheet')
            ->where('status', 'actif')
            ->get();

        return Inertia::render('TimesheetsEntry/SupEntry', [
            'superior' => $superior,
        ]);
    }

    // entry tc
    public function entryTelecon()
    {
        $telecon = Employee::with('position', 'assignments')
            ->whereHas('position', function ($query) {
                $query->where('code', 'TC');
            })
            ->whereHas('assignments')
            ->where('status', 'actif')
            ->get();

        return Inertia::render('TimesheetsEntry/TeleconEntry', [
            'telecon' => $telecon,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'employee_ids'    => 'required|array',
            'employee_ids.*'  => 'exists:employees,id',
            'date'           => 'required|date',
            'check_in'       => 'nullable',
            'check_out'      => 'nullable',
            'break_duration' => 'nullable|integer',
            'absence_type'   => 'nullable|string',
            'comment'        => 'nullable|string',
        ]);

        $entries = [];
        foreach ($validated['employee_ids'] as $id) {
            // 1. Récupérer la timesheet active pour cet employé à cette date
            $timesheet = Timesheet::where('employee_id', $id)
                ->where('period_start', '<=', $validated['date'])
                ->where('period_end', '>=', $validated['date'])
                ->first();

            if (!$timesheet) continue;

            $startTime = Carbon::parse($validated['check_in']);
            $endTime = Carbon::parse($validated['check_out']);

            // Calcul de la durée en minutes, moins la pause
            $durationInMinutes = $startTime->diffInMinutes($endTime, false);
            $breakMinutes = $validated['break_duration'] ?? 0;

            // Conversion en heures décimales (ex: 8.5)
            $totalHours = max(0, ($durationInMinutes - $breakMinutes) / 60);

            // 3. Récupérer le planning de l'employé
            $planningAssignment = PlanningAssignment::where('employee_id', $id)->first();

            $plannedHours = 0;
            if ($planningAssignment && $planningAssignment->planningModel) {
                $dayColumn = strtolower(Carbon::parse($validated['date'])->format('l')) . '_hours';
                $plannedHours = $planningAssignment->planningModel->$dayColumn ?? 0;
            }

            // 4. Calcul de l'overtime
            $diff = $totalHours - $plannedHours;
            $overtime = $diff > 0 ? $diff : 0;


            $entries[] = [
                'timesheet_id'   => $timesheet->id,
                'date'           => Carbon::parse($validated['date'])->format('Y-m-d'),
                'check_in'       => Carbon::parse($startTime)->format('H:m:i'),
                'check_out'      => Carbon::parse($endTime)->format('H:m:i'),
                'break_duration' => $validated['break_duration'] ?? 0,
                'total_hours'    => $totalHours,
                'planned_hours'  => $plannedHours,
                'overtime_hours' => $overtime,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
        if (!empty($entries)) {
            TimesheetEntry::insert($entries);
        }
        return redirect()->back()->with('success', 'Entrées enregistrées.');
    }

    // saisie des heures d'un supervisor
    public function storeSup(Request $request)
    {

        $validated = $request->validate([
            'employee_ids'    => 'required|array',
            'employee_ids.*'  => 'exists:employees,id',
            'date'           => 'required|date',
            'check_in'       => 'nullable',
            'check_out'      => 'nullable',
            'break_duration' => 'nullable|integer',
            'absence_type'   => 'nullable|string',
            'comment'        => 'nullable|string',
        ]);

        $entries = [];
        foreach ($validated['employee_ids'] as $id) {
            // 1. Récupérer la timesheet active pour cet employé à cette date
            $timesheet = Timesheet::where('employee_id', $id)
                ->where('period_start', '<=', $validated['date'])
                ->where('period_end', '>=', $validated['date'])
                ->first();

            if (!$timesheet) continue;

            $startTime = Carbon::parse($validated['check_in']);
            $endTime = Carbon::parse($validated['check_out']);

            // Calcul de la durée en minutes, moins la pause
            $durationInMinutes = $startTime->diffInMinutes($endTime, false);
            $breakMinutes = $validated['break_duration'] ?? 0;

            // Conversion en heures décimales (ex: 8.5)
            $totalHours = max(0, ($durationInMinutes - $breakMinutes) / 60);

            // 3. Récupérer le planning de l'employé
            $planningAssignment = PlanningAssignment::where('employee_id', $id)->first();

            $plannedHours = 0;
            if ($planningAssignment && $planningAssignment->planningModel) {
                $dayColumn = strtolower(Carbon::parse($validated['date'])->format('l')) . '_hours';
                $plannedHours = $planningAssignment->planningModel->$dayColumn ?? 0;
            }

            // 4. Calcul de l'overtime
            $diff = $totalHours - $plannedHours;
            $overtime = $diff > 0 ? $diff : 0;


            $entries[] = [
                'timesheet_id'   => $timesheet->id,
                'date'           => Carbon::parse($validated['date'])->format('Y-m-d'),
                'check_in'       => Carbon::parse($startTime)->format('H:m:i'),
                'check_out'      => Carbon::parse($endTime)->format('H:m:i'),
                'break_duration' => $validated['break_duration'] ?? 0,
                'total_hours'    => $totalHours,
                'planned_hours'  => $plannedHours,
                'overtime_hours' => $overtime,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
        if (!empty($entries)) {
            TimesheetEntry::insert($entries);
            return redirect()->back()->with('success', 'Entrées enregistrées.');
        }
        return redirect()->back()->with('error', 'La date entrée ne fais pas partie de la fiche d\'heure.');
    }

    // saisie des heures d'un teleconseiller
    public function storeTelecon(Request $request)
    {
        $validated = $request->validate([
            'employee_ids'    => 'required|array', // Ici, ce sont les IDs des Superviseurs sélectionnés
            'employee_ids.*'  => 'exists:employees,id',
            'date'            => 'required|date',
            'check_in'        => 'required',
            'check_out'       => 'required',
            'break_duration'  => 'nullable|integer',
            'absence_type'    => 'nullable|string',
            'comment'         => 'nullable|string',
        ]);

        // 1. Récupérer tous les téléconseillers liés à ces superviseurs via la table Assignment
        // On cherche les assignments où le manager_id est dans la liste des SUP sélectionnés
        $telecon_ids = Assignment::whereIn('manager_id', $validated['employee_ids'])
            ->where('status', 'actif') // Optionnel: uniquement ceux qui sont actifs
            ->pluck('employee_id')
            ->unique()
            ->toArray();

        if (empty($telecon_ids)) {
            return redirect()->back()->with('error', 'Aucun téléconseiller trouvé pour ces superviseurs.');
        }

        $entries = [];
        $startTime = Carbon::parse($validated['check_in']);
        $endTime = Carbon::parse($validated['check_out']);

        // Calcul de la durée commune
        $durationInMinutes = $startTime->diffInMinutes($endTime, false);
        $breakMinutes = $validated['break_duration'] ?? 0;
        $totalHours = max(0, ($durationInMinutes - $breakMinutes) / 60);

        // 2. Boucler sur les Téléconseillers (TC) au lieu des superviseurs
        foreach ($telecon_ids as $tcId) {

            // Trouver la timesheet du TC pour cette date
            $timesheet = Timesheet::where('employee_id', $tcId)
                ->where('period_start', '<=', $validated['date'])
                ->where('period_end', '>=', $validated['date'])
                ->first();

            if (!$timesheet) continue;

            // 3. Récupérer le planning spécifique du TC
            $planningAssignment = PlanningAssignment::where('employee_id', $tcId)->first();
            $plannedHours = 0;

            if ($planningAssignment && $planningAssignment->planningModel) {
                $dayColumn = strtolower(Carbon::parse($validated['date'])->format('l')) . '_hours';
                $plannedHours = $planningAssignment->planningModel->$dayColumn ?? 0;
            }

            $diff = $totalHours - $plannedHours;
            $overtime = $diff > 0 ? $diff : 0;

            $entries[] = [
                'timesheet_id'   => $timesheet->id,
                'date'           => $validated['date'],
                'check_in'       => $startTime->format('H:i:s'),
                'check_out'      => $endTime->format('H:i:s'),
                'break_duration' => $breakMinutes,
                'total_hours'    => $totalHours,
                'planned_hours'  => $plannedHours,
                'overtime_hours' => $overtime,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        if (!empty($entries)) {
            TimesheetEntry::insert($entries);
            return redirect()->back()->with('success', count($entries) . ' entrées générées pour les téléconseillers.');
        }
        return redirect()->back()->with('error', 'La date entrée ne fais pas partie de la fiche d\'heure.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
