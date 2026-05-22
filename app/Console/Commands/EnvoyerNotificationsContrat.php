<?php

namespace App\Console\Commands;

use App\Mail\ContratExpirantEmployer;
use App\Mail\ContratExpirantRH;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnvoyerNotificationsContrat extends Command
{
    protected $signature   = 'contrats:notifier';
    protected $description = 'Envoyer des notifications pour les contrats expirant bientôt';

    public function handle()
    {
        $users = User::role('employer')
            ->whereNotNull('end_date')
            ->whereNotNull('company_id')
            ->where('contract_type', '!=', 'CDI')
            ->whereBetween('end_date', [
                Carbon::today(),
                Carbon::today()->addDays(30),
            ])
            ->get();

        foreach ($users as $user) {
            // Email à l'employé
            Mail::to($user->email)->send(new ContratExpirantEmployer($user));

            // Email au RH de la même company
            $rhUsers = User::where('company_id', $user->company_id)
                ->role('rh')
                ->get();

            foreach ($rhUsers as $rh) {
                Mail::to($rh->email)->send(new ContratExpirantRH($user));
            }
        }

        $this->info($users->count() . ' notification(s) envoyée(s).');
    }
}