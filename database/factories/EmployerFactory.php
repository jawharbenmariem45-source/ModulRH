<?php

namespace Database\Factories;

use App\Models\Employer;
use App\Models\Company;
use App\Models\Departement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

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

        // ══════════════════════════════════════════════
        // DATES SALES
        // ══════════════════════════════════════════════
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

        // ══════════════════════════════════════════════
        // SALAIRE SALE
        // ══════════════════════════════════════════════
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

        // ══════════════════════════════════════════════
        // TÉLÉPHONE SALE
        // ══════════════════════════════════════════════
        $telSale = $this->faker->randomElement([
            $this->faker->numerify('2#######'),
            $this->faker->numerify('+216 9#######'),
            $this->faker->numerify('9#-###-###'),
            $this->faker->numerify('9# ### ###'),
            $this->faker->numerify('##########'),
            null,
        ]);

        // ══════════════════════════════════════════════
        // CNSS SALE
        // ══════════════════════════════════════════════
        $cnssSale = $this->faker->randomElement([
            $this->faker->numerify('##########'),
            $this->faker->numerify('#######'),
            $this->faker->bothify('######??##'),
            $this->faker->numerify('## ### ### ##'),
            null,
        ]);

        // ══════════════════════════════════════════════
        // EMAIL SALE
        // ══════════════════════════════════════════════
        $emailSale = $this->faker->randomElement([
            $this->faker->unique()->safeEmail(),
            $this->faker->unique()->safeEmail(),
            str_replace('@', ' @ ', $this->faker->unique()->safeEmail()),
            strtoupper($this->faker->unique()->safeEmail()),
            $this->faker->unique()->safeEmail() . ' ',
        ]);

        // ══════════════════════════════════════════════
        // NOM/PRENOM SALE
        // ══════════════════════════════════════════════
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
            'company_id'               => Company::where('name', 'SummitRise')->first()?->id ?? 1, // ✅ SummitRise uniquement
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

    // État 'ancien' : employé avec une ancienneté réaliste
    public function ancien(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_debut'   => $this->faker->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
            'date_fin'     => null,
            'type_contrat' => 'CDI',
        ]);
    }
}