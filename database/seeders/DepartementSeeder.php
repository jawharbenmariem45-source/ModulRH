<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departement;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $departements = [
            ['name' => 'Production'],
            ['name' => 'Ressources Humaines'],
            ['name' => 'Informatique'],
            ['name' => 'Finance'],
        ];

        foreach ($departements as $departement) {
            Departement::firstOrCreate($departement);
        }
    }
}