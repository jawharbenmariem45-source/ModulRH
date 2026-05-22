<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Departement;
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
            $user->hasRole('admin')    => $this->dashboardAdmin(),
            $user->hasRole('rh')       => $this->dashboardRh(),
            $user->hasRole('manager')  => $this->dashboardManager(),
            $user->hasRole('employer') => $this->dashboardEmployer(),
            default                    => redirect()->route('login'),
        };
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    private function dashboardAdmin()
    {
        $totalDepartements    = Departement::count();
        $totalEmployers       = User::role('employer')->count();
        $totalAdministrateurs = User::count();
        $paymentNotification  = '';

        $contratsAlertes = User::role('employer')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereDate('end_date', '<=', Carbon::today()->addDays(7))
            ->count();

        return view('dashboard.admin', compact(
            'totalDepartements', 'totalEmployers',
            'totalAdministrateurs', 'paymentNotification',
            'contratsAlertes'
        ));
    }

    // ── RH ───────────────────────────────────────────────────────────────────

    private function dashboardRh()
    {
        $user      = Auth::user();
        $companyId = $user->company_id;

        $totalEmployers = User::role('employer')
            ->where('company_id', $companyId)
            ->count();

        $contratsAlertes = User::role('employer')
            ->where('company_id', $companyId)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereDate('end_date', '<=', Carbon::today()->addDays(30))
            ->count();

        $congesEnAttente = Conge::whereHas('user', fn($q) =>
            $q->where('company_id', $companyId)
        )->where('status', 'En attente')->count();

        $monthMapping = [
            'JANUARY'   => 'JANVIER',  'FEBRUARY'  => 'FEVRIER',
            'MARCH'     => 'MARS',     'APRIL'     => 'AVRIL',
            'MAY'       => 'MAI',      'JUNE'      => 'JUIN',
            'JULY'      => 'JUILLET',  'AUGUST'    => 'AOUT',
            'SEPTEMBER' => 'SEPTEMBRE','OCTOBER'   => 'OCTOBRE',
            'NOVEMBER'  => 'NOVEMBRE', 'DECEMBER'  => 'DECEMBRE',
        ];

        $currentMonthFrench  = $monthMapping[strtoupper(Carbon::now()->format('F'))] ?? '';
        $paiementsMoisActuel = Payment::whereHas('user', fn($q) =>
            $q->where('company_id', $companyId)
        )->where('month', $currentMonthFrench)
         ->where('year', Carbon::now()->format('Y'))
         ->count();

        $paymentNotification = $this->getPaymentNotification($companyId);

        return view('dashboard.rh', compact(
            'totalEmployers', 'contratsAlertes',
            'congesEnAttente', 'paiementsMoisActuel',
            'paymentNotification'
        ));
    }

    // ── Manager ───────────────────────────────────────────────────────────────

    private function dashboardManager()
    {
        $user      = Auth::user();
        $companyId = $user->company_id;

        $totalEmployers = User::role('employer')
            ->where('company_id', $companyId)
            ->count();

        $congesEnAttente = Conge::whereHas('user', fn($q) =>
            $q->where('company_id', $companyId)
        )->where('status', 'En attente')->count();

        $congesApprouves = Conge::whereHas('user', fn($q) =>
            $q->where('company_id', $companyId)
        )->whereIn('status', ['Approuvé', 'approuvé'])->count();

        $congesRefuses = Conge::whereHas('user', fn($q) =>
            $q->where('company_id', $companyId)
        )->whereIn('status', ['Refusé', 'refusé'])->count();

        return view('dashboard.manager', compact(
            'congesEnAttente', 'congesApprouves',
            'congesRefuses', 'totalEmployers'
        ));
    }

    // ── Employer ──────────────────────────────────────────────────────────────

    private function dashboardEmployer()
    {
        $user = Auth::user();

        if ($user->contract_type === 'CDI') {
            $contrat = true;
        } elseif ($user->contract_type && $user->end_date) {
            try {
                $contrat = Carbon::parse($user->end_date)->isFuture();
            } catch (\Exception $e) {
                $contrat = true;
            }
        } else {
            $contrat = $user->contract_type ? true : false;
        }

        $congesEnAttente  = $user->conges()->whereIn('status', ['En attente', 'en attente', 'en_attente'])->count();
        $congesApprouves  = $user->conges()->whereIn('status', ['Approuvé', 'approuvé', 'accepte', 'APPROUVE'])->count();
        $totalPaiements   = $user->payments()->count();
        $dernierConges    = $user->conges()->latest()->take(5)->get();
        $dernierPaiements = $user->payments()->latest()->take(5)->get();

        return view('dashboard.employer', compact(
            'contrat', 'congesEnAttente', 'congesApprouves',
            'totalPaiements', 'dernierConges', 'dernierPaiements'
        ));
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function getPaymentNotification(int $companyId = null): string
    {
        $company = $companyId ? Company::find($companyId) : null;

        if (!$company || !$company->payment_date) return '';

        $date        = $company->payment_date;
        $currentDate = Carbon::now()->day;

        if ($currentDate < intval($date)) {
            return 'Le paiement doit avoir lieu le ' . $date . ' de ce mois';
        }

        return 'Le paiement doit avoir lieu le ' . $date . ' du mois de ' . Carbon::now()->addMonth()->format('F');
    }
}