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
        // 1. On crée les rôles fixes
        $roles = collect(['Admin', 'CP', 'SUP', 'TC'])->map(function ($name) {
            return Role::create(['name' => $name]);
        });
 
        // 2. Créer un Admin spécifique pour se connecter
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role_id' => $roles->where('name', 'Admin')->first()->id,
        ]);
 
        // 3. Créer 300 utilisateurs aléatoires répartis sur les rôles existants
        User::factory(300)->create([
            'role_id' => fn() => $roles->random()->id,
        ]);
    }
}

