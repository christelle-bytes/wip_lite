<?php

namespace App\Http\Controllers;

use App\Models\PlanningAssignment;
use App\Models\PlanningModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanningModelController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee;

        // Récupérer "Mon Planning" (les assignations personnelles de l'utilisateur)
        $myAssignments = $employee 
            ? $employee->planningAssignments()->with(['planningModel', 'employee.user.role'])->get() 
            : collect();

        // Si l'utilisateur est Admin, il voit tous les modèles.
        // Si c'est un CP, il ne voit que les modèles qu'il a créés.
        if ($user->hasRole('Admin') || $user->hasRole('CP')) {
            $planningModels = PlanningModel::when($user->hasRole('CP'), function ($query) use ($user) {
                return $query->where('created_by', $user->employee->id);
            })->withCount(['planningAssignment as active_assignment_count' => function ($query) {
                $query->whereIn('status', ['validé', 'suspendu']);
            }])->get();

            $assignments = PlanningAssignment::with(['employee.user.role', 'planningModel'])->get();
        } else {
            // Les SUP et TC ne voient pas les modèles globaux ni les autres assignations
            $planningModels = collect();
            $assignments = collect();
        }

        // Groupement par statut pour les assignations générales (uniquement pour Admin/CP)
        $groupedAssignments = $assignments->groupBy('status');

        return Inertia::render('planning/Index', [
            'planningModels' => $planningModels,
            'assignments'    => $groupedAssignments,
            'allAssignments' => $assignments,
            'myAssignments'  => $myAssignments 
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'monday_hours'    => 'required|numeric|min:0|max:24',
            'tuesday_hours'   => 'required|numeric|min:0|max:24',
            'wednesday_hours' => 'required|numeric|min:0|max:24',
            'thursday_hours'  => 'required|numeric|min:0|max:24',
            'friday_hours'    => 'required|numeric|min:0|max:24',
            'saturday_hours'  => 'required|numeric|min:0|max:24',
            'sunday_hours'    => 'required|numeric|min:0|max:24',
        ]);

        $validated['total_hours'] = array_sum([
            $validated['monday_hours'],
            $validated['tuesday_hours'],
            $validated['wednesday_hours'],
            $validated['thursday_hours'],
            $validated['friday_hours'],
            $validated['saturday_hours'],
            $validated['sunday_hours'],
        ]);

        
        $validated['created_by'] = auth()->user()->employee->id;

        PlanningModel::create($validated);

        return redirect()->route('planning.index')
            ->with('success', 'Planning créé avec succès.');
    }

    public function destroy(PlanningModel $planningModel)
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        // Vérifier qu'il n'y a pas d'assignments actifs (validé ou suspendu)
        $hasActive = $planningModel->planningAssignment()
            ->whereIn('status', ['validé', 'suspendu'])
            ->exists();

        if ($hasActive) {
            abort(403, 'Suppression impossible : le planning a des assignments actifs.');
        }

        $planningModel->delete();

        return redirect()->back()->with('success', 'Planning supprimé avec succès.');
    }

    public function suspend(PlanningModel $planningModel)
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        // Suspendre tous les assignments validés du planning
        $planningModel->planningAssignment()
            ->where('status', 'validé')
            ->update(['status' => 'suspendu']);

        return redirect()->back()->with('success', 'Planning suspendu avec succès.');
    }

    public function update(Request $request, PlanningModel $planningModel)
{
    if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
        abort(403, 'Action non autorisée.');
    }

    $hasNonPending = $planningModel->planningAssignment()
        ->where('status', '!=', 'en attente')
        ->exists();

    if ($hasNonPending) {
        abort(403, 'Modification impossible : des assignments ne sont pas en attente.');
    }

    $validated = $request->validate([
        'name'            => 'required|string|max:255',
        'description'     => 'nullable|string',
        'monday_hours'    => 'required|numeric|min:0|max:24',
        'tuesday_hours'   => 'required|numeric|min:0|max:24',
        'wednesday_hours' => 'required|numeric|min:0|max:24',
        'thursday_hours'  => 'required|numeric|min:0|max:24',
        'friday_hours'    => 'required|numeric|min:0|max:24',
        'saturday_hours'  => 'required|numeric|min:0|max:24',
        'sunday_hours'    => 'required|numeric|min:0|max:24',
    ]);

    $validated['total_hours'] = array_sum([
        $validated['monday_hours'],
        $validated['tuesday_hours'],
        $validated['wednesday_hours'],
        $validated['thursday_hours'],
        $validated['friday_hours'],
        $validated['saturday_hours'],
        $validated['sunday_hours'],
    ]);

    $planningModel->update($validated);

    return redirect()->route('planning.index')
        ->with('success', 'Planning mis à jour avec succès.');
}
}
