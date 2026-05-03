<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contract;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        Contract::firstOrCreate(['name' => 'CIVP'], [
            'details'       => 'Contrat d\'Initiation à la Vie Professionnelle, première année après graduation. Période d\'essai maximale de 6 mois, renouvelable une fois.',
            'duration_days' => 365
        ]);

        Contract::firstOrCreate(['name' => 'CDI'], [
            'details'       => 'Contrat à durée indéterminée. Période d\'essai 6 mois maximum, renouvelable une fois.' . '1.83 al conger',
            'duration_days' => null
        ]);

        Contract::firstOrCreate(['name' => 'CDD'], [
            'details'       => 'Contrat à durée déterminée. Conversion automatique en CDI si l\'employé continue après expiration.',
            'duration_days' => 180
        ]);

        Contract::firstOrCreate(['name' => 'Karama'], [
            'details'       => 'Contrat social pour faciliter l\'insertion professionnelle.',
            'duration_days' => 365
        ]);
    }
}