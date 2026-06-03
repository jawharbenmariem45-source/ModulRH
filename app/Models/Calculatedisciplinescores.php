<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateDisciplineScores extends Command
{
    protected $signature   = 'discipline:calculate';
    protected $description = 'Recalculer les scores de discipline de tous les employés sur les 6 derniers mois';

    public function handle(): void
    {
        $this->info('Calcul des scores de discipline...');

        $users = User::role('employer')->get();
        $bar   = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $score = self::calculerScore($user);
            $user->update(['discipline_score' => $score]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Scores recalculés pour ' . $users->count() . ' employés.');
    }

    public static function calculerScore(User $user): int
    {
        $debut = Carbon::now()->subMonths(6)->startOfDay();
        $fin   = Carbon::now();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$debut->format('Y-m-d'), $fin->format('Y-m-d')])
            ->get();

        $nbTravailles = $attendances->whereIn('status', ['present', 'late'])->count();
        $nbRetards    = $attendances->where('status', 'late')->count();
        $nbAbsences   = $attendances->where('status', 'absent')->count();

        $joursConge = Leave::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'Approuvé'])
            ->whereBetween('start_date', [$debut->format('Y-m-d'), $fin->format('Y-m-d')])
            ->sum('days_count');

        if ($nbTravailles === 0) return 100;

        $tauxRetard  = ($nbRetards / $nbTravailles) * 100;
        $tauxAbsence = ($nbAbsences / $nbTravailles) * 100;
        $congeExcess = max((int) $joursConge - 6, 0);

        $score = 100
            - ($tauxRetard  * 0.5)
            - ($tauxAbsence * 1.0)
            - ($congeExcess * 2);

        return max(0, min(100, (int) round($score)));
    }
}
