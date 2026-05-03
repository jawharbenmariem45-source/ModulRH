<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =============================================
        // PERMISSIONS GUARD WEB (users: admin/rh/manager)
        // =============================================
        $permissionsWeb = [
            // Employers
            'voir employers',
            'ajouter employer',
            'modifier employer',
            'supprimer employer',
            // Contrats
            'voir contrats',
            'modifier contrat',
            'supprimer contrat',
            'telecharger contrat pdf',
            // Paiements
            'voir paiements',
            'lancer paiements',
            'telecharger facture',
            // Congés
            'voir conges',
            'ajouter conge',
            'modifier conge',
            'valider conge',
            'refuser conge',
            // Départements
            'voir departements',
            'ajouter departement',
            'modifier departement',
            'supprimer departement',
            // Rôles
            'voir roles',
            'ajouter role',
            'supprimer role',
            // Configurations
            'voir configurations',
            'modifier configurations',
        ];

        foreach ($permissionsWeb as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // =============================================
        // PERMISSIONS GUARD EMPLOYER (table employers)
        // =============================================
        $permissionsEmployer = [
            'voir conges',
            'ajouter conge',
            'modifier conge',
            'voir paiements',
            'telecharger facture',
            'voir contrats',
            'telecharger contrat pdf',
        ];

        foreach ($permissionsEmployer as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'employer',
            ]);
        }

        // =============================================
        // RÔLES GUARD WEB
        // =============================================
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(
    Permission::where('guard_name', 'web')
        ->whereNotIn('name', ['voir configurations', 'modifier configurations'])
        ->pluck('name')
        ->toArray()
);

        $rh = Role::firstOrCreate(['name' => 'rh', 'guard_name' => 'web']);
        $rh->syncPermissions([
            'voir employers', 'ajouter employer', 'modifier employer', 'supprimer employer',
            'voir contrats', 'modifier contrat', 'supprimer contrat', 'telecharger contrat pdf',
            'voir paiements', 'lancer paiements', 'telecharger facture',
            'voir conges', 'ajouter conge', 'modifier conge', 'valider conge', 'refuser conge',
            'voir departements', 'ajouter departement', 'modifier departement', 'supprimer departement',
        ]);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'voir conges',
            'valider conge',
            'refuser conge',
        ]);

        // =============================================
        // RÔLE GUARD EMPLOYER
        // =============================================
        $employer = Role::firstOrCreate(['name' => 'employer', 'guard_name' => 'employer']);
        $employer->syncPermissions([
            'voir conges',
            'ajouter conge',
            'modifier conge',
            'voir paiements',
            'telecharger facture',
            'voir contrats',
            'telecharger contrat pdf',
        ]);
    }
}