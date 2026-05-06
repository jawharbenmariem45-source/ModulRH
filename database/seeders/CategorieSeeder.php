<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Cadre'],
            ['name' => 'Maîtrise'],
            ['name' => 'Exécution'],
            ['name' => 'Ingénieur'],
            ['name' => 'Technicien'],
            ['name' => 'Ouvrier'],
            ['name' => 'Stagiaire'],
            ['name' => 'Consultant'],
        ];

        foreach ($categories as $categorie) {
            DB::table('categories')->updateOrInsert(
                ['name' => $categorie['name']],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}