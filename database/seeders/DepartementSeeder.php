<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departement;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $departements = [
            'Direction',
            'Ressources Humaines',
            'Finance',
            'Commercial',
            'Informatique',
            'Production',
            'Logistique',
            'Qualité',
        ];

        foreach ($departements as $name) {
            Departement::firstOrCreate(['name' => $name]);
        }

        $this->command->info('✓ ' . count($departements) . ' départements créés.');
    }
}