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
            ->whereHas('employee.position', function ($query) {
                $query->where('code', 'SUP');
            })
            ->latest()
            ->get()
            ->map(function ($ts) {
                // Calcul de la durée théorique
                $start = Carbon::parse($ts->period_start);
                $end = Carbon::parse($ts->period_end);
                $joursTheoriques = $start->diffInDays($end) + 1;

                // Nombre d'entrées uniques par date (pour éviter de compter 2 fois le même jour)
                $joursSaisis = $ts->entries->pluck('date')->unique()->count();
                $isComplete = ($joursSaisis >= $joursTheoriques);

                
                // On injecte ces infos dans l'objet pour la vue
                $ts->stats = [
                    'total_jours' => $joursTheoriques,
                    'jours_saisis' => $joursSaisis,
                    'is_complete' => $isComplete,
                    'manquant' => max(0, $joursTheoriques - $joursSaisis)
                    ];
                    
                    // On ne change le statut que si la feuille n'est pas encore validée
                $newStatus = $ts->status;
                if ($ts->status !== 'validated') {
                    $newStatus = $isComplete ? 'submitted' : 'draft';
                    
                    // 4. Mise à jour en base de données si le statut a changé
                    if ($ts->status !== $newStatus) {
                        $ts->update(['status' => $newStatus]);
                    }
                }
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
            'employee_id' => 'required|array',
            'employee_id.*' => 'exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'status' => 'nullable',
        ]);
        $data = collect($validated['employee_id'])->map(function ($id) use ($validated) {
            return [
                'employee_id'  => $id,
                'period_start' => Carbon::parse($validated['period_start'])->format('Y-m-d'),
                'period_end'   => Carbon::parse($validated['period_end'])->format('Y-m-d'),
                'status'       => $validated['status'] ?? 'draft',
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        })->toArray();
        Timesheet::insert($data);
        return redirect()->route('timesheet.index');
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
