<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\TimesheetEntryController;
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
