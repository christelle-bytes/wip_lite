<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'cp', 'sup', 'tc'];

        foreach ($roles as $roleName) {
            // firstOrCreate évite de créer des doublons si tu relances le seeder
            Role::firstOrCreate(['name' => $roleName]);
        }
    }
}