<?php

use App\Http\Controllers\PlanningAssignementController;
use App\Http\Controllers\PlanningModelController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CampaignController;
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

// Route::get('/dashboard', [PlanningModelController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

Route::resource('campaigns', CampaignController::class);

Route::resource('assignments', AssignmentController::class);

require __DIR__.'/auth.php';
