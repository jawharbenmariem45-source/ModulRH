<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Conge;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
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
            'micro'   => ['nombre' => 9,   'mois' => 6, 'label' => 'Micro entreprise'],
            'petite'  => ['nombre' => 49,  'mois' => 6, 'label' => 'Petite entreprise'],
            'moyenne' => ['nombre' => 249, 'mois' => 6, 'label' => 'Moyenne entreprise'],
        ];

        // ── Employé fixe ──────────────────────────────────────
        $employerFixe = Employer::where('email', 'employer@gmail.com')->first();

        if ($employerFixe) {
            $this->command->info('-> Génération des données pour l\'employé fixe...');

            for ($m = 0; $m < 6; $m++) {
                $date = Carbon::now()->subMonths($m);
                try {
                    Payment::factory()->create([
                        'employer_id' => $employerFixe->id,
                        'month'       => $moisFrancais[$date->month],
                        'year'        => (string) $date->year,
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

            $this->genererPointages($employerFixe->id, 6);
            $this->command->info('✓ Données employé fixe générées.');
        }

        // ── Génération pour les 3 companies ──────────────────
        $companies = Company::all();

        foreach ($companies as $company) {
            $palier         = $paliers[$company->type];
            $nombreEmployes = $palier['nombre'];
            $moisHistorique = $palier['mois'];

            $this->command->line('══════════════════════════════════════════════');
            $this->command->info("  {$palier['label']} — {$company->name}");
            $this->command->line("  Employés   : {$nombreEmployes}");
            $this->command->line("  Historique : {$moisHistorique} mois");
            $this->command->line('══════════════════════════════════════════════');

            $nbCDI     = (int) round($nombreEmployes * 0.55);
            $nbCDD     = (int) round($nombreEmployes * 0.25);
            $nbCIVP    = (int) round($nombreEmployes * 0.12);
            $nbKarama  = max(0, $nombreEmployes - $nbCDI - $nbCDD - $nbCIVP);
            $nbAnciens = (int) round($nbCDI * 0.30);
            $nbRecents = $nbCDI - $nbAnciens;

            $this->command->warn("-> Création de {$nombreEmployes} employés pour {$company->name}...");
            $this->command->line("  CDI anciens : {$nbAnciens}");
            $this->command->line("  CDI récents : {$nbRecents}");
            $this->command->line("  CDD         : {$nbCDD}");
            $this->command->line("  CIVP        : {$nbCIVP}");
            $this->command->line("  Karama      : {$nbKarama}");

            $employers = collect()
                ->merge(Employer::factory($nbAnciens)->ancien()->state(['company_id' => $company->id])->create())
                ->merge(Employer::factory($nbRecents)->cdi()->state(['company_id' => $company->id])->create())
                ->merge(Employer::factory($nbCDD)->cdd()->state(['company_id' => $company->id])->create())
                ->merge(Employer::factory($nbCIVP)->civp()->state(['company_id' => $company->id])->create())
                ->merge(Employer::factory($nbKarama)->karama()->state(['company_id' => $company->id])->create());

            $this->command->info("✓ {$nombreEmployes} employés créés pour {$company->name}.");
            $this->command->warn("-> Génération des données liées...");

            foreach ($employers as $employer) {

                try {
                    for ($m = 0; $m < $moisHistorique; $m++) {
                        $date = Carbon::now()->subMonths($m);
                        Payment::factory()->create([
                            'employer_id' => $employer->id,
                            'month'       => $moisFrancais[$date->month],
                            'year'        => (string) $date->year,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->command->error('Payment: ' . $e->getMessage());
                }

                try {
                    Conge::factory()->count(rand(1, 3))->create(['employer_id' => $employer->id]);
                } catch (\Exception $e) {
                    $this->command->error('Conge: ' . $e->getMessage());
                }

                $this->genererPointages($employer->id, $moisHistorique);
            }

            $this->command->info("✓ {$company->name} initialisée !");
        }

        $this->command->info('✓ Base de données complète initialisée avec succès !');
    }

    private function genererPointages(int $employerId, int $mois): void
    {
        $statuts = ['present', 'present', 'present', 'present', 'absent', 'late', 'on_leave'];
        $now     = now()->toDateTimeString();
        $data    = [];

        $debut = Carbon::now()->subMonths($mois)->startOfDay();
        $fin   = Carbon::now();
        $jour  = $debut->copy();

        while ($jour->lte($fin)) {
            if (!$jour->isWeekend()) {
                $statut = $statuts[array_rand($statuts)];

                $morningIn  = null;
                $morningOut = null;
                $afterIn    = null;
                $afterOut   = null;

                if (in_array($statut, ['present', 'late'])) {
                    $heure      = $statut === 'late' ? rand(9, 10) : 8;
                    $morningIn  = $jour->copy()->setTime($heure, rand(0, 59))->toDateTimeString();
                    $morningOut = $jour->copy()->setTime(12, rand(0, 30))->toDateTimeString();
                    $afterIn    = $jour->copy()->setTime(13, rand(0, 30))->toDateTimeString();
                    $afterOut   = $jour->copy()->setTime(17, rand(0, 59))->toDateTimeString();
                }

                $data[] = [
                    'employer_id'         => $employerId,
                    'date'                => $jour->format('Y-m-d'),
                    'morning_check_in'    => $morningIn,
                    'morning_check_out'   => $morningOut,
                    'afternoon_check_in'  => $afterIn,
                    'afternoon_check_out' => $afterOut,
                    'status'              => $statut,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }
            $jour->addDay();
        }

        if (!empty($data)) {
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('attendances')->insert($chunk);
            }
        }
    }
}