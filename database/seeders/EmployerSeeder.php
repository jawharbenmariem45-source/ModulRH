<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employer;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class EmployerSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', 'SummitRise')->first();

        if (!$company) {
            $this->command->error('Entreprise introuvable. Lancez d\'abord CompanySeeder.');
            return;
        }

        Employer::firstOrCreate(
            ['email' => 'employer@gmail.com'],
            [
                'department_id'            => 1,
                'company_id'               => $company->id,
                'nom'                      => 'employer',
                'prenom'                   => 'production',
                'password'                 => Hash::make('123456'),
                'numero_telephone'         => '12345678',
                'type_contrat'             => 'CDD',
                'date_debut'               => '2026-01-01',
                'date_fin'                 => '2026-12-31',
                'cnss'                     => '2222222222',
                'salaire'                  => 1500,
                'chef_famille'             => false,
                'nombre_enfants'           => 0,
                'nombre_enfants_infirmes'  => 0,
                'nombre_enfants_etudiants' => 0,
            ]
        );

        $this->command->info('✓ 1 employé créé — salaire 1500 DT');
    }
}