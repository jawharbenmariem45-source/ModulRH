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
        $company = Company::where('name', 'Entreprise A')->first();

        if (!$company) {
            $this->command->error('Entreprise A introuvable. Lancez d\'abord CompanySeeder.');
            return;
        }

        // ── Employé 1 — CDD (déjà existant, on le garde) ─────────────
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

        // ── Employé 2 — CDI ───────────────────────────────────────────
        Employer::firstOrCreate(
            ['email' => 'ahmed.ben.ali@gmail.com'],
            [
                'department_id'            => 1,
                'company_id'               => $company->id,
                'nom'                      => 'BEN ALI',
                'prenom'                   => 'Ahmed',
                'password'                 => Hash::make('123456'),
                'numero_telephone'         => '22334455',
                'type_contrat'             => 'CDI',
                'date_debut'               => '2024-03-01',
                'date_fin'                 => null,
                'cnss'                     => '3333333333',
                'salaire'                  => 1500,
                'chef_famille'             => true,
                'nombre_enfants'           => 2,
                'nombre_enfants_infirmes'  => 0,
                'nombre_enfants_etudiants' => 0,
            ]
        );

        // ── Employé 3 — CIVP ─────────────────────────────────────────
        Employer::firstOrCreate(
            ['email' => 'salma.trabelsi@gmail.com'],
            [
                'department_id'            => 1,
                'company_id'               => $company->id,
                'nom'                      => 'TRABELSI',
                'prenom'                   => 'Salma',
                'password'                 => Hash::make('123456'),
                'numero_telephone'         => '55667788',
                'type_contrat'             => 'CIVP',
                'date_debut'               => '2025-09-01',
                'date_fin'                 => '2026-08-31',
                'cnss'                     => null,
                'salaire'                  => 1500,
                'chef_famille'             => false,
                'nombre_enfants'           => 0,
                'nombre_enfants_infirmes'  => 0,
                'nombre_enfants_etudiants' => 0,
            ]
        );

        // ── Employé 4 — Karama ────────────────────────────────────────
        Employer::firstOrCreate(
            ['email' => 'yassine.maaloul@gmail.com'],
            [
                'department_id'            => 1,
                'company_id'               => $company->id,
                'nom'                      => 'MAALOUL',
                'prenom'                   => 'Yassine',
                'password'                 => Hash::make('123456'),
                'numero_telephone'         => '99887766',
                'type_contrat'             => 'Karama',
                'date_debut'               => '2025-06-01',
                'date_fin'                 => '2026-05-31',
                'cnss'                     => '4444444444',
                'salaire'                  => 1500,
                'chef_famille'             => false,
                'nombre_enfants'           => 1,
                'nombre_enfants_infirmes'  => 0,
                'nombre_enfants_etudiants' => 0,
            ]
        );

        $this->command->info('✓ 4 employés créés (CDD, CDI, CIVP, Karama) — salaire 1500 DT chacun');
    }
}