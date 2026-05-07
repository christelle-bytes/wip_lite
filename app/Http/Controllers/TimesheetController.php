<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PlanningAssignment;
use App\Models\Timesheet;
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
        $sup = Employee::with('position')->whereHas('position', function ($query) {
            $query->where('code', 'SUP');
        })
            ->get();
        $planning = PlanningAssignment::with(['employee', 'planningModel'])->whereHas('employee.position', function ($query) {
            $query->where('code', 'SUP');
        })
            ->get();
        $timesheets = Timesheet::with(['employee', 'validator'])
            ->whereHas('employee.position', function ($query) {
                $query->where('code', 'SUP');
            })
            ->latest()
            ->get();

        return Inertia::render('Timesheets/Index', [
            'timesheets' => $timesheets,
            'sup' => $sup,
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
