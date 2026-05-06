<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $password = Hash::make('password123');

        $users = [
            'admin' => [
                'name' => 'RH Admin',
                'email' => 'admin@rh.com',
                'role_name' => 'admin',
            ],
            'cp' => [
                'name' => 'Chef Plateau',
                'email' => 'cp@rh.com',
                'role_name' => 'cp',
            ],
            'sup' => [
                'name' => 'Superviseur',
                'email' => 'sup@rh.com',
                'role_name' => 'sup',
            ],
            'tc' => [
                'name' => 'Employé TC',
                'email' => 'tc@rh.com',
                'role_name' => 'tc',
            ],
        ];

        foreach ($users as $key => $data) {
            $role = Role::where('name', $data['role_name'])->first();
            if (! $role) {
                continue;
            }

            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'role_id' => $role->id,
                ]
            );
        }
    }
}

