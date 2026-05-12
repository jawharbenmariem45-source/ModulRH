<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Salaire;
use App\Models\Conge;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Contract;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    // ══════════════════════════════════════════════
    // 👇 CHOISISSEZ VOTRE PALIER ICI
    // 0 = Micro entreprise   (1-9 employés)
    // 1 = Petite entreprise  (10-49 employés)
    // 2 = Moyenne entreprise (200-249 employés)
    // ══════════════════════════════════════════════
    private int $palierIndex = 1;

    public function run(): void
    {
        // ══════════════════════════════════════════════
        // 1. SEEDERS FIXES (Données de structure)
        // ══════════════════════════════════════════════
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            CompanySeeder::class,
            DepartementSeeder::class,
            ConfigurationSeeder::class,
            UserSeeder::class,
            ContractSeeder::class,
        ]);

        // ══════════════════════════════════════════════
        // 2. EMPLOYÉS FIXES
        // ══════════════════════════════════════════════
        $this->call([
            EmployerSeeder::class,
        ]);

        // ══════════════════════════════════════════════
        // MOIS FRANÇAIS
        // ══════════════════════════════════════════════
        $moisFrancais = [
            1  => 'JANVIER',   2  => 'FEVRIER',   3  => 'MARS',
            4  => 'AVRIL',     5  => 'MAI',        6  => 'JUIN',
            7  => 'JUILLET',   8  => 'AOUT',       9  => 'SEPTEMBRE',
            10 => 'OCTOBRE',   11 => 'NOVEMBRE',   12 => 'DECEMBRE',
        ];

        // ══════════════════════════════════════════════
        // DONNÉES POUR L'EMPLOYÉ FIXE
        // ══════════════════════════════════════════════
        $employerFixe = Employer::where('email', 'employer@gmail.com')->first();

        if ($employerFixe) {
            $this->command->info('-> Génération des données pour l\'employé fixe...');

            for ($m = 0; $m < 6; $m++) {
                $date  = Carbon::now()->subMonths($m);
                $mois  = $moisFrancais[$date->month];
                $annee = (string) $date->year;

                try {
                    Salaire::create([
                        'employer_id' => $employerFixe->id,
                        'montant'     => 1500,
                        'created_at'  => $date,
                        'updated_at'  => $date,
                    ]);
                } catch (\Exception $e) {
                    $this->command->error('Salaire fixe: ' . $e->getMessage());
                }

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

        // ══════════════════════════════════════════════
        // 3. GÉNÉRATION DYNAMIQUE PAR PALIER
        // ══════════════════════════════════════════════
        $paliers = [
            ['min' => 1,   'max' => 9,   'mois' => 13,  'label' => 'Micro entreprise (1-9 employés)'],
            ['min' => 10,  'max' => 49,  'mois' => 13, 'label' => 'Petite entreprise (10-49 employés)'],
            ['min' => 200, 'max' => 249, 'mois' => 13, 'label' => 'Moyenne entreprise (200-249 employés)'],
        ];

        $palierChoisi   = $paliers[$this->palierIndex];
        $nombreEmployes = rand($palierChoisi['min'], $palierChoisi['max']);
        $moisHistorique = rand(6, $palierChoisi['mois']);

        $this->command->info('-----------------------------------------------');
        $this->command->info('Génération de la structure de l\'entreprise...');
        $this->command->info('-----------------------------------------------');
        $this->command->line('══════════════════════════════════════════════');
        $this->command->info("  {$palierChoisi['label']}");
        $this->command->line("  Employés   : {$nombreEmployes} (générés dynamiquement)");
        $this->command->line("  Historique : {$moisHistorique} mois");
        $this->command->line("  Données sales : 100%");
        $this->command->line('══════════════════════════════════════════════');

        // ── Distribution réaliste des contrats ────────────────
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

            // ── Salaires ──────────────────────────────────────
            try {
                for ($m = 0; $m < $moisHistorique; $m++) {
                    $date = Carbon::now()->subMonths($m);
                    Salaire::create([
                        'employer_id' => $employer->id,
                        'montant'     => $employer->salaire ?? rand(800, 5000),
                        'created_at'  => $date,
                        'updated_at'  => $date,
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->error('Salaire: ' . $e->getMessage());
            }

            // ── Paiements ─────────────────────────────────────
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

            // ── Congés ────────────────────────────────────────
            try {
                Conge::factory()
                    ->count(rand(1, 4))
                    ->create(['employer_id' => $employer->id]);
            } catch (\Exception $e) {
                $this->command->error('Conge: ' . $e->getMessage());
            }

            // ── Pointages ─────────────────────────────────────
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