<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Admin', 'CP', 'SUP', 'TC'];

        foreach ($roles as $roleName) {
            // firstOrCreate évite de créer des doublons si tu relances le seeder
            Role::firstOrCreate(['name' => $roleName]);
        }
    }
}