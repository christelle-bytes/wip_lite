<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Position;
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
        $posRH = Position::where('code', 'RH')->first();

        // 1. Créer l'Admin
        $user = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'password' => Hash::make('password'),
                'role_id' => $roleAdmin->id,
                'must_change_password' => false,
                'is_active' => true,
            ]
        );

        // Créer l'employé lié si inexistant
        if ($user->wasRecentlyCreated || !$user->employee) {
            Employee::updateOrCreate(
                ['email' => 'admin@test.com'],
                [
                    'first_name' => 'Admin',
                    'last_name' => 'System',
                    'matricule' => 'ADM-001',
                    'position_id' => $posRH->id,
                    'status' => 'actif',
                    'user_id' => $user->id,
                    'birth_date' => '1990-01-01',
                    'salary_base' => 5000,
                ]
            );
        }
    }
}

