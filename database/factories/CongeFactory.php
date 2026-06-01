<?php

namespace Database\Factories;

use App\Models\Conge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CongeFactory extends Factory
{
    protected $model = Conge::class;

    // Jours fériés Tunisie 2026 fixes (MM-DD)
    private static array $FERIES_FIXES = [
        '01-01','03-20','04-09','05-01','06-01','07-25','08-13','10-15','12-17',
    ];
    // Variables islamiques 2026
    private static array $FERIES_VAR = [
        '2026-03-20','2026-03-21','2026-05-26','2026-05-27','2026-06-15','2026-08-24',
    ];

    private function estJourFerie(Carbon $date): bool
    {
        if (in_array($date->format('m-d'), self::$FERIES_FIXES)) return true;
        return in_array($date->format('Y-m-d'), self::$FERIES_VAR);
    }

    private function compterJoursOuvres(Carbon $debut, Carbon $fin): int
    {
        $jours = 0;
        $cur   = $debut->copy()->startOfDay();
        $f     = $fin->copy()->startOfDay();
        while ($cur->lte($f)) {
            if (!$cur->isWeekend() && !$this->estJourFerie($cur)) $jours++;
            $cur->addDay();
        }
        return max($jours, 1);
    }

    public function definition(): array
    {
        $user   = User::role('employer')->inRandomOrder()->first();
        $gender = $user?->gender ?? 'Homme';

        // ── Règles légales Tunisie par type ───────────────────────────────────
        // Congé Annuel   : selon solde acquis (max 22j ouvrés/an)
        // Maladie        : 1-15 jours (certificat médical)
        // Maternité      : 30 jours (Femme uniquement — Art. 64 CT)
        // Paternité      : 3 jours  (Homme uniquement — Art. 64 bis CT)
        // Sans solde     : 1-15 jours (accord mutuel)

        // Types disponibles selon genre
        if ($gender === 'Femme') {
            $typesDisponibles = ['Congé Annuel', 'Congé Annuel', 'Maladie', 'Maternité', 'Sans solde'];
        } else {
            $typesDisponibles = ['Congé Annuel', 'Congé Annuel', 'Maladie', 'Paternité', 'Sans solde'];
        }

        $type = $typesDisponibles[array_rand($typesDisponibles)];

        // Durée selon type (en jours ouvrés)
        switch ($type) {
            case 'Congé Annuel':
                // Solde acquis selon ancienneté (1.833j/mois)
                $moisAnciennete = 0;
                if ($user?->start_date) {
                    try { $moisAnciennete = Carbon::parse($user->start_date)->diffInMonths(Carbon::now()); }
                    catch (\Exception $e) {}
                }
                $soldeMax = min(round($moisAnciennete * 1.833), 22);
                $soldeMax = max($soldeMax, 1);
                $nbJoursOuvres = rand(1, min($soldeMax, 10)); // max 10 par demande
                break;

            case 'Maladie':
                $nbJoursOuvres = rand(1, 10); // certificat médical
                break;

            case 'Maternité':
                $nbJoursOuvres = 22; // 30 jours calendaires ≈ 22 ouvrés
                break;

            case 'Paternité':
                $nbJoursOuvres = 3; // 3 jours légaux
                break;

            case 'Sans solde':
                $nbJoursOuvres = rand(1, 10);
                break;

            default:
                $nbJoursOuvres = rand(1, 5);
        }

        // Générer les dates — trouver une fin cohérente en jours ouvrés
        $startDate = Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'));
        // Avancer si week-end ou férié
        while ($startDate->isWeekend() || $this->estJourFerie($startDate)) {
            $startDate->addDay();
        }

        // Calculer end_date en ajoutant des jours ouvrés
        $endDate = $startDate->copy();
        $ouvresComptés = 1;
        while ($ouvresComptés < $nbJoursOuvres) {
            $endDate->addDay();
            if (!$endDate->isWeekend() && !$this->estJourFerie($endDate)) {
                $ouvresComptés++;
            }
        }

        // Vérification
        $joursOuvresReels = $this->compterJoursOuvres($startDate, $endDate);

        // Raisons par type
        $raisons = [
            'Congé Annuel' => ['Congé annuel planifié', 'Vacances', 'Repos annuel'],
            'Maladie'      => ['Arrêt maladie prescrit', 'Grippe saisonnière', 'Infection respiratoire'],
            'Maternité'    => ['Congé maternité légal (Art. 64 CT)'],
            'Paternité'    => ['Congé paternité légal (Art. 64 bis CT)'],
            'Sans solde'   => ['Projet personnel', 'Raisons familiales', 'Voyage'],
        ];
        $listeRaisons = $raisons[$type] ?? ['Congé'];
        $reason = $listeRaisons[array_rand($listeRaisons)];

        // Document selon type
        $document = null;
        if (in_array($type, ['Maladie', 'Maternité', 'Paternité'])) {
            $document = 'documents/justificatif_' . uniqid() . '.pdf';
        }

        $status = $this->faker->randomElement([
            'Approuvé', 'Approuvé', 'Approuvé',
            'En attente', 'En attente',
            'Refusé',
        ]);

        return [
            'user_id'    => $user?->id ?? 1,
            'type'       => $type,
            'start_date' => $startDate->toDateString(),
            'end_date'   => $endDate->toDateString(),
            'days_count' => $joursOuvresReels, // jours ouvrés réels
            'reason'     => $reason,
            'document'   => $document,
            'status'     => $status,
        ];
    }

    public function approuve(): static  { return $this->state(['status' => 'Approuvé']); }
    public function enAttente(): static { return $this->state(['status' => 'En attente']); }
    public function refuse(): static    { return $this->state(['status' => 'Refusé']); }
}