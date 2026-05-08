<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

use Illuminate\Support\Facades\Auth;


Route::get('/dashboard', function () {
    $user = Auth::user();
    $roleName = $user?->role?->name;

    return match ($roleName) {
        'Admin' => Inertia::render('DashboardAdmin'),
        'CP' => Inertia::render('DashboardCp'),
        'SUP' => Inertia::render('DashboardSup'),
        'TC' => Inertia::render('DashboardTc'),
        default => Inertia::render('DashboardAdmin'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');


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

Route::resource('campaigns', CampaignController::class);

Route::resource('assignments', AssignmentController::class);

require __DIR__.'/auth.php';
