<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employer;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('attendances')->truncate();
        DB::table('conges')->where('statut', 'Approuvé')->delete();

        // Récupère les employés par email (IDs dynamiques)
        $employers = [
            'cdd'    => Employer::where('email', 'employer@gmail.com')->first(),
            'cdi'    => Employer::where('email', 'ahmed.ben.ali@gmail.com')->first(),
            'civp'   => Employer::where('email', 'salma.trabelsi@gmail.com')->first(),
            'karama' => Employer::where('email', 'yassine.maaloul@gmail.com')->first(),
        ];

        $mois  = (int) now()->format('m');
        $annee = (int) now()->format('Y');

        foreach ($employers as $type => $employer) {
            if (!$employer) {
                $this->command->warn("Employé $type introuvable — lancez d'abord EmployerSeeder.");
                continue;
            }

            $this->genererPointage($employer->id, $type, $mois, $annee);
            $this->genererConge($employer->id, $type, $mois, $annee);

            $this->command->info("✓ Pointage + congé généré pour {$employer->prenom} {$employer->nom} ($type)");
        }
    }

    // =========================================================
    // Pointage du mois selon le profil
    // =========================================================
    private function genererPointage(int $employerId, string $profil, int $mois, int $annee): void
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        // Jours spéciaux par profil
        $config = match($profil) {
            'cdd'    => ['absences' => [8],         'conges' => [20, 21],     'retards' => [3, 14],    'hs' => true],
            'cdi'    => ['absences' => [12],        'conges' => [19, 20, 21], 'retards' => [5],        'hs' => true],
            'civp'   => ['absences' => [7, 15],     'conges' => [22],         'retards' => [2, 9, 16], 'hs' => false],
            'karama' => ['absences' => [10],        'conges' => [26, 27],     'retards' => [4],        'hs' => true],
            default  => ['absences' => [],          'conges' => [],           'retards' => [],         'hs' => false],
        };

        $rows = [];

        for ($date = $debut->copy(); $date->lte($fin); $date->addDay()) {
            if ($date->isWeekend()) continue;

            $jour   = $date->day;
            $statut = 'present';

            if (in_array($jour, $config['absences'])) $statut = 'absent';
            elseif (in_array($jour, $config['conges'])) $statut = 'on_leave';
            elseif (in_array($jour, $config['retards'])) $statut = 'late';

            $row = [
                'employer_id'              => $employerId,
                'date'                     => $date->toDateString(),
                'check_in_morning_time'    => null,
                'check_out_morning_time'   => null,
                'check_in_afternoon_time'  => null,
                'check_out_afternoon_time' => null,
                'status'                   => $statut,
                'created_at'               => now(),
                'updated_at'               => now(),
            ];

            if (in_array($statut, ['present', 'late'])) {
                $sorties = $config['hs']
                    ? ['17:00', '17:30', '17:30', '18:00']
                    : ['17:00', '17:00', '17:00'];

                $row['check_in_morning_time']    = $date->copy()->setTimeFromTimeString($statut === 'late' ? '08:30' : '08:00');
                $row['check_out_morning_time']   = $date->copy()->setTimeFromTimeString('12:00');
                $row['check_in_afternoon_time']  = $date->copy()->setTimeFromTimeString('13:00');
                $row['check_out_afternoon_time'] = $date->copy()->setTimeFromTimeString($sorties[array_rand($sorties)]);
            }

            $rows[] = $row;
        }

        DB::table('attendances')->insert($rows);
    }

    // =========================================================
    // Congés selon le profil
    // =========================================================
    private function genererConge(int $employerId, string $profil, int $mois, int $annee): void
    {
        $congesData = match($profil) {
            'cdd' => [[
                'type'         => 'Congé annuel',
                'date_debut'   => Carbon::create($annee, $mois, 20)->toDateString(),
                'date_fin'     => Carbon::create($annee, $mois, 21)->toDateString(),
                'nombre_jours' => 2,
                'motif'        => 'Congé personnel',
                'statut'       => 'Approuvé',
            ]],
            'cdi' => [[
                'type'         => 'Congé annuel',
                'date_debut'   => Carbon::create($annee, $mois, 19)->toDateString(),
                'date_fin'     => Carbon::create($annee, $mois, 21)->toDateString(),
                'nombre_jours' => 3,
                'motif'        => 'Vacances',
                'statut'       => 'Approuvé',
            ]],
            'civp' => [[
                'type'         => 'Congé maladie',
                'date_debut'   => Carbon::create($annee, $mois, 22)->toDateString(),
                'date_fin'     => Carbon::create($annee, $mois, 22)->toDateString(),
                'nombre_jours' => 1,
                'motif'        => 'Maladie',
                'statut'       => 'Approuvé',
            ]],
            'karama' => [[
                'type'         => 'Congé annuel',
                'date_debut'   => Carbon::create($annee, $mois, 26)->toDateString(),
                'date_fin'     => Carbon::create($annee, $mois, 27)->toDateString(),
                'nombre_jours' => 2,
                'motif'        => 'Événement familial',
                'statut'       => 'Approuvé',
            ]],
            default => [],
        };

        foreach ($congesData as $c) {
            DB::table('conges')->insert([
                'employer_id'  => $employerId,
                'type'         => $c['type'],
                'date_debut'   => $c['date_debut'],
                'date_fin'     => $c['date_fin'],
                'nombre_jours' => $c['nombre_jours'],
                'motif'        => $c['motif'],
                'statut'       => $c['statut'],
                'commentaire'  => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}