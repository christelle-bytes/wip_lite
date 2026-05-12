<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PlanningAssignment;
use App\Models\PlanningModel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class PlanningAssignementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee;

        // Récupérer "Mon Planning" (les assignations personnelles de l'utilisateur)
        $myAssignments = $employee 
            ? $employee->planningAssignments()->with(['planningModel', 'employee.user.role'])->get() 
            : collect();

        // Si l'utilisateur est Admin ou CP, il voit les modèles et toutes les assignations
        if ($user->hasRole('Admin') || $user->hasRole('CP')) {
            $planningModels = PlanningModel::all();
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
            'planning_model_id' => 'required|exists:planning_models,id',
            'employee_ids'      => 'nullable|array|min:1',
            'employee_ids.*'    => 'required_with:employee_ids|exists:employees,id',
            'employee_id'       => 'nullable|exists:employees,id',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after:start_date',
        ]);

        $employeeIds = Arr::wrap($validated['employee_ids'] ?? $validated['employee_id'] ?? []);

        if (empty($employeeIds)) {
            return redirect()->back()->withErrors(['employee_ids' => 'Veuillez sélectionner au moins un employé.']);
        }

        $now = now();

        $assignmentsData = array_map(function ($employeeId) use ($validated, $now) {
            return [
                'planning_model_id' => $validated['planning_model_id'],
                'employee_id'       => $employeeId,
                'start_date'        => $validated['start_date'],
                'end_date'          => $validated['end_date'] ?? null,
                'status'            => 'en attente',
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }, $employeeIds);

        PlanningAssignment::insert($assignmentsData);

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
            'end_date'          => 'nullable|date|after:start_date',
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
            $updateData['validated_by'] = auth()->user()->employee?->id;
            $updateData['validated_at'] = now();
        }

        $planningAssignment->update($updateData);

        // Mettre à jour le statut du modèle
        $model = $planningAssignment->planningModel;
        if ($validated['status'] === 'validé') {
            $model->update(['status' => 'actif']);
        } elseif (in_array($validated['status'], ['suspendu', 'terminé'])) {
            // Vérifier si il reste des assignations validées
            $hasValidated = $model->planningAssignment()->where('status', 'validé')->exists();
            if (!$hasValidated) {
                $model->update(['status' => 'inactif']);
            }
        }

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
        $user = auth()->user();
        if (!$user->hasRole('CP') && !$user->hasRole('Admin')) {
            abort(403, 'Action non autorisée.');
        }

        $planningModels = PlanningModel::when($user->hasRole('CP'), function ($query) use ($user) {
            return $query->where('created_by', $user->employee->id);
        })->get();
        
        // Filtrage des employés en fonction du rôle
        $query = Employee::query()->with('user.role');

        if ($user->hasRole('Admin')) {
            // L'admin voit les CP et les SUP
            $query->whereHas('user.role', function ($q) {
                $q->whereIn('name', ['CP', 'SUP']);
            });
        } elseif ($user->hasRole('CP')) {
            // Le CP ne voit que les SUP
            $query->whereHas('user.role', function ($q) {
                $q->where('name', 'SUP');
            });
        }

        $employees = $query->get()->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => "[{$emp->user->role->name}] {$emp->first_name} {$emp->last_name}"
            ];
        });

        $assignments = PlanningAssignment::with(['employee.user.role', 'planningModel'])->get();

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

        $assignments = PlanningAssignment::with(['employee.user.role', 'planningModel'])->get();

        return Inertia::render('planning/Validation', [
            'assignments' => $assignments
        ]);
    }
}
