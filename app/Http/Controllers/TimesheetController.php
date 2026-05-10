<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PlanningAssignment;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Ramsey\Collection\Collection;

class TimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // les heures des chef plateau
    public function index()
    {
        // 1. Récupération de base
        $sup = Employee::with('position')->whereHas('position', function ($query) {
            $query->where('code', 'SUP');
        })->get();

        $planning = PlanningAssignment::with(['employee', 'planningModel'])
            ->whereHas('employee.position', function ($query) {
                $query->where('code', 'SUP');
            })->get();

        // 2. Récupération des feuilles avec calcul de complétude
        $timesheets = Timesheet::with(['employee', 'validator', 'entries'])
            ->latest()
            ->get()
            ->map(function ($ts) {
                // 1. Calcul des stats (en mémoire uniquement)
                $start = Carbon::parse($ts->period_start);
                $end = Carbon::parse($ts->period_end);
                $joursTheoriques = $start->diffInDays($end) + 1;
                $joursSaisis = $ts->entries->pluck('date')->unique()->count();
                $isComplete = ($joursSaisis >= $joursTheoriques);

                // 2. Mise à jour du statut SEULEMENT
                if ($ts->status !== 'validated') {
                    $newStatus = $isComplete ? 'submitted' : 'draft';

                    if ($ts->status !== $newStatus) {
                        // On spécifie uniquement la colonne 'status' pour éviter l'erreur
                        $ts->update(['status' => $newStatus]);
                    }
                }

                // 3. On injecte 'stats' APRÈS l'update
                // On utilise l'affectation directe pour que ce soit disponible pour Inertia
                $ts->stats = [
                    'total_jours' => $joursTheoriques,
                    'jours_saisis' => $joursSaisis,
                    'is_complete' => $isComplete,
                    'manquant' => max(0, $joursTheoriques - $joursSaisis)
                ];

                return $ts;
            });

        return Inertia::render('Timesheets/Index', [
            'timesheets' => $timesheets,
            'sup' => $sup,
            'planning' => $planning,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'employee_id'   => 'required|array',
        'employee_id.*' => 'exists:employees,id',
        'period_start'  => 'required|date',
        'period_end'    => 'required|date|after_or_equal:period_start',
        'status'        => 'nullable|in:draft,submitted',
    ]);

    $periodStart = Carbon::parse($validated['period_start'])->format('Y-m-d');
    $periodEnd   = Carbon::parse($validated['period_end'])->format('Y-m-d');

    $data = collect($validated['employee_id'])->map(function ($id) use ($periodStart, $periodEnd, $validated) {

        // Vérifier si une feuille existe déjà pour cet employé sur cette période
        $existing = Timesheet::where('employee_id', $id)
            ->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('period_start', [$periodStart, $periodEnd])
                      ->orWhereBetween('period_end', [$periodStart, $periodEnd])
                      ->orWhere(function ($q) use ($periodStart, $periodEnd) {
                          $q->where('period_start', '<=', $periodStart)
                            ->where('period_end', '>=', $periodEnd);
                      });
            })
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', "Une feuille de temps existe déjà pour l'employé ID {$id} sur cette période.");
        }

        return [
            'employee_id'  => $id,
            'period_start' => $periodStart,
            'period_end'   => $periodEnd,
            'status'       => $validated['status'] ?? 'draft',
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    })->toArray();

    // Supprimer les éventuels redirects qui se sont glissés dans le tableau
    $data = array_filter($data, fn($item) => is_array($item));

    if (empty($data)) {
        // dd($data);
        return redirect()->route('timesheet.index')
        ->with('error', 'Aucune feuille n\'a été créée. Merci de vérifer vos entrées');
        }

    Timesheet::insert($data);

    return redirect()->route('timesheet.index')
        ->with('success', 'Feuille(s) d\'heures créée(s) avec succès.');
}



    /**
     * Display the specified resource.
     */
    public function show(Timesheet $timesheet)
    {
        $timesheet->load(['employee']);
        return inertia('Timesheets/Show', ['timesheet' => $timesheet]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Timesheet $timesheet)
    {
        $timesheet->update($request->only(['status', 'validated_by', 'validated_at']));
        return redirect()->route('timesheet.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
