<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Guards web (admin, rh, manager)
        Role::firstOrCreate(['name' => 'admin',    'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'rh',       'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manager',  'guard_name' => 'web']);

        // Guard employer
        Role::firstOrCreate(['name' => 'employer', 'guard_name' => 'employer']);
    }
}