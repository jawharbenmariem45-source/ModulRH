<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Leave;
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
            PostSeeder::class,
            ShiftSeeder::class,
            ShiftUserSeeder::class,
            UserSeeder::class,
            ContractSeeder::class,
        ]);

        // ── Employé fixe ──────────────────────────────────────────────────────
        $employerFixe = User::where('email', 'employer@gmail.com')->first();

        if ($employerFixe) {
            $this->command->info('-> Génération des données pour l\'employé fixe...');

            for ($m = 0; $m < 12; $m++) {
                $date = Carbon::now()->subMonths($m);
                try {
                    $moisInt    = $date->month;
                    $anneeInt   = $date->year;
                    $launchDate = Carbon::create($anneeInt, $moisInt, rand(25, 28), rand(8, 17), rand(0, 59));

                    Payment::factory()->forUser($employerFixe)->create([
                        'month'       => $moisInt,
                        'year'        => $anneeInt,
                        'launch_date' => $launchDate->toDateString(),
                        'done_time'   => $launchDate->copy()->addMinutes(rand(1, 30)),
                    ]);
                } catch (\Exception $e) {}
            }

            try {
                Leave::factory()->count(3)->create(['user_id' => $employerFixe->id]);
            } catch (\Exception $e) {}

            $this->genererPointages($employerFixe->id, 12);
            $this->command->info('✓ Données employé fixe générées.');
        }

        // ── Configuration par entreprise ──────────────────────────────────────
        $config = [
            'AlphaCorp'  => ['nombre' => 8,   'mois' => 12],
            'TechNova'   => ['nombre' => 35,  'mois' => 12],
            'SummitRise' => ['nombre' => 120, 'mois' => 12],
        ];

        foreach ($config as $nomCompany => $cfg) {
            $company = Company::where('name', $nomCompany)->first();
            if (!$company) { $this->command->error("'{$nomCompany}' introuvable."); continue; }

            $nombreEmployes = $cfg['nombre'];
            $moisHistorique = $cfg['mois'];

            $this->command->line('══════════════════════════════════════════════');
            $this->command->info("  {$company->name}");
            $this->command->line('══════════════════════════════════════════════');

            $nbCDI     = (int) round($nombreEmployes * 0.55);
            $nbCDD     = (int) round($nombreEmployes * 0.25);
            $nbCIVP    = (int) round($nombreEmployes * 0.12);
            $nbKarama  = max(0, $nombreEmployes - $nbCDI - $nbCDD - $nbCIVP);
            $nbAnciens = (int) round($nbCDI * 0.30);
            $nbRecents = $nbCDI - $nbAnciens;

            $this->command->warn("-> Création de {$nombreEmployes} employés...");

            $users = collect()
                ->merge(User::factory($nbAnciens)->ancien()->state(['company_id' => $company->id])->create())
                ->merge(User::factory($nbRecents)->cdi()->state(['company_id' => $company->id])->create())
                ->merge(User::factory($nbCDD)->cdd()->state(['company_id' => $company->id])->create())
                ->merge(User::factory($nbCIVP)->civp()->state(['company_id' => $company->id])->create())
                ->merge(User::factory($nbKarama)->karama()->state(['company_id' => $company->id])->create());

            foreach ($users as $user) {
                $user->syncRoles(['employer']);
            }

            $this->command->info("✓ {$nombreEmployes} employés créés.");
            $this->command->warn("-> Génération des données liées...");

            $bar = $this->command->getOutput()->createProgressBar($users->count());
            $bar->start();

            foreach ($users as $user) {
                // Paiements — 1 par mois
                for ($m = 0; $m < $moisHistorique; $m++) {
                    $date = Carbon::now()->subMonths($m);
                    try {
                        $moisInt    = $date->month;
                        $anneeInt   = $date->year;
                        $launchDate = Carbon::create($anneeInt, $moisInt, rand(25, 28), rand(8, 17), rand(0, 59));

                        Payment::factory()->forUser($user)->create([
                            'month'       => $moisInt,
                            'year'        => $anneeInt,
                            'launch_date' => $launchDate->toDateString(),
                            'done_time'   => $launchDate->copy()->addMinutes(rand(1, 30)),
                        ]);
                    } catch (\Exception $e) {}
                }

                // Congés
                try {
                    Leave::factory()->count(rand(1, 3))->create(['user_id' => $user->id]);
                } catch (\Exception $e) {}

                // Pointages
                $this->genererPointages($user->id, $moisHistorique);
                $bar->advance();
            }

            $bar->finish();
            $this->command->newLine();
            $this->command->info("✓ {$company->name} initialisée avec succès !");
        }

        $this->command->newLine();
        $this->command->info('✓ Base de données complète initialisée !');
        $this->command->line('  AlphaCorp  →   8 employés');
        $this->command->line('  TechNova   →  35 employés');
        $this->command->line('  SummitRise → 120 employés');
        $this->command->line('  Total      → 163 employés');
    }

    private function genererPointages(int $userId, int $mois): void
    {
        $statuts = [
            'present', 'present', 'present', 'present', 'present',
            'present', 'present', 'present',
            'late', 'late',
            'absent',
            'on_leave',
        ];

        $now   = now()->toDateTimeString();
        $data  = [];
        $debut = Carbon::now()->subMonths($mois)->startOfDay();
        $fin   = Carbon::now();
        $jour  = $debut->copy();

        while ($jour->lte($fin)) {
            if (!$jour->isWeekend()) {
                $statut = $statuts[array_rand($statuts)];

                if (in_array($statut, ['present', 'late'])) {
                    $heure  = $statut === 'late' ? rand(9, 10) : 8;
                    $minute = rand(0, 59);

                    // Entrée
                    $data[] = [
                        'user_id'           => $userId,
                        'type'              => 'entree',
                        'pointage_at'       => $jour->copy()->setTime($heure, $minute)->toDateTimeString(),
                        'shift_user_id'     => null,
                        'face_matched'      => false,
                        'tx_hash'           => null,
                        'block_number'      => null,
                        'blockchain_statut' => 'pending',
                        'device_ref'        => null,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];

                    // Sortie
                    $data[] = [
                        'user_id'           => $userId,
                        'type'              => 'sortie',
                        'pointage_at'       => $jour->copy()->setTime(rand(17, 18), rand(0, 59))->toDateTimeString(),
                        'shift_user_id'     => null,
                        'face_matched'      => false,
                        'tx_hash'           => null,
                        'block_number'      => null,
                        'blockchain_statut' => 'pending',
                        'device_ref'        => null,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
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