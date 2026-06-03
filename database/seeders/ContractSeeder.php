<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContractType;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        ContractType::firstOrCreate(['name' => 'CDI'], [
            'details'       => 'Contrat à durée indéterminée. Période d\'essai 6 mois maximum, renouvelable une fois. 1.83 jours de congé par mois.',
            'duration_days' => null,
            'active'        => true,
        ]);

        ContractType::firstOrCreate(['name' => 'CDD'], [
            'details'       => 'Contrat à durée déterminée. Conversion automatique en CDI si l\'employé continue après expiration.',
            'duration_days' => 180,
            'active'        => true,
        ]);

        ContractType::firstOrCreate(['name' => 'CIVP'], [
            'details'       => 'Contrat d\'Initiation à la Vie Professionnelle, première année après graduation. Période d\'essai maximale de 6 mois, renouvelable une fois.',
            'duration_days' => 365,
            'active'        => true,
        ]);

        ContractType::firstOrCreate(['name' => 'Karama'], [
            'details'       => 'Contrat social pour faciliter l\'insertion professionnelle.',
            'duration_days' => 365,
            'active'        => true,
        ]);

        $this->command->info('✓ Types de contrats créés (CDI, CDD, CIVP, Karama).');
    }
}