<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class UsersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
    $roleNames = ['Admin', 'CP', 'SUP', 'TC'];
    $roles = [];

    foreach ($roleNames as $name) {
        $roles[$name] = Role::firstOrCreate(['name' => $name]);
    }

    // 2. Créer l'Admin (On utilise updateOrCreate pour pouvoir relancer le seeder sans doublon)
    User::updateOrCreate(
        ['email' => 'admin@test.com'],
        [
           
            'password' => Hash::make('password'), // Toujours mieux de définir un mot de passe
            'role_id' => $roles['Admin']->id,
        ]
    );

    // 3. Créer les 300 utilisateurs
    // On transforme la liste des rôles en collection pour utiliser random()
    $rolesCollection = collect($roles);

    User::factory(299)->create([
        'role_id' => function () use ($rolesCollection) {
            return $rolesCollection->random()->id;
        },
    ]);;
    }
}

