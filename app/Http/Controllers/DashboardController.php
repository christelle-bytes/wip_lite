<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Inertia\Inertia;

class DashboardController {

    public function index(){

        $employee= Employee::all() ;
        return Inertia::render('Dashboard/Admin',['employee' => $employee] );

    }

}