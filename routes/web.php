<?php

use App\Http\Controllers\PlanningAssignementController;
use App\Http\Controllers\PlanningModelController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\TimesheetEntryController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});




Route::get('/dashboard', function () {
    $user = Auth::user();
    $role = $user?->role;
    $roleName = $role ? strtoupper($role->name) : 'ADMIN';

    return match ($roleName) {
        'ADMIN' => Inertia::render('DashboardAdmin'),
        'CP' => Inertia::render('DashboardCp'),
        'SUP' => Inertia::render('DashboardSup'),
        'TC' => Inertia::render('DashboardTc'),
        default => Inertia::render('DashboardAdmin'),
    };
})->middleware(['auth'])->name('dashboard');

Route::resource('/timesheet', TimesheetController::class);
Route::resource('/timesheetEntry', TimesheetEntryController::class);
// saisie heure sup
Route::get('/timesheetEntry_supEntry', [TimesheetEntryController::class, 'entrySup'])->name('entry.sup');
// vue recap saisie sup
Route::get('/timesheetEntry_sup', [TimesheetEntryController::class, 'indexSup'])->name('index.sup');

// vue recap saisie telecon
Route::get('/timesheetEntry_telecon', [TimesheetEntryController::class, 'indexTelecon'])->name('index.telecon');
// saisie heure telecon
Route::get('/timesheetEntry_teleconEntry', [TimesheetEntryController::class, 'entryTelecon'])->name('entry.telecon');

// store telecon
Route::post('/timesheetEntry_teleconStore', [TimesheetEntryController::class, 'storeTelecon'])->name('store.telecon');
// store sup
Route::post('/timesheetEntry_supStore', [TimesheetEntryController::class, 'storeSup'])->name('store.sup');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/gestion-employees', function () {
        return Inertia::render('Employees/gestionEmployee');
    })->name('employees.gestion');
    
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::get('/positions/{id}', [PositionController::class, 'show'])->name('positions.show');
    Route::delete('/positions/{id}', [PositionController::class, 'destroy'])->name('positions.destroy');

    Route::middleware('role:admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});

//Tout ce qui concerne planning chez Breton
//planningModel
Route::get('/planning', [PlanningModelController::class, 'index'])->name('planning.index');
Route::post('/planning', [PlanningModelController::class, 'store'])->name('planning.store');
Route::put('/planning/{planningModel}', [PlanningModelController::class, 'update'])->name('planning.update');
Route::delete('/planning/{planningModel}', [PlanningModelController::class, 'destroy'])->name('planning.destroy');
Route::post('/planning/{planningModel}/suspend', [PlanningModelController::class, 'suspend'])->name('planning.suspend');

// Planning Assignments
Route::post('/planning-assignment', [PlanningAssignementController::class, 'store'])->name('planning-assignment.store');
Route::put('/planning-assignment/{planningAssignment}', [PlanningAssignementController::class, 'update'])->name('planning-assignment.update');
Route::delete('/planning-assignment/{planningAssignment}', [PlanningAssignementController::class, 'destroy'])->name('planning-assignment.destroy');
Route::patch('/planning-assignment/{planningAssignment}/status', [PlanningAssignementController::class, 'changeStatus'])->name('planning-assignment.change-status');

// planning assignment page
Route::get('/planning/affectation', [PlanningAssignementController::class, 'affectation'])->name('planning.affectation');
Route::get('/planning/validation', [PlanningAssignementController::class, 'validation'])
    ->name('planning.validation');

//planningAssignment
Route::post('/planning-assignments', [PlanningAssignementController::class, 'store'])->name('planning-assignments.store');
Route::put('/planning-assignments/{planningAssignment}', [PlanningAssignementController::class, 'update'])->name('planning-assignments.update');
Route::delete('/planning-assignments/{planningAssignment}', [PlanningAssignementController::class, 'destroy'])->name('planning-assignments.destroy');
Route::patch('/planning-assignments/{planningAssignment}/status', [PlanningAssignementController::class, 'changeStatus'])->name('planning-assignments.changeStatus');


    
Route::resource('campaigns', CampaignController::class);

Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
Route::post('/assignments/cp', [AssignmentController::class, 'assignCP'])->name('assignments.assignCP');
Route::post('/assignments/sup', [AssignmentController::class, 'assignSUP'])->name('assignments.assignSUP');
Route::post('/assignments/tc', [AssignmentController::class, 'assignTC'])->name('assignments.assignTC');
Route::patch('/assignments/{assignment}/release', [AssignmentController::class, 'release'])->name('assignments.release');
Route::get('/statistiques', [ReportingController::class, 'index'])->name('reporting.index');

require __DIR__.'/auth.php';
