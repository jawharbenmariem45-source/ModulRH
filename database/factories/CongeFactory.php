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
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate   = $this->faker->dateTimeBetween($startDate, '+30 days');

        $startDateSale = $this->faker->randomElement([
            $startDate->format('Y-m-d'),
            $startDate->format('d/m/Y'),
            $startDate->format('d-m-Y'),
            $startDate->format('m/d/Y'),
            $startDate->format('d.m.Y'),
        ]);

        $endDateSale = $this->faker->randomElement([
            $endDate->format('Y-m-d'),
            $endDate->format('d/m/Y'),
            $endDate->format('d-m-Y'),
            $endDate->format('m/d/Y'),
            $endDate->format('d.m.Y'),
        ]);

        if ($this->faker->boolean(20)) {
            $startDateSale = $endDate->format('Y-m-d');
            $endDateSale   = $startDate->format('Y-m-d');
        }

        $daysReal      = (int) $startDate->diff($endDate)->days;
        $daysCountSale = $this->faker->randomElement([
            $daysReal,
            $daysReal + rand(1, 10),
            0,
            -rand(1, 5),
            rand(100, 999),
        ]);

        $typeSale = $this->faker->randomElement([
            'Congé annuel', 'Congé maladie', 'Congé maternité',
            'Congé sans solde', 'Congé exceptionnel',
            'conge annuel', 'CONGE MALADIE', 'Congé  annuel',
            'Maternité', 'N/A', null,
        ]);

        $document = $this->faker->randomElement([
            null, null, null,
            'leaves/documents/fake_' . uniqid() . '.pdf',
        ]);

        return [
            'employer_id' => Employer::inRandomOrder()->first()?->id ?? 1,
            'type'        => $typeSale,
            'start_date'  => $startDateSale,
            'end_date'    => $endDateSale,
            'days_count'  => $daysCountSale,
            'reason'      => $this->faker->optional(0.7)->sentence(6),
            'document'    => $document,
            'status'      => $this->faker->randomElement([
                'En attente', 'Approuvé', 'Refusé',
                'approuvé', 'APPROUVE', 'en attente', null,
            ]),
            'comment'     => $this->faker->optional(0.4)->sentence(10),
        ];
    }

    public function approuve(): static
    {
        return $this->state(['status' => 'Approuvé']);
    }

    public function enAttente(): static
    {
        return $this->state(['status' => 'En attente']);
    }

    public function refuse(): static
    {
        return $this->state(['status' => 'Refusé']);
    }
}