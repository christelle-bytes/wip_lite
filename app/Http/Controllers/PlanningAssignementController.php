<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PlanningAssignment;
use App\Models\PlanningModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanningAssignementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if ($user->hasRole('Admin') || $user->hasRole('CP')) {
            $planningModels = PlanningModel::all();
            $assignments = PlanningAssignment::with(['employee', 'planningModel'])->get();
        } else {
            $planningModels = PlanningModel::all(); // On suppose que les modèles sont visibles par tous
            $assignments = $employee ? $employee->planningAssignments()->with('planningModel')->get() : collect();
        }

        // Groupement par statut
        $groupedAssignments = $assignments->groupBy('status');

        return Inertia::render('planning/Index', [
            'planningModels' => $planningModels,
            'assignments'    => $groupedAssignments,
            'allAssignments' => $assignments // Au cas où la vue en a besoin non groupé
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'planning_model_id' => 'required|exists:planning_models,id',
            'employee_id'       => 'required|exists:employees,id',
            'start_date'        => 'required|date',
        ]);

        $validated['status'] = 'en attente';

        PlanningAssignment::create($validated);

        return redirect()->back()->with('success', 'Assignation créée avec succès.');
    }

    public function update(Request $request, PlanningAssignment $planningAssignment)
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        if ($planningAssignment->status !== 'en attente') {
            abort(403, 'Modification impossible : l\'assignation n\'est plus en attente.');
        }

        $validated = $request->validate([
            'planning_model_id' => 'required|exists:planning_models,id',
            'employee_id'       => 'required|exists:employees,id',
            'start_date'        => 'required|date',
        ]);

        $planningAssignment->update($validated);

        return redirect()->back()->with('success', 'Assignation mise à jour avec succès.');
    }

    public function destroy(PlanningAssignment $planningAssignment)
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        if ($planningAssignment->status !== 'en attente') {
            abort(403, 'Suppression impossible : l\'assignation n\'est plus en attente.');
        }

        $planningAssignment->delete();

        return redirect()->back()->with('success', 'Assignation supprimée avec succès.');
    }

    public function changeStatus(Request $request, PlanningAssignment $planningAssignment)
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:en attente,validé,suspendu,terminé'
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'validé') {
            $updateData['validated_by'] = auth()->user()->employee->id;
            $updateData['validated_at'] = now();
        }

        $planningAssignment->update($updateData);

        return redirect()->back()->with('success', 'Statut mis à jour avec succès.');
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

    public function affectation()
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        $planningModels = PlanningModel::all();
        $employees = Employee::all();
        $assignments = PlanningAssignment::with(['employee', 'planningModel'])->get();

        return Inertia::render('planning/Affectation', [
            'planningModels' => $planningModels,
            'employees' => $employees,
            'assignments' => $assignments,
        ]);
    }

    public function validation()
    {
        if (!auth()->user()->hasRole('CP') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        $assignments = PlanningAssignment::with(['employee', 'planningModel'])->get();

        return Inertia::render('planning/Validation', [
            'assignments' => $assignments
        ]);
    }
}
