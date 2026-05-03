<?php

namespace App\Console\Commands;

use App\Mail\ContratExpirantEmployer;
use App\Mail\ContratExpirantRH;
use App\Models\Employer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class EnvoyerNotificationsContrat extends Command
{
    protected $signature = 'contrats:notifier';
    protected $description = 'Envoyer des notifications pour les contrats expirant bientôt';

    public function handle()
    {
        // Employés dont le contrat expire dans 30 jours ou moins
        $employers = Employer::whereNotNull('date_fin')
            ->whereNotNull('company_id')
            ->where('type_contrat', '!=', 'CDI')
            ->whereBetween('date_fin', [
                Carbon::today(),
                Carbon::today()->addDays(30)
            ])
            ->get();

        foreach ($employers as $employer) {
            // Email à l'employé
            Mail::to($employer->email)->send(new ContratExpirantEmployer($employer));

            // Email au RH de la même company
            $rhUsers = User::where('company_id', $employer->company_id)
                ->role('rh')
                ->get();

            foreach ($rhUsers as $rh) {
                Mail::to($rh->email)->send(new ContratExpirantRH($employer));
            }
        }

        $this->info($employers->count() . ' notification(s) envoyée(s).');
    }
}