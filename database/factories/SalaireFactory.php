<?php

namespace Database\Factories;

use App\Models\Salaire;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalaireFactory extends Factory
{
    protected $model = Salaire::class;

    public function definition(): array
    {
        // ══════════════════════════════════════════════
        // MONTANT SALE — unité fausse, format incorrect
        // ══════════════════════════════════════════════
        $montantBase = $this->faker->randomFloat(3, 800, 5000);

        $montantSale = $this->faker->randomElement([
            $montantBase,                                    // ✅ correct
            '$' . $montantBase,                              // ❌ dollar
            $montantBase . 'DT',                             // ❌ unité collée
            '€' . $montantBase,                              // ❌ euro
            str_replace('.', ',', $montantBase),             // ❌ virgule
            0,                                               // ❌ zéro
            -rand(100, 500),                                 // ❌ négatif
            rand(10000, 99999),                              // ❌ aberrant
            null,                                            // ❌ null
        ]);

        return [
            'employer_id' => Employer::inRandomOrder()->first()?->id ?? 1,
            'montant'     => $montantSale,
        ];
    }
}