<?php

namespace Database\Factories;

use App\Models\Conge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CongeFactory extends Factory
{
    protected $model = Conge::class;

    private static array $types = [
        'Congé annuel', 'Congé maladie', 'Congé maternité',
        'Congé sans solde', 'Congé exceptionnel',
    ];

    private static array $durees = [
        'Congé annuel'       => [30, 60],
        'Congé maladie'      => [1,  14],
        'Congé maternité'    => [60, 98],
        'Congé sans solde'   => [7,  30],
        'Congé exceptionnel' => [1,   5],
    ];

    private static array $raisons = [
        'Congé annuel'       => ['Congé annuel planifié', 'Vacances en famille', 'Repos annuel'],
        'Congé maladie'      => ['Arrêt maladie prescrit', 'Grippe saisonnière', 'Infection respiratoire'],
        'Congé maternité'    => ['Congé maternité légal', 'Congé prénatal et postnatal'],
        'Congé sans solde'   => ['Projet personnel', 'Voyage à l\'étranger', 'Raisons familiales'],
        'Congé exceptionnel' => ['Mariage', 'Décès d\'un proche', 'Naissance d\'un enfant'],
    ];

    private static array $documents = [
        'Congé maladie'      => 'documents/certificat_medical_',
        'Congé maternité'    => 'documents/attestation_maternite_',
        'Congé sans solde'   => 'documents/demande_conge_sans_solde_',
        'Congé exceptionnel' => 'documents/justificatif_exceptionnel_',
    ];

    public function definition(): array
    {
        $type    = self::$types[array_rand(self::$types)];
        $duree   = self::$durees[$type];
        $nbJours = rand($duree[0], $duree[1]);

        $startDate = Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'));
        $endDate   = $startDate->copy()->addDays($nbJours - 1);

        $raisons  = self::$raisons[$type];
        $reason   = $raisons[array_rand($raisons)];

        $document = null;
        if (isset(self::$documents[$type])) {
            $document = self::$documents[$type] . uniqid() . '.pdf';
        }

        $status = $this->faker->randomElement([
            'Approuvé', 'Approuvé', 'Approuvé',
            'En attente', 'En attente',
            'Refusé',
        ]);

        return [
            'user_id'    => User::role('employer')->inRandomOrder()->first()?->id ?? 1,
            'type'       => $type,
            'start_date' => $startDate->toDateString(),
            'end_date'   => $endDate->toDateString(),
            'days_count' => $nbJours,
            'reason'     => $reason,
            'document'   => $document,
            'status'     => $status,
        ];
    }

    public function approuve(): static  { return $this->state(['status' => 'Approuvé']); }
    public function enAttente(): static { return $this->state(['status' => 'En attente']); }
    public function refuse(): static    { return $this->state(['status' => 'Refusé']); }
}