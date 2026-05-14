<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Conge;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    // ══════════════════════════════════════════════
    // 👇 CHOISISSEZ LA COMPANY ICI
    //    'SummitRise' → micro   (1-9 employés)
    //    'TechNova'   → petite  (10-49 employés)
    //    'AlphaCorp'  → moyenne (50-249 employés)
    // ══════════════════════════════════════════════
    private string $companyName = 'TechNova';

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CompanySeeder::class,
            DepartementSeeder::class,
            UserSeeder::class,
            ContractSeeder::class,
            EmployerSeeder::class,
        ]);

        $moisFrancais = [
            1  => 'JANVIER',  2  => 'FEVRIER',  3  => 'MARS',
            4  => 'AVRIL',    5  => 'MAI',       6  => 'JUIN',
            7  => 'JUILLET',  8  => 'AOUT',      9  => 'SEPTEMBRE',
            10 => 'OCTOBRE',  11 => 'NOVEMBRE',  12 => 'DECEMBRE',
        ];

        $paliers = [
            'micro'   => ['min' => 1,  'max' => 9,   'mois' => 13, 'label' => 'Micro entreprise (1-9 employés)'],
            'petite'  => ['min' => 10, 'max' => 49,  'mois' => 13, 'label' => 'Petite entreprise (10-49 employés)'],
            'moyenne' => ['min' => 50, 'max' => 249, 'mois' => 13, 'label' => 'Moyenne entreprise (50-249 employés)'],
        ];

        $company = Company::where('name', $this->companyName)->first();

        if (!$company) {
            $this->command->error("Company '{$this->companyName}' introuvable.");
            return;
        }

        $palierChoisi   = $paliers[$company->type];
        $nombreEmployes = rand($palierChoisi['min'], $palierChoisi['max']);
        $moisHistorique = rand(6, $palierChoisi['mois']);

        // ── Données de l'employé fixe ─────────────────────────
        $employerFixe = Employer::where('email', 'employer@gmail.com')->first();

        if ($employerFixe) {
            $this->command->info('-> Génération des données pour l\'employé fixe...');

            for ($m = 0; $m < 6; $m++) {
                $date  = Carbon::now()->subMonths($m);
                $mois  = $moisFrancais[$date->month];
                $annee = (string) $date->year;

                try {
                    Payment::factory()->create([
                        'employer_id' => $employerFixe->id,
                        'month'       => $mois,
                        'year'        => $annee,
                    ]);
                } catch (\Exception $e) {
                    $this->command->error('Payment fixe: ' . $e->getMessage());
                }
            }

            try {
                Conge::factory()->count(3)->create(['employer_id' => $employerFixe->id]);
            } catch (\Exception $e) {
                $this->command->error('Conge fixe: ' . $e->getMessage());
            }

            try {
                $debut = Carbon::now()->subMonths(6);
                $fin   = Carbon::now();
                $jour  = $debut->copy();
                while ($jour->lte($fin)) {
                    if (!$jour->isWeekend()) {
                        Attendance::factory()->create([
                            'employer_id' => $employerFixe->id,
                            'date'        => $jour->format('Y-m-d'),
                        ]);
                    }
                    $jour->addDay();
                }
            } catch (\Exception $e) {
                $this->command->error('Attendance fixe: ' . $e->getMessage());
            }

            $this->command->info('✓ Données employé fixe générées.');
        }

        // ── Génération dynamique ──────────────────────────────
        $this->command->line('══════════════════════════════════════════════');
        $this->command->info("  {$palierChoisi['label']}");
        $this->command->line("  Company    : {$company->name}");
        $this->command->line("  Type       : {$company->type}");
        $this->command->line("  Employés   : {$nombreEmployes} (générés dynamiquement)");
        $this->command->line("  Historique : {$moisHistorique} mois");
        $this->command->line('══════════════════════════════════════════════');

        $nbCDI     = (int) round($nombreEmployes * 0.55);
        $nbCDD     = (int) round($nombreEmployes * 0.25);
        $nbCIVP    = (int) round($nombreEmployes * 0.12);
        $nbKarama  = max(0, $nombreEmployes - $nbCDI - $nbCDD - $nbCIVP);
        $nbAnciens = (int) round($nbCDI * 0.30);
        $nbRecents = $nbCDI - $nbAnciens;

        $this->command->warn("-> Création de {$nombreEmployes} employés...");
        $this->command->line("  CDI anciens : {$nbAnciens}");
        $this->command->line("  CDI récents : {$nbRecents}");
        $this->command->line("  CDD         : {$nbCDD}");
        $this->command->line("  CIVP        : {$nbCIVP}");
        $this->command->line("  Karama      : {$nbKarama}");

        $employers = collect()
            ->merge(Employer::factory($nbAnciens)->ancien()->create())
            ->merge(Employer::factory($nbRecents)->cdi()->create())
            ->merge(Employer::factory($nbCDD)->cdd()->create())
            ->merge(Employer::factory($nbCIVP)->civp()->create())
            ->merge(Employer::factory($nbKarama)->karama()->create());

        $this->command->info("✓ {$nombreEmployes} employés créés.");
        $this->command->warn("-> Génération des données liées ({$moisHistorique} mois d'historique)...");

        foreach ($employers as $employer) {

            try {
                for ($m = 0; $m < $moisHistorique; $m++) {
                    $date  = Carbon::now()->subMonths($m);
                    $mois  = $moisFrancais[$date->month];
                    $annee = (string) $date->year;

                    Payment::factory()->create([
                        'employer_id' => $employer->id,
                        'month'       => $mois,
                        'year'        => $annee,
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->error('Payment: ' . $e->getMessage());
            }

            try {
                Conge::factory()
                    ->count(rand(1, 4))
                    ->create(['employer_id' => $employer->id]);
            } catch (\Exception $e) {
                $this->command->error('Conge: ' . $e->getMessage());
            }

            try {
                $debut = Carbon::now()->subMonths($moisHistorique);
                $fin   = Carbon::now();
                $jour  = $debut->copy();

                while ($jour->lte($fin)) {
                    if (!$jour->isWeekend()) {
                        Attendance::factory()->create([
                            'employer_id' => $employer->id,
                            'date'        => $jour->format('Y-m-d'),
                        ]);
                    }
                    $jour->addDay();
                }
            } catch (\Exception $e) {
                $this->command->error('Attendance: ' . $e->getMessage());
            }
        }

        $this->command->info('✓ Base de données initialisée et synchronisée avec succès !');
    }
}