<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $employer    = Employer::inRandomOrder()->first();
        $salaireBase = is_numeric($employer->salaire)
            ? (float) $employer->salaire
            : $this->faker->randomFloat(3, 800, 5000);

        $heuresSup        = $this->faker->randomFloat(3, 0, 50);
        $montantHeuresSup = round($heuresSup * ($salaireBase / 208) * 1.25, 3);
        $primes           = $this->faker->randomFloat(3, 0, 300);
        $indemnites       = $this->faker->randomFloat(3, 0, 200);
        $salaireBrut      = round($salaireBase + $montantHeuresSup + $primes + $indemnites, 3);

        $cnss   = round($salaireBrut * 0.0918, 3);
        $css    = round($salaireBrut * 0.01, 3);
        $irpp   = round(($salaireBrut - $cnss) * 0.15, 3);
        $amount = round($salaireBrut - $cnss - $css - $irpp, 3);

        $launchDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $doneTime   = Carbon::parse($launchDate)->addMinutes(rand(1, 60));

        $mois = [
            'JANVIER', 'FEVRIER', 'MARS', 'AVRIL', 'MAI', 'JUIN',
            'JUILLET', 'AOUT', 'SEPTEMBRE', 'OCTOBRE', 'NOVEMBRE', 'DECEMBRE'
        ];

        // ══════════════════════════════════════════════
        // SALAIRE BASE SALE
        // ══════════════════════════════════════════════
        $salaireBaseSale = $this->faker->randomElement([
            $salaireBase,                                    // ✅ correct
            '$' . $salaireBase,                              // ❌ dollar
            $salaireBase . 'DT',                             // ❌ unité collée
            str_replace('.', ',', $salaireBase),             // ❌ virgule
            '€' . $salaireBase,                              // ❌ euro
            0,                                               // ❌ zéro
            -rand(100, 500),                                 // ❌ négatif
            rand(10000, 99999),                              // ❌ aberrant
        ]);

        // ══════════════════════════════════════════════
        // MONTANTS SALES
        // ══════════════════════════════════════════════
        $salaireBrutSale = $this->faker->randomElement([
            $salaireBrut,                                    // ✅ correct
            '$' . $salaireBrut,                              // ❌ dollar
            str_replace('.', ',', $salaireBrut),             // ❌ virgule
            0,                                               // ❌ zéro
            -$salaireBrut,                                   // ❌ négatif
        ]);

        $amountSale = $this->faker->randomElement([
            $amount,                                         // ✅ correct
            '$' . $amount,                                   // ❌ dollar
            str_replace('.', ',', $amount),                  // ❌ virgule
            0,                                               // ❌ zéro
            -$amount,                                        // ❌ négatif
            rand(10000, 99999),                              // ❌ aberrant
        ]);

        // ══════════════════════════════════════════════
        // DATE SALE
        // ══════════════════════════════════════════════
        $launchDateSale = $this->faker->randomElement([
            $launchDate->format('Y-m-d H:i:s'),             // ✅ correct
            $launchDate->format('d/m/Y H:i:s'),             // ❌ européen
            $launchDate->format('d-m-Y H:i:s'),             // ❌ inversé
            $launchDate->format('m/d/Y H:i:s'),             // ❌ américain
        ]);

        // ══════════════════════════════════════════════
        // REFERENCE SALE — parfois dupliquée ou mal formatée
        // ══════════════════════════════════════════════
        $referenceSale = $this->faker->randomElement([
            'PAY-' . strtoupper($this->faker->bothify('??####')),  // ✅ correct
            'pay-' . $this->faker->bothify('??####'),              // ❌ minuscule
            $this->faker->bothify('??####'),                       // ❌ sans préfixe
            'PAY_' . strtoupper($this->faker->bothify('??####')),  // ❌ underscore
            null,                                                   // ❌ null
        ]);

        // ══════════════════════════════════════════════
        // MOIS SALE — mal écrit ou mauvais format
        // ══════════════════════════════════════════════
        $moisSale = $this->faker->randomElement([
            $this->faker->randomElement($mois),              // ✅ correct
            strtolower($this->faker->randomElement($mois)),  // ❌ minuscule
            $this->faker->numberBetween(1, 12),              // ❌ chiffre
            'N/A',                                           // ❌ inconnu
        ]);

        // ══════════════════════════════════════════════
        // ANNEE SALE
        // ══════════════════════════════════════════════
        $anneSale = $this->faker->randomElement([
            (string) $this->faker->numberBetween(2022, 2026), // ✅ correct
            $this->faker->numberBetween(2022, 2026),          // ❌ entier au lieu de string
            '20' . $this->faker->numerify('##'),              // ❌ année future aberrante
            null,                                              // ❌ null
        ]);

        // ══════════════════════════════════════════════
        // STATUS SALE — incohérent avec le montant
        // ══════════════════════════════════════════════
        $statusSale = $this->faker->randomElement([
            'SUCCESS',   // ✅
            'FAILED',    // ✅
            'success',   // ❌ minuscule
            'failed',    // ❌ minuscule
            'PENDING',   // ❌ inexistant
            null,        // ❌ null
        ]);

        return [
            'reference'          => $referenceSale,
            'employer_id'        => $employer->id,
            'type_contrat'       => $employer->type_contrat,
            'salaire_base'       => $salaireBaseSale,
            'heures_sup'         => $heuresSup,
            'montant_heures_sup' => $montantHeuresSup,
            'primes'             => $primes,
            'indemnites'         => $indemnites,
            'salaire_brut'       => $salaireBrutSale,
            'cnss'               => $cnss,
            'irpp'               => $irpp,
            'css'                => $css,
            'amount'             => $amountSale,
            'launch_date'        => $launchDateSale,
            'done_time'          => $doneTime,
            'status'             => $statusSale,
            'month'              => $moisSale,
            'year'               => $anneSale,
        ];
    }
}