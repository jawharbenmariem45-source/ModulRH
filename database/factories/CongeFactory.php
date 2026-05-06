<?php

namespace Database\Factories;

use App\Models\Conge;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CongeFactory extends Factory
{
    protected $model = Conge::class;

    public function definition(): array
    {
        $dateDebut = $this->faker->dateTimeBetween('-1 year', 'now');
        $dateFin   = $this->faker->dateTimeBetween($dateDebut, '+30 days');

        // ══════════════════════════════════════════════
        // DATES SALES — format aléatoirement incorrect
        // ══════════════════════════════════════════════
        $dateDebutSale = $this->faker->randomElement([
            $dateDebut->format('Y-m-d'),   // ✅ correct
            $dateDebut->format('d/m/Y'),   // ❌ format européen
            $dateDebut->format('d-m-Y'),   // ❌ inversé
            $dateDebut->format('m/d/Y'),   // ❌ américain
            $dateDebut->format('d.m.Y'),   // ❌ points
        ]);

        $dateFinSale = $this->faker->randomElement([
            $dateFin->format('Y-m-d'),     // ✅ correct
            $dateFin->format('d/m/Y'),     // ❌ format européen
            $dateFin->format('d-m-Y'),     // ❌ inversé
            $dateFin->format('m/d/Y'),     // ❌ américain
            $dateFin->format('d.m.Y'),     // ❌ points
        ]);

        // ══════════════════════════════════════════════
        // DATE FIN AVANT DATE DEBUT (incohérent)
        // ══════════════════════════════════════════════
        if ($this->faker->boolean(20)) {
            $dateDebutSale = $dateFin->format('Y-m-d');
            $dateFinSale   = $dateDebut->format('Y-m-d');
        }

        // ══════════════════════════════════════════════
        // NOMBRE DE JOURS SALE — incorrect ou négatif
        // ══════════════════════════════════════════════
        $nombreJoursReel = (int) $dateDebut->diff($dateFin)->days;
        $nombreJoursSale = $this->faker->randomElement([
            $nombreJoursReel,                          // ✅ correct
            $nombreJoursReel + rand(1, 10),            // ❌ trop grand
            0,                                         // ❌ zéro
            -rand(1, 5),                               // ❌ négatif
            rand(100, 999),                            // ❌ aberrant
        ]);

        // ══════════════════════════════════════════════
        // TYPE SALE — valeurs inconnues ou mal écrites
        // ══════════════════════════════════════════════
        $typeSale = $this->faker->randomElement([
            'Congé annuel',
            'Congé maladie',
            'Congé maternité',
            'Congé sans solde',
            'Congé exceptionnel',
            'conge annuel',      // ❌ minuscule
            'CONGE MALADIE',     // ❌ majuscule
            'Congé  annuel',     // ❌ double espace
            'Maternité',         // ❌ incomplet
            'N/A',               // ❌ inconnu
            null,                // ❌ null
        ]);

        return [
            'employer_id'  => Employer::inRandomOrder()->first()?->id ?? 1,
            'type'         => $typeSale,
            'date_debut'   => $dateDebutSale,
            'date_fin'     => $dateFinSale,
            'nombre_jours' => $nombreJoursSale,
            'motif'        => $this->faker->optional(0.7)->sentence(6),
            'statut'       => $this->faker->randomElement([
                'En attente',
                'Approuvé',
                'Refusé',
                'approuvé',   // ❌ minuscule
                'APPROUVE',   // ❌ majuscule
                'en attente', // ❌ minuscule
                null,         // ❌ null
            ]),
            'commentaire'  => $this->faker->optional(0.4)->sentence(10),
        ];
    }
}