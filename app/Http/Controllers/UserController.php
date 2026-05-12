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
        
        $employeeIds = (array) $data['employee_ids'];
        $count = 0;

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::findOrFail($employeeId);
            
            // Vérifier si l'employé n'a pas déjà un utilisateur (sécurité supplémentaire)
            if ($employee->user_id || User::where('email', $employee->email)->exists()) {
                continue;
            }

            $user = User::create([
                'email' => $employee->email,
                'role_id' => $data['role_id'],
                'password' => bcrypt('Welcome123!'),
                'must_change_password' => true,
            ]);

            // Lier l'utilisateur à l'employé
            $employee->update(['user_id' => $user->id]);
            $count++;
        }

        if ($count === 0) {
            return redirect()->route('users.index')->with('error', "Aucun compte n'a pu être créé.");
        }

        $message = $count === 1 
            ? "1 compte utilisateur a été créé avec succès." 
            : "{$count} comptes utilisateurs ont été créés avec succès.";

        return redirect()->route('users.index')->with('success', $message . " Mot de passe par défaut: Welcome123!");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => false]);

        return redirect()->route('users.index')->with('success', 'Utilisateur désactivé.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas modifier le statut de votre propre compte.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activé' : 'désactivé';

        return redirect()->route('users.index')->with('success', "Utilisateur {$status}.");
    }
}

