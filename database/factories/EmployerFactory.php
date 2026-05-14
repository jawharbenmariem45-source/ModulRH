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
        $contractType = $this->faker->randomElement(['CDI', 'CDD', 'CIVP', 'Karama']);
        $dateDebut    = $this->faker->dateTimeBetween('-3 years', '-1 month');
        $dateFin      = in_array($contractType, ['CDD', 'CIVP', 'Karama'])
            ? $this->faker->dateTimeBetween('now', '+2 years')
            : null;

        $startDateSale = $this->faker->randomElement([
            $dateDebut->format('Y-m-d'),
            $dateDebut->format('d/m/Y'),
            $dateDebut->format('d-m-Y'),
            $dateDebut->format('m/d/Y'),
            $dateDebut->format('d.m.Y'),
        ]);

        $endDateSale = $dateFin ? $this->faker->randomElement([
            $dateFin->format('Y-m-d'),
            $dateFin->format('d/m/Y'),
            $dateFin->format('d-m-Y'),
            $dateFin->format('m/d/Y'),
            $dateFin->format('d.m.Y'),
        ]) : null;

        $salaryBase = $this->faker->randomFloat(3, 800, 5000);
        $salarySale = $this->faker->randomElement([
            $salaryBase,
            '$' . $salaryBase,
            $salaryBase . 'DT',
            str_replace('.', ',', $salaryBase),
            '€' . $salaryBase,
            0,
            -rand(100, 500),
            rand(10000, 99999),
        ]);

        $phoneSale = $this->faker->randomElement([
            $this->faker->numerify('2#######'),
            $this->faker->numerify('+216 9#######'),
            $this->faker->numerify('9#-###-###'),
            $this->faker->numerify('9# ### ###'),
            $this->faker->numerify('##########'),
            null,
        ]);

        $cnssSale = $this->faker->randomElement([
            $this->faker->numerify('##########'),
            $this->faker->numerify('#######'),
            $this->faker->bothify('######??##'),
            $this->faker->numerify('## ### ### ##'),
            null,
        ]);

        $emailSale = $this->faker->randomElement([
            $this->faker->unique()->safeEmail(),
            $this->faker->unique()->safeEmail(),
            str_replace('@', ' @ ', $this->faker->unique()->safeEmail()),
            strtoupper($this->faker->unique()->safeEmail()),
            $this->faker->unique()->safeEmail() . ' ',
        ]);

        $lastNameSale = $this->faker->randomElement([
            $this->faker->lastName(),
            strtoupper($this->faker->lastName()),
            strtolower($this->faker->lastName()),
            $this->faker->lastName() . '  ',
            '  ' . $this->faker->lastName(),
        ]);

        $firstNameSale = $this->faker->randomElement([
            $this->faker->firstName(),
            strtoupper($this->faker->firstName()),
            strtolower($this->faker->firstName()),
            $this->faker->firstName() . '  ',
        ]);

        return [
            'department_id'           => Departement::inRandomOrder()->first()?->id ?? 1,
            'company_id'              => Company::where('name', 'SummitRise')->first()?->id ?? 1,
            'last_name'               => $lastNameSale,
            'first_name'              => $firstNameSale,
            'email'                   => $emailSale,
            'password'                => Hash::make('password'),
            'phone'                   => $phoneSale,
            'salary'                  => $salarySale,
            'family_head'             => $this->faker->boolean(30),
            'children_count'          => $this->faker->numberBetween(0, 5),
            'disabled_children_count' => $this->faker->numberBetween(0, 2),
            'student_children_count'  => $this->faker->numberBetween(0, 3),
            'contract_type'           => $contractType,
            'rib'                     => $this->faker->numerify('##############'),
            'rib_image'               => null,
            'cnss'                    => $cnssSale,
            'start_date'              => $startDateSale,
            'end_date'                => $endDateSale,
        ];
    }

    public function cdi(): static
    {
        return $this->state([
            'contract_type' => 'CDI',
            'start_date'    => Carbon::now()->subMonths(rand(1, 24))->startOfMonth()->toDateString(),
            'end_date'      => null,
        ]);
    }

    public function ancien(): static
    {
        return $this->state([
            'contract_type' => 'CDI',
            'start_date'    => Carbon::now()->subYears(rand(3, 10))->startOfMonth()->toDateString(),
            'end_date'      => null,
        ]);
    }

    public function cdd(): static
    {
        $startDate = Carbon::now()->subMonths(rand(1, 12))->startOfMonth();
        return $this->state([
            'contract_type' => 'CDD',
            'start_date'    => $startDate->toDateString(),
            'end_date'      => $startDate->copy()->addMonths(rand(6, 24))->toDateString(),
        ]);
    }

    public function civp(): static
    {
        $startDate = Carbon::now()->subMonths(rand(1, 6))->startOfMonth();
        return $this->state([
            'contract_type' => 'CIVP',
            'start_date'    => $startDate->toDateString(),
            'end_date'      => $startDate->copy()->addMonths(rand(6, 12))->toDateString(),
            'cnss'          => null,
        ]);
    }

    public function karama(): static
    {
        $startDate = Carbon::now()->subMonths(rand(1, 6))->startOfMonth();
        return $this->state([
            'contract_type' => 'Karama',
            'start_date'    => $startDate->toDateString(),
            'end_date'      => $startDate->copy()->addMonths(rand(6, 12))->toDateString(),
        ]);
    }
}