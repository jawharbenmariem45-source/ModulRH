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

        $permissionsWeb = [
            'view employers', 'create employer', 'edit employer', 'delete employer',
            'view contracts', 'edit contract', 'delete contract', 'download contract pdf',
            'view payments', 'process payments', 'download invoice',
            'view leaves', 'create leave', 'edit leave', 'approve leave', 'reject leave',
            'view departments', 'create department', 'edit department', 'delete department',
            'view roles', 'create role', 'delete role',
            'view settings', 'edit settings',
        ];

        foreach ($permissionsWeb as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $permissionsEmployer = [
            'view leaves', 'create leave', 'edit leave',
            'view payments', 'download invoice',
            'view contracts', 'download contract pdf',
        ];

        foreach ($permissionsEmployer as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'employer']);
        }

        // Admin — tout sauf settings
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereNotIn('name', ['view settings', 'edit settings'])
                ->pluck('name')
                ->toArray()
        );

        // RH — tout + settings
        $rh = Role::firstOrCreate(['name' => 'rh', 'guard_name' => 'web']);
        $rh->syncPermissions([
            'view employers', 'create employer', 'edit employer', 'delete employer',
            'view contracts', 'edit contract', 'delete contract', 'download contract pdf',
            'view payments', 'process payments', 'download invoice',
            'view leaves', 'create leave', 'edit leave', 'approve leave', 'reject leave',
            'view departments', 'create department', 'edit department', 'delete department',
            'view settings', 'edit settings',
        ]);

        // Manager — congés uniquement
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view leaves', 'approve leave', 'reject leave',
        ]);

        // Employer (guard employer)
        $employer = Role::firstOrCreate(['name' => 'employer', 'guard_name' => 'employer']);
        $employer->syncPermissions([
            'view leaves', 'create leave', 'edit leave',
            'view payments', 'download invoice',
            'view contracts', 'download contract pdf',
        ]);

        $this->command->info('✓ Roles and permissions created.');
    }
}