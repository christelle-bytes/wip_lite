<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanningAssignementController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;

        $plannings = ($employee && $employee->status === 'actif')
            ? $employee->planningAssignment
            : collect();

        return Inertia::render('planning/Index', [
            'planning' => $plannings
        ]);
    }
}
