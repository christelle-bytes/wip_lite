<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $user = Auth::user();
        // if (!$user || !$user->isAdmin()) {
        //     abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        // }

        $assignments = Assignment::with(['employee.user', 'campaign', 'position', 'manager'])->get();
        $employees = Employee::with('user')->get();
        $campaigns = Campaign::all();
        $positions = Position::all();

        return Inertia::render('Assignments/Index', [
            'assignments' => $assignments,
            'employees' => $employees,
            'campaigns' => $campaigns,
            'positions' => $positions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        }

        $employees = Employee::with('user')->get();
        $campaigns = Campaign::all();
        $positions = Position::all();

        return Inertia::render('Assignments/Create', [
            'employees' => $employees,
            'campaigns' => $campaigns,
            'positions' => $positions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:actif,terminé,suspendu',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        Assignment::create($request->all());

        return redirect()->route('assignments.index')->with('success', 'Assignation créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        }

        $assignment = Assignment::with(['employee.user', 'campaign', 'position', 'manager'])->findOrFail($id);

        return Inertia::render('Assignments/Show', [
            'assignment' => $assignment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        }

        $assignment = Assignment::findOrFail($id);
        $employees = Employee::with('user')->get();
        $campaigns = Campaign::all();
        $positions = Position::all();

        return Inertia::render('Assignments/Edit', [
            'assignment' => $assignment,
            'employees' => $employees,
            'campaigns' => $campaigns,
            'positions' => $positions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        }

        $assignment = Assignment::findOrFail($id);

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:actif,terminé,suspendu',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $assignment->update($request->all());

        return redirect()->route('assignments.index')->with('success', 'Assignation mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        }

        $assignment = Assignment::findOrFail($id);
        $assignment->update(['status' => 'suspendu']);

        return redirect()->route('assignments.index')->with('success', 'Assignation suspendue avec succès.');
    }
}
