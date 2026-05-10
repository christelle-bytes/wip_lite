<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('role')->paginate(10);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $roles = Role::pluck('name', 'id');
        
        // Récupérer les employés qui n'ont pas encore de compte utilisateur
        // On vérifie si user_id est nul OU s'il n'y a pas de relation User
        $employees = Employee::where(function($query) {
                $query->whereNull('user_id')
                      ->orWhereDoesntHave('user');
            })
            ->where('status', 'actif')
            ->get(['id', 'first_name', 'last_name', 'email', 'matricule']);

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'employees' => $employees,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Récupérer l'employé pour obtenir son email
        $employee = Employee::findOrFail($data['employee_id']);
        
        $user = User::create([
            'email' => $employee->email,
            'role_id' => $data['role_id'],
            'password' => bcrypt('Welcome123!'),
            'must_change_password' => true,
        ]);

        // Lier l'utilisateur à l'employé
        $employee->update(['user_id' => $user->id]);

        return redirect()->route('users.index')->with('success', "Compte créé pour {$employee->first_name} {$employee->last_name} avec l'email {$employee->email}. Mot de passe par défaut: Welcome123!");
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}

