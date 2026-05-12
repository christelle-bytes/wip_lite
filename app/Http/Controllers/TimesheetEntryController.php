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
use Illuminate\Support\Facades\Auth;
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
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $query = Employee::query()
            ->with([
                'position',
                'timesheet' => function ($q) {
                    $q->orderBy('period_start')
                        ->with('entries');
                }
            ])
            ->whereHas('position', fn($q) => $q->where('code', 'SUP'))
            ->where('status', 'actif');

        if ($startDate && $endDate) {
            $query->whereHas(
                'timesheet',
                fn($q) =>
                $q->where('period_start', $startDate)
                    ->where('period_end', $endDate)
            );
        } else {
            $query->whereHas('timesheet');
        }

        $supervisors = $query->get();

        // === Toutes les périodes uniques ===
        $allPeriods = Employee::whereHas('position', fn($q) => $q->where('code', 'SUP'))
            ->where('status', 'actif')
            ->whereHas('timesheet')
            ->with(['timesheet:id,employee_id,period_start,period_end'])
            ->get()
            ->flatMap(
                fn($employee) =>
                $employee->timesheet->map(fn($ts) => [
                    'period_start' => $ts->period_start->format('Y-m-d'),   // ← Important
                    'period_end'   => $ts->period_end->format('Y-m-d'),     // ← Important
                    'label'        => $ts->period_start->format('d/m/Y') . ' → ' . $ts->period_end->format('d/m/Y'),
                ])
            )
            ->unique(fn($p) => $p['period_start'] . '|' . $p['period_end'])
            ->sortBy('period_start')
            ->values();

        // Filtrage des timesheets si période sélectionnée
        if ($startDate && $endDate) {
            $supervisors->each(function ($sup) use ($startDate, $endDate) {
                $filtered = $sup->timesheet->where('period_start', $startDate)
                    ->where('period_end', $endDate);
                $sup->setRelation('timesheet', $filtered);
            });
        }

        return Inertia::render('TimesheetsEntry/IndexSup', [
            'supervisors'    => $supervisors,
            'allPeriods'     => $allPeriods,
            'selectedPeriod' => $startDate && $endDate ? [
                'period_start' => $startDate,
                'period_end'   => $endDate,
                'label'        => str_replace('-', '/', $startDate) . ' → ' . str_replace('-', '/', $endDate),
            ] : null,
        ]);
    }
    // index telecon
    public function indexTelecon(Request $request)
    {
        $manager = Auth::user()->employee;
        if (!$manager) {
            // Gérer le cas où l'utilisateur n'est pas lié à un employé
            return redirect()->back()->with('error', 'Aucun profil employé lié.');
        }

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $targetMonth = $request->input('month', Carbon::now()->format('Y-m'));

        // 1. Les périodes viennent des timesheets du manager (Supervisor)
        $managerTimesheetsQuery = Timesheet::where('employee_id', $manager->id)
            ->orderBy('period_start', 'desc');

        // Récupérer toutes les périodes disponibles pour ce manager
        $allPeriods = Timesheet::where('employee_id', $manager->id)
            ->select('period_start', 'period_end')
            ->orderBy('period_start', 'desc')
            ->get()
            ->map(fn($ts) => [
                'period_start' => $ts->period_start->format('Y-m-d'),
                'period_end'   => $ts->period_end->format('Y-m-d'),
                'label'        => $ts->period_start->format('d/m/Y') . ' → ' . $ts->period_end->format('d/m/Y'),
            ])
            ->unique(fn($p) => $p['period_start'] . '|' . $p['period_end'])
            ->values();

        // Filtrer les timesheets du manager si une période est spécifiée
        if ($startDate && $endDate) {
            $managerTimesheetsQuery->where('period_start', $startDate)
                ->where('period_end', $endDate);
        }

        $managerTimesheets = $managerTimesheetsQuery->with('entries')->get();

        // 2. Récupérer les téléconseillers assignés au manager
        $teleconseillers = Employee::query()
            ->with(['position', 'assignments'])
            ->whereHas('position', fn($q) => $q->where('code', 'TC'))
            ->whereHas('assignments', function ($query) use ($manager) {
                $query->where('manager_id', $manager->id);
            })
            ->where('status', 'actif')
            ->get();

        // 3. Attacher les entrées à chaque TC via la structure "timesheet" attendue par le front
        $teleconseillers->each(function ($tc) use ($managerTimesheets) {
            $tcTimesheets = $managerTimesheets->map(function ($ts) use ($tc) {
                // On crée une version de la timesheet du manager pour ce TC
                $clonedTs = $ts->replicate();
                $clonedTs->id = $ts->id;
                $clonedTs->exists = true;

                // On ne garde que les entrées qui concernent ce TC spécifique
                $filteredEntries = $ts->entries->where('employee_id', $tc->id);
                $clonedTs->setRelation('entries', $filteredEntries->values());

                return $clonedTs;
            });

            $tc->setRelation('timesheet', $tcTimesheets);
        });

        return Inertia::render('TimesheetsEntry/IndexTelecon', [
            'telecon' => $teleconseillers,
            'allPeriods' => $allPeriods,
            'selectedPeriod' => $startDate && $endDate ? [
                'period_start' => $startDate,
                'period_end'   => $endDate,
                'label'        => str_replace('-', '/', $startDate) . ' → ' . str_replace('-', '/', $endDate),
            ] : null,
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
            ->whereHas('timesheet', function ($query) {
                $query->where('status', '!=', 'validated');
            })
            ->where('status', 'actif')
            ->get();

        return Inertia::render('TimesheetsEntry/SupEntry', [
            'superior' => $superior,
        ]);
    }

    // entry tc
    public function entryTelecon()
    {

        // $manager = Auth::user()->employee;
        // if (!$manager) {
        //     // Gérer le cas où l'utilisateur n'est pas lié à un employé
        //     return redirect()->back()->with('error', 'Aucun profil employé lié.');
        // }
        // ->whereHas('assignments', function ($query) {
        //     $query->where('manager_id', Auth::user()->employee->id);
        // })
        $manager = Auth::user()->employee;
        if (!$manager) {
            // Gérer le cas où l'utilisateur n'est pas lié à un employé
            return redirect()->route('entry.telecon')->with('error', 'Aucun profil employé lié.');
        }
        $telecon = Employee::with('position', 'assignments')
            ->whereHas('position', function ($query) {
                $query->where('code', 'TC');
            })
            ->whereHas('assignments', function ($query) use ($manager) {
                $query->where('manager_id', $manager->id);
            })
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
                ->where('status', '=', 'draft')
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
                'employee_id'   => $id,
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
        // \Log::info('Début storeTelecon - Données reçues:', $request->all());
        $validated = $request->validate([
            'sup_id'          => 'exists:employees,id',
            'tc_ids'          => 'required|array', // La liste des IDs des TCs sélectionnés
            'tc_ids.*'        => 'exists:employees,id',
            'date'            => 'required|date',
            'check_in'        => 'required',
            'check_out'       => 'required',
            'break_duration'  => 'nullable|integer',
            'comment'         => 'nullable|string',
        ]);

        // \Log::info('Données validées:', $validated);

        // 1. Récupérer l'ID du manager (superviseur) connecté
        $managerEmployeeId = $validated['sup_id'];
        // \Log::info('Manager ID:', ['manager_id' => $managerEmployeeId]);

        // 2. Filtrer les IDs envoyés pour ne garder que ceux qui sont RÉELLEMENT assignés à ce manager
        // Sécurité : évite qu'un manager injecte des IDs de TCs qui ne lui appartiennent pas.

        $authorized_tc_ids = Assignment::where('manager_id', $managerEmployeeId)
            ->whereIn('employee_id', $validated['tc_ids'])
            ->where('status', 'actif')
            ->pluck('employee_id')
            ->toArray();




        if (empty($authorized_tc_ids)) {
            return redirect()->back()->with('error', "Aucun téléconseiller valide sélectionné ou assigné.");
        }


        $entries = [];
        $startTime = Carbon::parse($validated['check_in']);
        $endTime = Carbon::parse($validated['check_out']);
        $durationInMinutes = $startTime->diffInMinutes($endTime, false);
        $breakMinutes = $validated['break_duration'] ?? 0;
        $totalHours = max(0, ($durationInMinutes - $breakMinutes) / 60);
        // $planningAssignment = PlanningAssignment::where('employee_id', $managerEmployeeId)->first();
        // $dayColumn = strtolower(Carbon::parse($validated['date'])->format('l')) . '_hours';
        //         $plannedHours = $planningAssignment->planningModel->$dayColumn ?? 0;
        // dd($plannedHours);
        foreach ($authorized_tc_ids as $tcId) {
            // Trouver la timesheet du TC pour cette date
            $timesheet = Timesheet::where('employee_id', $managerEmployeeId)
                ->where('period_start', '<=', $validated['date'])
                ->where('period_end', '>=', $validated['date'])
                ->first();
            // dd($timesheet);
            // if (!$timesheet) continue;

            // Calcul du planning/overtime
            $planningAssignment = PlanningAssignment::where('employee_id', $managerEmployeeId)->first();
            $plannedHours = 0;
            if ($planningAssignment && $planningAssignment->planningModel) {
                $dayColumn = strtolower(Carbon::parse($validated['date'])->format('l')) . '_hours';
                $plannedHours = $planningAssignment->planningModel->$dayColumn ?? 0;
            }

            $entries[] = [
                'timesheet_id'   => $timesheet->id,
                'employee_id'   => $tcId,
                'date'           => $validated['date'],
                'check_in'       => $startTime->format('H:i:s'),
                'check_out'      => $endTime->format('H:i:s'),
                'break_duration' => $breakMinutes,
                'total_hours'    => $totalHours,
                'planned_hours'  => $plannedHours,
                'overtime_hours' => ($totalHours - $plannedHours) > 0 ? ($totalHours - $plannedHours) : 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        if (!empty($entries)) {
            TimesheetEntry::insert($entries);
            return redirect()->back()->with('success', count($entries) . ' entrées générées avec succès.');
        }

        return redirect()->back()->with('error', "La date entrée ne fais pas partie de la fiche d\'heure.");
    }

    // pour permettre au teleconseiller connecté de voir les entrées éffectué
    public function myTimesheet(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return redirect()->back()->with('error', 'Aucun profil employé lié.');
        }

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // Récupérer les périodes via les timesheets du superviseur auxquelles le TC est lié
        // OU si le TC a ses propres timesheets (selon l'évolution future)
        // Ici, on cherche toutes les timesheets qui contiennent des entrées pour cet employé
        $timesheets = Timesheet::whereHas('entries', function ($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })
            ->with(['entries' => function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            }, 'validator'])
            ->orderBy('period_start', 'desc')
            ->get();

        $allPeriods = $timesheets->map(fn($ts) => [
            'period_start' => $ts->period_start->format('Y-m-d'),
            'period_end'   => $ts->period_end->format('Y-m-d'),
            'label'        => $ts->period_start->format('d/m/Y') . ' → ' . $ts->period_end->format('d/m/Y'),
        ])->unique(fn($p) => $p['period_start'] . '|' . $p['period_end'])->values();

        // Filtrage si période sélectionnée
        if ($startDate && $endDate) {
            $timesheets = $timesheets->filter(function ($ts) use ($startDate, $endDate) {
                return $ts->period_start->format('Y-m-d') === $startDate &&
                    $ts->period_end->format('Y-m-d') === $endDate;
            });
        }

        return Inertia::render('TimesheetsEntry/MyTimesheet', [
            'timesheets' => $timesheets->values(),
            'allPeriods' => $allPeriods,
            'selectedPeriod' => $startDate && $endDate ? [
                'period_start' => $startDate,
                'period_end'   => $endDate,
                'label'        => str_replace('-', '/', $startDate) . ' → ' . str_replace('-', '/', $endDate),
            ] : null,
            'employee' => $employee->load('position')
        ]);
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
