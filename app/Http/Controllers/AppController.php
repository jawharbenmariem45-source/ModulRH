<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Departement;
use App\Models\Employer;
use App\Models\User;
use App\Models\Conge;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match(true) {
            $user->hasRole('admin')   => $this->dashboardAdmin(),
            $user->hasRole('rh')      => $this->dashboardRh(),
            $user->hasRole('manager') => $this->dashboardManager(),
            default => redirect()->route('login'),
        };
    }

    private function dashboardAdmin()
    {
        $totalDepartements    = Departement::count();
        $totalEmployers       = Employer::count();
        $totalAdministrateurs = User::count();
        $paymentNotification  = $this->getPaymentNotification();

        $contratsAlertes = Employer::whereNotNull('date_fin')
            ->whereDate('date_fin', '>=', Carbon::today())
            ->whereDate('date_fin', '<=', Carbon::today()->addDays(7))
            ->count();

        return view('dashboard.admin', compact(
            'totalDepartements', 'totalEmployers',
            'totalAdministrateurs', 'paymentNotification',
            'contratsAlertes'
        ));
    }

    private function dashboardRh()
    {
        $user           = Auth::user();
        $totalEmployers = Employer::count();

        $contratsAlertes = Employer::whereNotNull('date_fin')
            ->whereDate('date_fin', '>=', Carbon::today())
            ->whereDate('date_fin', '<=', Carbon::today()->addDays(30))
            ->count();

        $congesEnAttente = Conge::where('statut', 'En attente')->count();

        $monthMapping = [
            'JANUARY'   => 'JANVIER',  'FEBRUARY'  => 'FEVRIER',
            'MARCH'     => 'MARS',     'APRIL'     => 'AVRIL',
            'MAY'       => 'MAI',      'JUNE'      => 'JUIN',
            'JULY'      => 'JUILLET',  'AUGUST'    => 'AOUT',
            'SEPTEMBER' => 'SEPTEMBRE','OCTOBER'   => 'OCTOBRE',
            'NOVEMBER'  => 'NOVEMBRE', 'DECEMBER'  => 'DECEMBRE',
        ];

        $currentMonthFrench  = $monthMapping[strtoupper(Carbon::now()->format('F'))] ?? '';
        $paiementsMoisActuel = Payment::where('month', $currentMonthFrench)
            ->where('year', Carbon::now()->format('Y'))
            ->count();

        $paymentNotification = $this->getPaymentNotification($user->company_id);

        return view('dashboard.rh', compact(
            'totalEmployers', 'contratsAlertes',
            'congesEnAttente', 'paiementsMoisActuel',
            'paymentNotification'
        ));
    }

    private function dashboardManager()
    {
        $congesEnAttente = Conge::where('statut', 'En attente')->count();
        $congesApprouves = Conge::where('statut', 'Approuvé')->count();
        $congesRefuses   = Conge::where('statut', 'Refusé')->count();
        $totalEmployers  = Employer::count();

        return view('dashboard.manager', compact(
            'congesEnAttente', 'congesApprouves',
            'congesRefuses', 'totalEmployers'
        ));
    }

    private function getPaymentNotification(int $companyId = null)
    {
        // ✅ Nouvelle structure — une ligne par company
        $config = $companyId
            ? Configuration::where('company_id', $companyId)->first()
            : null;

        if (!$config) return '';

        $date        = $config->payment_date;
        $currentDate = Carbon::now()->day;

        if ($currentDate < intval($date)) {
            return 'Le paiement doit avoir lieu le ' . $date . ' de ce mois';
        }

        return 'Le paiement doit avoir lieu le ' . $date . ' du mois de ' . Carbon::now()->addMonth()->format('F');
    }
}