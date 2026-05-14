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
                'department_id'           => 1,
                'company_id'              => $company->id,
                'last_name'               => 'Employer',     
                'first_name'              => 'Production',  
                'password'                => Hash::make('123456'),
                'phone'                   => '12345678',     
                'contract_type'           => 'CDD',          
                'start_date'              => '2026-01-01',   
                'end_date'                => '2026-12-31',   
                'cnss'                    => '2222222222',
                'salary'                  => 1500,              
                'family_head'             => false,             
                'children_count'          => 0,                
                'disabled_children_count' => 0,               
                'student_children_count'  => 0,              
            ]
        );

        $this->command->info('✓ 1 employé créé — salaire 1500 DT');
    }
}