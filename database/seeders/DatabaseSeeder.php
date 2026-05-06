<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // seed ordre important : rôles -> positions -> users
        $roles = ['admin', 'cp', 'sup', 'tc'];
        foreach ($roles as $roleName) {
            \App\Models\Role::updateOrCreate(['name' => $roleName], ['name' => $roleName]);
        }

        $this->call(PositionsSeeder::class);
        $this->call(UsersSeeder::class);
    }
}

