<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            DepartementSeeder::class, // ← doit être avant EmployerSeeder
            UserSeeder::class,
            ContractSeeder::class,
            EmployerSeeder::class,    // ← a besoin des départements
        ]);
    }
}