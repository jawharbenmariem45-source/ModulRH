<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Models\Company;
use App\Models\Departement;
use App\Models\Poste;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company1     = Company::where('name', 'SummitRise')->first();
        $deptProd     = Departement::where('name', 'Production')->first();
        $posteTech    = Poste::where('name', 'Technicien')->first();
        
        $deptDirection = Departement::where('name', 'Direction')->first();

        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'       => 'Admin',
                'password'   => Hash::make('azerty'),
                'company_id' => null,
            ]
        );
        $admin->syncRoles(['admin']);

        // RH
        $rh = User::updateOrCreate(
            ['email' => 'rh@gmail.com'],
            [
                'name'       => 'RH',
                'password'   => Hash::make('123456'),
                'company_id' => $company1?->id,
            ]
        );
        $rh->syncRoles(['rh']);

        $manager = User::updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'name'           => 'Manager',
                'password'       => Hash::make('123456'),
                'company_id'     => $company1?->id,
                'departement_id' => $deptDirection?->id, 
            ]
        );
        $manager->syncRoles(['manager']);

        // Employé de test
        $employer = User::updateOrCreate(
            ['email' => 'employer@gmail.com'],
            [
                'name'                    => 'Employer',
                'last_name'               => 'Employer',
                'first_name'              => 'Test',
                'password'                => Hash::make('123456'),
                'phone'                   => '12345678',
                'company_id'              => $company1?->id,
                'departement_id'          => $deptProd?->id,
                'poste_id'                => $posteTech?->id,
                'contract_type'           => 'CDD',
                'start_date'              => '2026-01-01',
                'end_date'                => '2026-12-31',
                'cnss'                    => '2222222222',
                'rib'                     => '1234567890123456',
                'salary'                  => 800,
                'family_head'             => false,
                'children_count'          => 0,
                'disabled_children_count' => 0,
                'student_children_count'  => 0,
            ]
        );
        $employer->syncRoles(['employer']);

        $this->command->info('✓ 4 utilisateurs créés (admin, rh, manager, employer)');
    }
}