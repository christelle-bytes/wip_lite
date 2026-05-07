<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class UsersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. On crée les rôles fixes (doivent matcher EXACTEMENT avec User::isAdmin/isSUP/isTC)
        $roleNames = ['Admin', 'CP', 'SUP', 'TC'];
        $roles = collect($roleNames)->map(function ($name) {
            return Role::firstOrCreate(['name' => $name]);
        })->keyBy(fn ($role) => $role->name);


        // 2. Créer un Admin spécifique pour se connecter
        $adminRole = $roles->get('Admin') ?? Role::firstWhere('name', 'Admin');

        if (! $adminRole) {
            // Fallback diagnostique : si ça casse ici, c’est un problème de schéma/seed roles.
            $existingRoles = Role::query()->pluck('name')->values()->all();
            throw new \RuntimeException('Role admin introuvable. Roles existants: '.json_encode($existingRoles));
        }

        // Nettoyer les utilisateurs de test avant d'en créer de nouveaux
        $testEmails = ['admin@test.com', 'cp@test.com', 'sup@test.com', 'tc@test.com'];
        User::query()->whereIn('email', $testEmails)->delete();

        // Créer un utilisateur pour chaque rôle avec mot de passe 'password123'
        $testUsers = [
            ['email' => 'admin@test.com', 'name' => 'Admin User', 'role' => 'Admin'],
            ['email' => 'cp@test.com', 'name' => 'CP User', 'role' => 'CP'],
            ['email' => 'sup@test.com', 'name' => 'Supervisor User', 'role' => 'SUP'],
            ['email' => 'tc@test.com', 'name' => 'Technician User', 'role' => 'TC'],
        ];

        foreach ($testUsers as $userData) {
            $role = $roles->get($userData['role']) ?? Role::firstWhere('name', $userData['role']);
            
            if ($role) {
                User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'role_id' => $role->id,
                        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    ]
                );
            }
        }




        // 3. Créer 300 utilisateurs aléatoires répartis sur les rôles existants
        // Important : en reseed, la factory génère des emails uniques, mais la seed doit être nettoyée.
        // On supprime d'abord les users créés par la factory (simple approche : tout sauf les utilisateurs de test).
        User::query()->whereNotIn('email', $testEmails)->delete();

        User::factory(300)->create([
            'role_id' => fn () => $roles->random()->id,
        ]);



    }
}

