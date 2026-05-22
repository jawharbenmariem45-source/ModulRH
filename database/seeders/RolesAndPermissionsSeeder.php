<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view employers', 'create employer', 'edit employer', 'delete employer',
            'view contracts', 'edit contract', 'delete contract', 'download contract pdf',
            'view payments', 'process payments', 'download invoice',
            'view leaves', 'create leave', 'edit leave', 'approve leave', 'reject leave',
            'view departments', 'create department', 'edit department', 'delete department',
            'view roles', 'create role', 'delete role',
            'view settings', 'edit settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereNotIn('name', ['view settings', 'edit settings'])
                ->pluck('name')
                ->toArray()
        );

        // RH
        $rh = Role::firstOrCreate(['name' => 'rh', 'guard_name' => 'web']);
        $rh->syncPermissions([
            'view employers', 'create employer', 'edit employer', 'delete employer',
            'view contracts', 'edit contract', 'delete contract', 'download contract pdf',
            'view payments', 'process payments', 'download invoice',
            'view leaves', 'create leave', 'edit leave', 'approve leave', 'reject leave',
            'view departments', 'create department', 'edit department', 'delete department',
            'view settings', 'edit settings',
        ]);

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view leaves', 'approve leave', 'reject leave',
        ]);

        // Employer — guard web
        $employer = Role::firstOrCreate(['name' => 'employer', 'guard_name' => 'web']);
        $employer->syncPermissions([
            'view leaves', 'create leave', 'edit leave',
            'view payments', 'download invoice',
            'view contracts', 'download contract pdf',
        ]);

        $this->command->info('✓ Roles and permissions created.');
    }
}