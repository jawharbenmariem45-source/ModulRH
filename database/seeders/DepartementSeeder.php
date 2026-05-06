<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departement;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $departements = [
            ['name' => 'Production & Opérations'],
            ['name' => 'Ressources Humaines'],
            ['name' => 'Informatique & IT'],
            ['name' => 'Finance & Comptabilité'],
            ['name' => 'Direction Générale'],
            ['name' => 'Commercial & Ventes'],
            ['name' => 'Marketing & Communication'],
            ['name' => 'Logistique & Supply Chain'],
            ['name' => 'Qualité, Hygiène, Sécurité & Environnement (QHSE)'],
            ['name' => 'Recherche & Développement (R&D)'],
            ['name' => 'Achats & Approvisionnements'],
            ['name' => 'Juridique & Contentieux'],
        ];

        foreach ($departements as $departement) {
            Departement::firstOrCreate($departement);
        }
    }
}