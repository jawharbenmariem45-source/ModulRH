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
    private int $palierIndex = 2;

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
        // 3. GÉNÉRATION DYNAMIQUE PAR PALIER
        // ══════════════════════════════════════════════
        $paliers = [
            ['min' => 1,   'max' => 9,   'mois' => 6,  'label' => 'Micro entreprise (1-9 employés)'],
            ['min' => 10,  'max' => 49,  'mois' => 12, 'label' => 'Petite entreprise (10-49 employés)'],
            ['min' => 200, 'max' => 249, 'mois' => 60, 'label' => 'Moyenne entreprise (200-249 employés)'],
        ];

        $moisFrancais = [
            1  => 'JANVIER',   2  => 'FEVRIER',   3  => 'MARS',
            4  => 'AVRIL',     5  => 'MAI',        6  => 'JUIN',
            7  => 'JUILLET',   8  => 'AOUT',       9  => 'SEPTEMBRE',
            10 => 'OCTOBRE',   11 => 'NOVEMBRE',   12 => 'DECEMBRE',
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

        $this->command->warn("-> Création de {$nombreEmployes} employés...");

        $employers = Employer::factory()
            ->count($nombreEmployes)
            ->ancien()
            ->create();

        $this->command->info("✓ {$nombreEmployes} employés créés.");
        $this->command->warn("-> Génération des données liées ({$moisHistorique} mois d'historique)...");

        foreach ($employers as $employer) {

            // -- Salaires (1 par mois d'historique) --
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

            // -- Paiements (1 par mois d'historique avec le bon mois/année) --
            try {
                for ($m = 0; $m < $moisHistorique; $m++) {
                    $date  = Carbon::now()->subMonths($m);
                    $mois  = $moisFrancais[$date->month];
                    $annee = (string) $date->year;

                    Payment::factory()->create([
                        'employer_id' => $employer->id,
                        'month'       => $mois,   // ✅ vrai mois
                        'year'        => $annee,  // ✅ vraie année
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->error('Payment: ' . $e->getMessage());
            }

            // -- Congés (1 à 4 par employé) --
            try {
                Conge::factory()
                    ->count(rand(1, 4))
                    ->create(['employer_id' => $employer->id]);
            } catch (\Exception $e) {
                $this->command->error('Conge: ' . $e->getMessage());
            }

            // -- Pointages (jours ouvrables sur la période) --
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