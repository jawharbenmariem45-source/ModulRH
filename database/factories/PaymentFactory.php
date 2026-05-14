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
        $employer   = Employer::inRandomOrder()->first();
        $baseSalary = is_numeric($employer->salary)
            ? (float) $employer->salary
            : $this->faker->randomFloat(3, 800, 5000);

        $overtimeHours  = $this->faker->randomFloat(3, 0, 50);
        $overtimeAmount = round($overtimeHours * ($baseSalary / 208) * 1.25, 3);
        $bonuses        = $this->faker->randomFloat(3, 0, 300);
        $allowances     = $this->faker->randomFloat(3, 0, 200);
        $grossSalary    = round($baseSalary + $overtimeAmount + $bonuses + $allowances, 3);
        $cnss           = round($grossSalary * 0.0918, 3);
        $css            = round($grossSalary * 0.01, 3);
        $irpp           = round(($grossSalary - $cnss) * 0.15, 3);
        $amount         = round($grossSalary - $cnss - $css - $irpp, 3);
        $launchDate     = $this->faker->dateTimeBetween('-2 years', 'now');
        $doneTime       = Carbon::parse($launchDate)->addMinutes(rand(1, 60));

        $months = [
            'JANVIER', 'FEVRIER', 'MARS', 'AVRIL', 'MAI', 'JUIN',
            'JUILLET', 'AOUT', 'SEPTEMBRE', 'OCTOBRE', 'NOVEMBRE', 'DECEMBRE'
        ];

        $baseSalarySale = $this->faker->randomElement([
            $baseSalary, '$' . $baseSalary, $baseSalary . 'DT',
            str_replace('.', ',', $baseSalary), '€' . $baseSalary,
            0, -rand(100, 500), rand(10000, 99999),
        ]);

        $grossSalarySale = $this->faker->randomElement([
            $grossSalary, '$' . $grossSalary,
            str_replace('.', ',', $grossSalary), 0, -$grossSalary,
        ]);

        $amountSale = $this->faker->randomElement([
            $amount, '$' . $amount, str_replace('.', ',', $amount),
            0, -$amount, rand(10000, 99999),
        ]);

        $launchDateSale = $this->faker->randomElement([
            $launchDate->format('Y-m-d H:i:s'),
            $launchDate->format('d/m/Y H:i:s'),
            $launchDate->format('d-m-Y H:i:s'),
            $launchDate->format('m/d/Y H:i:s'),
        ]);

        $referenceSale = $this->faker->randomElement([
            'PAY-' . strtoupper($this->faker->bothify('??####')),
            'pay-' . $this->faker->bothify('??####'),
            $this->faker->bothify('??####'),
            'PAY_' . strtoupper($this->faker->bothify('??####')),
            null,
        ]);

        $monthSale = $this->faker->randomElement([
            $this->faker->randomElement($months),
            strtolower($this->faker->randomElement($months)),
            $this->faker->numberBetween(1, 12),
            'N/A',
        ]);

        $yearSale = $this->faker->randomElement([
            (string) $this->faker->numberBetween(2022, 2026),
            $this->faker->numberBetween(2022, 2026),
            '20' . $this->faker->numerify('##'),
            null,
        ]);

        return [
            'reference'       => $referenceSale,
            'employer_id'     => $employer->id,
            'contract_type'   => $employer->contract_type,
            'base_salary'     => $baseSalarySale,
            'overtime_hours'  => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'bonuses'         => $bonuses,
            'allowances'      => $allowances,
            'gross_salary'    => $grossSalarySale,
            'cnss'            => $cnss,
            'irpp'            => $irpp,
            'css'             => $css,
            'amount'          => $amountSale,
            'launch_date'     => $launchDateSale,
            'done_time'       => $doneTime,
            'month'           => $monthSale,
            'year'            => $yearSale,
        ];
    }
}