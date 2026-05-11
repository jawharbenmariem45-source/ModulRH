<?php

namespace Database\Factories;

use App\Models\Employer;
use App\Models\Company;
use App\Models\Departement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployerFactory extends Factory
{
    protected $model = Employer::class;

    public function definition(): array
    {
        $typeContrat = $this->faker->randomElement(['CDI', 'CDD', 'CIVP', 'Karama']);
        $dateDebut   = $this->faker->dateTimeBetween('-3 years', '-1 month');
        $dateFin     = in_array($typeContrat, ['CDD', 'CIVP', 'Karama'])
            ? $this->faker->dateTimeBetween('now', '+2 years')
            : null;

        // ── Dates sales ───────────────────────────────────────
        $dateDebutSale = $this->faker->randomElement([
            $dateDebut->format('Y-m-d'),
            $dateDebut->format('d/m/Y'),
            $dateDebut->format('d-m-Y'),
            $dateDebut->format('m/d/Y'),
            $dateDebut->format('d.m.Y'),
        ]);

        $dateFinSale = $dateFin ? $this->faker->randomElement([
            $dateFin->format('Y-m-d'),
            $dateFin->format('d/m/Y'),
            $dateFin->format('d-m-Y'),
            $dateFin->format('m/d/Y'),
            $dateFin->format('d.m.Y'),
        ]) : null;

        // ── Salaire sale ──────────────────────────────────────
        $salaireBase = $this->faker->randomFloat(3, 800, 5000);
        $salaireSale = $this->faker->randomElement([
            $salaireBase,
            '$' . $salaireBase,
            $salaireBase . 'DT',
            str_replace('.', ',', $salaireBase),
            '€' . $salaireBase,
            0,
            -rand(100, 500),
            rand(10000, 99999),
        ]);

        // ── Téléphone sale ────────────────────────────────────
        $telSale = $this->faker->randomElement([
            $this->faker->numerify('2#######'),
            $this->faker->numerify('+216 9#######'),
            $this->faker->numerify('9#-###-###'),
            $this->faker->numerify('9# ### ###'),
            $this->faker->numerify('##########'),
            null,
        ]);

        // ── CNSS sale ─────────────────────────────────────────
        $cnssSale = $this->faker->randomElement([
            $this->faker->numerify('##########'),
            $this->faker->numerify('#######'),
            $this->faker->bothify('######??##'),
            $this->faker->numerify('## ### ### ##'),
            null,
        ]);

        // ── Email sale ────────────────────────────────────────
        $emailSale = $this->faker->randomElement([
            $this->faker->unique()->safeEmail(),
            $this->faker->unique()->safeEmail(),
            str_replace('@', ' @ ', $this->faker->unique()->safeEmail()),
            strtoupper($this->faker->unique()->safeEmail()),
            $this->faker->unique()->safeEmail() . ' ',
        ]);

        // ── Nom / Prénom sale ─────────────────────────────────
        $nomSale = $this->faker->randomElement([
            $this->faker->lastName(),
            strtoupper($this->faker->lastName()),
            strtolower($this->faker->lastName()),
            $this->faker->lastName() . '  ',
            '  ' . $this->faker->lastName(),
        ]);

        $prenomSale = $this->faker->randomElement([
            $this->faker->firstName(),
            strtoupper($this->faker->firstName()),
            strtolower($this->faker->firstName()),
            $this->faker->firstName() . '  ',
        ]);

        return [
            'department_id'            => Departement::inRandomOrder()->first()?->id ?? 1,
            'company_id'               => Company::where('name', 'SummitRise')->first()?->id ?? 1,
            'nom'                      => $nomSale,
            'prenom'                   => $prenomSale,
            'email'                    => $emailSale,
            'password'                 => Hash::make('password'),
            'numero_telephone'         => $telSale,
            'salaire'                  => $salaireSale,
            'chef_famille'             => $this->faker->boolean(30),
            'nombre_enfants'           => $this->faker->numberBetween(0, 5),
            'nombre_enfants_infirmes'  => $this->faker->numberBetween(0, 2),
            'nombre_enfants_etudiants' => $this->faker->numberBetween(0, 3),
            'type_contrat'             => $typeContrat,
            'rib'                      => $this->faker->numerify('##############'),
            'rib_image'                => null,
            'cnss'                     => $cnssSale,
            'date_debut'               => $dateDebutSale,
            'date_fin'                 => $dateFinSale,
        ];
    }

    // ── CDI récent ────────────────────────────────────────────
    public function cdi(): static
    {
        return $this->state([
            'type_contrat' => 'CDI',
            'date_debut'   => Carbon::now()->subMonths(rand(1, 24))->startOfMonth()->toDateString(),
            'date_fin'     => null,
        ]);
    }

    // ── CDI ancien (3 à 10 ans) ───────────────────────────────
    public function ancien(): static
    {
        return $this->state([
            'type_contrat' => 'CDI',
            'date_debut'   => Carbon::now()->subYears(rand(3, 10))->startOfMonth()->toDateString(),
            'date_fin'     => null,
        ]);
    }

    // ── CDD ───────────────────────────────────────────────────
    public function cdd(): static
    {
        $dateDebut = Carbon::now()->subMonths(rand(1, 12))->startOfMonth();
        return $this->state([
            'type_contrat' => 'CDD',
            'date_debut'   => $dateDebut->toDateString(),
            'date_fin'     => $dateDebut->copy()->addMonths(rand(6, 24))->toDateString(),
        ]);
    }

    // ── CIVP ──────────────────────────────────────────────────
    public function civp(): static
    {
        $dateDebut = Carbon::now()->subMonths(rand(1, 6))->startOfMonth();
        return $this->state([
            'type_contrat' => 'CIVP',
            'date_debut'   => $dateDebut->toDateString(),
            'date_fin'     => $dateDebut->copy()->addMonths(rand(6, 12))->toDateString(),
            'cnss'         => null,
        ]);
    }

    // ── Karama ────────────────────────────────────────────────
    public function karama(): static
    {
        $dateDebut = Carbon::now()->subMonths(rand(1, 6))->startOfMonth();
        return $this->state([
            'type_contrat' => 'Karama',
            'date_debut'   => $dateDebut->toDateString(),
            'date_fin'     => $dateDebut->copy()->addMonths(rand(6, 12))->toDateString(),
        ]);
    }
}