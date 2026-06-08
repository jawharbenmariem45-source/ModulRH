<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('attendances')->truncate();
        DB::table('leaves')->where('status', 'approved')->delete();

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('Aucun utilisateur trouvé — lancez d\'abord UserSeeder.');
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $this->genererPointages($user->id, 3);
            $this->genererConges($user->id);
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('✓ Pointages et congés générés.');
    }

    // =========================================================
    // Génère les pointages entrée/sortie sur N mois
    // =========================================================
    private function genererPointages(int $userId, int $mois): void
    {
        $statuts = [
            'present', 'present', 'present', 'present', 'present',
            'present', 'present',
            'late', 'late',
            'absent',
            'on_leave',
        ];

        $debut = Carbon::now()->subMonths($mois)->startOfDay();
        $fin   = Carbon::now();
        $jour  = $debut->copy();
        $rows  = [];

        while ($jour->lte($fin)) {
            if (!$jour->isWeekend()) {
                $statut = $statuts[array_rand($statuts)];

                if (in_array($statut, ['present', 'late'])) {
                    $heureEntree = $statut === 'late'
                        ? $jour->copy()->setTime(rand(9, 10), rand(0, 59))
                        : $jour->copy()->setTime(8, rand(0, 15));

                    $heureSortie = $jour->copy()->setTime(rand(17, 18), rand(0, 59));

                    // Entrée matin
                    $rows[] = [
                        'user_id'            => $userId,
                        'type'               => 'entree',
                        'pointage_at'        => $heureEntree->toDateTimeString(),
                        'shift_user_id'      => null,
                        'face_matched'       => (bool) rand(0, 1),
                        'tx_hash'            => null,
                        'block_number'       => null,
                        'blockchain_statut'  => 'pending',
                        'device_ref'         => null,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];

                    // Sortie soir
                    $rows[] = [
                        'user_id'            => $userId,
                        'type'               => 'sortie',
                        'pointage_at'        => $heureSortie->toDateTimeString(),
                        'shift_user_id'      => null,
                        'face_matched'       => (bool) rand(0, 1),
                        'tx_hash'            => null,
                        'block_number'       => null,
                        'blockchain_statut'  => 'pending',
                        'device_ref'         => null,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                }
            }
            $jour->addDay();
        }

        if (!empty($rows)) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('attendances')->insert($chunk);
            }
        }
    }

    // =========================================================
    // Génère 1 à 3 congés par utilisateur
    // =========================================================
    private function genererConges(int $userId): void
    {
        $types = ['annuel', 'maladie', 'maternité', 'exceptionnel'];

        $nbConges = rand(1, 3);

        for ($i = 0; $i < $nbConges; $i++) {
            $startDate = Carbon::now()->subMonths(rand(1, 6))->startOfMonth()->addDays(rand(1, 20));
            $daysCount = rand(1, 5);
            $endDate   = $startDate->copy()->addDays($daysCount - 1);
            $statuts   = ['pending', 'approved', 'rejected'];

            DB::table('leaves')->insert([
                'user_id'    => $userId,
                'type'       => $types[array_rand($types)],
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'days_count' => $daysCount,
                'reason'     => 'Congé généré automatiquement',
                'document'   => null,
                'status'     => $statuts[array_rand($statuts)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}