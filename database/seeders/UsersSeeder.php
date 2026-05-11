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
        $roleAdmin = Role::where('name', 'Admin')->first();
        $roleCP = Role::where('name', 'CP')->first();
        $roleSUP = Role::where('name', 'SUP')->first();
        $roleTC = Role::where('name', 'TC')->first();

        // 1. Créer l'Admin
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'password' => Hash::make('password'),
                'role_id' => $roleAdmin->id,
                'must_change_password' => false,
            ]
        );

        // On ne crée plus d'utilisateurs aléatoires ici car ils doivent être liés à des employés
    }
}

