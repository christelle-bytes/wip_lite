<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PlanningAssignment;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // les heures des chef plateau
    public function index()
    {
        // $timesheets = Timesheet::with(['employee', 'validator'])->latest()->get();
        // return  inertia('Timesheets/Index', ['timesheets'=> $timesheets]);

        // $timesheets = Timesheet::with(['employee', 'validator']) // Charge les relations
        // ->latest()
        // ->get();
        $cp = Employee::with('position')->whereHas('position', function ($query) {
            $query->where('code', 'CP');
        })
            ->get();
        $planning = PlanningAssignment::with(['employee', 'planningModel'])->whereHas('employee.position', function ($query) {
            $query->where('code', 'CP');
        })
            ->get();
        $timesheets = Timesheet::with(['employee', 'validator'])
            ->whereHas('employee.position', function ($query) {
                $query->where('code', 'CP');
            })
            ->latest()
            ->get();

        return Inertia::render('Timesheets/Index', [
            'timesheets' => $timesheets,
            'cp' => $cp,
            'planning' => $planning,
        ]);
    }

    // les heures des superviseurs
    // public function indexSUP()
    // {
    //     // $timesheets = Timesheet::with(['employee', 'validator'])->latest()->get();
    //     // return  inertia('Timesheets/Index', ['timesheets'=> $timesheets]);

    //     // $timesheets = Timesheet::with(['employee', 'validator']) // Charge les relations
    //     // ->latest()
    //     // ->get();
    //     $timesheets = Timesheet::with(['employee', 'validator'])
    //     ->whereHas('employee.position', function ($query){
    //         $query->where('code', 'SUP');
    //     })
    //     ->latest()
    //     ->get();

    // return Inertia::render('Timesheets/Index', [
    //     'timesheets' => $timesheets
    // ]);
    // }

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
            'employee_id' => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'status' => 'nullable',
            'validated_by' => 'nullable|exists:employees,id',
        ]);

        Timesheet::create($validated);
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
        $timesheet->update($request->only(['period_start', 'period_end', 'status', 'validated_by', 'validated_at']));
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
