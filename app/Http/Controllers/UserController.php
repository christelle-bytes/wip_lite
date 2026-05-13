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
    public function index(Request $request): Response
    {
        // On récupère TOUS les utilisateurs actifs et inactifs pour le filtrage côté client
        $users = User::with('role')->get();
        $roles = Role::all(['id', 'name']);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        $roles = Role::pluck('name', 'id');
        
        // Récupérer les employés qui n'ont pas encore de compte utilisateur avec leur position
        $employees = Employee::with('position')
            ->where(function($query) {
                $query->whereNull('user_id')
                      ->orWhereDoesntHave('user');
            })
            ->where('status', 'actif')
            ->get();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'employees' => $employees,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::findOrFail($data['role_id']);
        $roleName = strtoupper($role->name);
        
        $employeeIds = (array) $data['employee_ids'];
        $count = 0;
        $mismatches = [];

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::with('position')->findOrFail($employeeId);
            
            // Vérification de sécurité : l'employé ne doit pas déjà avoir de compte
            if ($employee->user_id || User::where('email', $employee->email)->exists()) {
                continue;
            }

            // Vérification de cohérence rôle/position (optionnel mais recommandé)
            $posCode = strtoupper($employee->position?->code ?? '');
            if ($roleName !== 'ADMIN' && $posCode !== $roleName && !($roleName === 'ADMIN' && $posCode === 'RH')) {
                $mismatches[] = "{$employee->first_name} {$employee->last_name} ({$posCode})";
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

        if (!empty($mismatches)) {
            $message .= " Attention : Des rôles ont été attribués à des employés dont la position ne correspond pas : " . implode(', ', $mismatches);
        }

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

