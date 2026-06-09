<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Conge;
use App\Models\Attendance;
use App\Models\Company;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    // ── Constantes officielles Tunisie 2026 ───────────────────────────────────

    const CNSS_TAUX_SALARIE = 0.0968;
    const CNSS_PLAFOND      = 6000;
    const CSS_TAUX          = 0.005;
    const CSS_SEUIL_AAI     = 5000;
    const FRAIS_PRO_TAUX    = 0.20;
    const FRAIS_PRO_MAX     = 2000;
    const KARAMA_SUBVENTION = 400.0;
    const CIVP_ANETI        = 200.0;
    const CONGES_PAR_MOIS   = 1;

    // ── Mapping mois ──────────────────────────────────────────────────────────

    private const MONTH_MAP = [
        'JANUARY'   => 1,  'FEBRUARY'  => 2,  'MARCH'     => 3,
        'APRIL'     => 4,  'MAY'       => 5,  'JUNE'      => 6,
        'JULY'      => 7,  'AUGUST'    => 8,  'SEPTEMBER' => 9,
        'OCTOBER'   => 10, 'NOVEMBER'  => 11, 'DECEMBER'  => 12,
    ];

    private const MOIS_NOMS = [
        1  => 'JANVIER',  2  => 'FEVRIER',  3  => 'MARS',
        4  => 'AVRIL',    5  => 'MAI',      6  => 'JUIN',
        7  => 'JUILLET',  8  => 'AOUT',     9  => 'SEPTEMBRE',
        10 => 'OCTOBRE',  11 => 'NOVEMBRE', 12 => 'DECEMBRE',
    ];

    // ── Helpers entreprise ────────────────────────────────────────────────────

    private function getJoursOuvres(int $companyId): int
    {
        $company = Company::find($companyId);
        return ($company?->work_schedule ?? '40h') === '48h' ? 26 : 22;
    }

    private function getHeuresJournee(): float { return 8.0; }

    private function getHeuresMensuelles(int $companyId): float
    {
        return $this->getJoursOuvres($companyId) * $this->getHeuresJournee();
    }

    private function getRegimeLabel(int $companyId): string
    {
        return Company::find($companyId)?->work_schedule ?? '40h';
    }

    // ── ETL Pointage ──────────────────────────────────────────────────────────

    private function getPointage(User $user, int $mois, int $annee): array
    {
        $pointages = Attendance::where('user_id', $user->id)
            ->where('status', 'actived')
            ->whereMonth('pointage_at', $mois)
            ->whereYear('pointage_at', $annee)
            ->orderBy('pointage_at')
            ->get();

        $parJour = $pointages->groupBy(function ($p) {
            return Carbon::parse($p->pointage_at)->format('Y-m-d');
        });

        $joursTravailles = 0;
        $minutesTravail  = 0;

        foreach ($parJour as $jour => $pointagesJour) {
            $entrees = $pointagesJour->where('type', 'entree')->sortBy('pointage_at')->values();
            $sorties = $pointagesJour->where('type', 'sortie')->sortBy('pointage_at')->values();

            if ($entrees->isEmpty() || $sorties->isEmpty()) continue;

            $minutesJour = 0;
            $nbPaires    = min($entrees->count(), $sorties->count());

            for ($i = 0; $i < $nbPaires; $i++) {
                try {
                    $entree = Carbon::parse($entrees[$i]->pointage_at);
                    $sortie = Carbon::parse($sorties[$i]->pointage_at);
                    if ($sortie->gt($entree)) {
                        $minutesJour += $entree->diffInMinutes($sortie);
                    }
                } catch (\Exception $e) {}
            }

            if ($minutesJour > 0) {
                $joursTravailles++;
                $minutesTravail += $minutesJour;
            }
        }

        $heuresTravaillees = $minutesTravail / 60;
        $heuresNormales    = $joursTravailles * $this->getHeuresJournee();
        $heuresSup         = max(round($heuresTravaillees - $heuresNormales, 2), 0);

        return [
            'jours_travailles' => $joursTravailles,
            'heures_sup'       => $heuresSup,
            'regime'           => $this->getRegimeLabel($user->company_id),
            'discipline_score' => $user->discipline_score ?? 100,
        ];
    }

    // ── Congés ────────────────────────────────────────────────────────────────

    private function getConges(User $user, int $mois, int $annee): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        $congesApprouves = Conge::where('user_id', $user->id)
            ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('start_date', [$debut, $fin])
                  ->orWhereBetween('end_date', [$debut, $fin])
                  ->orWhere(fn($q2) => $q2->where('start_date', '<=', $debut)
                      ->where('end_date', '>=', $fin));
            })->get();

        $joursConge = 0;
        foreach ($congesApprouves as $c) {
            try {
                $d = Carbon::parse($c->start_date)->max($debut);
                $f = Carbon::parse($c->end_date)->min($fin);
                if ($f->gte($d))
                    $joursConge += $c->days_count ?? ($d->diffInWeekdays($f) + 1);
            } catch (\Exception $e) {}
        }

        try {
            $moisAnciennete = Carbon::parse($user->start_date ?? $user->created_at)
                ->diffInMonths(Carbon::create($annee, $mois, 1));
        } catch (\Exception $e) { $moisAnciennete = 0; }

        $soldeAcquis  = $moisAnciennete * self::CONGES_PAR_MOIS;
        $totalPris    = Conge::where('user_id', $user->id)
            ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
            ->where('end_date', '<', $debut)->sum('days_count');
        $soldeRestant = max($soldeAcquis - $totalPris - $joursConge, 0);

        return [
            'jours_conge'    => $joursConge,
            'solde_acquis'   => round($soldeAcquis, 1),
            'solde_pris'     => round($totalPris + $joursConge, 1),
            'solde_restant'  => round($soldeRestant, 1),
            'provision_mois' => self::CONGES_PAR_MOIS,
        ];
    }

    // ── Calculs ───────────────────────────────────────────────────────────────

    private function calculerHeuresSup(float $salaireBase, float $heuresSup, int $companyId): float
    {
        if ($heuresSup <= 0) return 0;

        $heuresMensuelles = $this->getHeuresMensuelles($companyId);
        $tauxHoraire      = $salaireBase / $heuresMensuelles;
        $regime           = $this->getRegimeLabel($companyId);

        if ($regime === '48h') {
            return round($heuresSup * $tauxHoraire * 1.75, 3);
        }

        $heuresAvant48 = max(($this->getJoursOuvres($companyId) * 48 / 5) - $heuresMensuelles, 0);

        if ($heuresSup <= $heuresAvant48) {
            return round($heuresSup * $tauxHoraire * 1.25, 3);
        }

        $tranche1 = $heuresAvant48 * $tauxHoraire * 1.25;
        $tranche2 = ($heuresSup - $heuresAvant48) * $tauxHoraire * 1.50;
        return round($tranche1 + $tranche2, 3);
    }

    private function calculerIRPPAnnuel(float $aai): float
    {
        if ($aai <= 0)     return 0;
        if ($aai <= 5000)  return 0;
        if ($aai <= 10000) return ($aai - 5000)  * 0.15;
        if ($aai <= 20000) return 750  + ($aai - 10000) * 0.25;
        if ($aai <= 30000) return 3250 + ($aai - 20000) * 0.30;
        if ($aai <= 40000) return 6250 + ($aai - 30000) * 0.33;
        if ($aai <= 50000) return 9550 + ($aai - 40000) * 0.36;
        if ($aai <= 70000) return 13150 + ($aai - 50000) * 0.38;
        return 20750 + ($aai - 70000) * 0.40;
    }

    private function deductionsFamille(User $user): float
    {
        $ded    = $user->family_head ? 300.0 : 0.0;
        $bareme = [90, 75, 60, 45];
        for ($i = 0; $i < min($user->children_count ?? 0, 4); $i++)
            $ded += $bareme[$i];
        $ded += ($user->disabled_children_count ?? 0) * 2000;
        return $ded;
    }

    private function calculerImpotsCDI(float $salaireBrut, User $user): array
    {
        $cnss       = round(min($salaireBrut, self::CNSS_PLAFOND) * self::CNSS_TAUX_SALARIE, 3);
        $sncMensuel = $salaireBrut - $cnss;
        $fraisPro   = min($sncMensuel * 12 * self::FRAIS_PRO_TAUX, self::FRAIS_PRO_MAX);
        $aai        = max(($sncMensuel * 12) - $fraisPro - $this->deductionsFamille($user), 0);
        $irpp       = round($this->calculerIRPPAnnuel($aai) / 12, 3);
        $css        = $aai > self::CSS_SEUIL_AAI
                        ? round(($aai * self::CSS_TAUX) / 12, 3) : 0;

        return ['cnss' => $cnss, 'irpp' => $irpp, 'css' => $css];
    }

    private function calculerImpotssKarama(float $brutEmployeur, User $user): array
    {
        $fraisPro = min($brutEmployeur * 12 * self::FRAIS_PRO_TAUX, self::FRAIS_PRO_MAX);
        $aai      = max(($brutEmployeur * 12) - $fraisPro - $this->deductionsFamille($user), 0);
        $irpp     = round($this->calculerIRPPAnnuel($aai) / 12, 3);
        $css      = $aai > self::CSS_SEUIL_AAI
                        ? round(($aai * self::CSS_TAUX) / 12, 3) : 0;

        return ['cnss' => 0, 'irpp' => $irpp, 'css' => $css];
    }

    private function calculerSalaireParContrat(
        User $user,
        int $mois,
        int $annee,
        float $primes     = 0,
        float $indemnites = 0
    ): array {
        $salaireBase = floatval($user->salary ?? 0);
        $companyId   = $user->company_id;
        $joursOuvres = $this->getJoursOuvres($companyId);

        $pointage        = $this->getPointage($user, $mois, $annee);
        $joursTravailles = $pointage['jours_travailles'];
        $heuresSup       = $pointage['heures_sup'];

        $conges     = $this->getConges($user, $mois, $annee);
        $joursConge = $conges['jours_conge'];
        $joursPayes = $joursTravailles + $joursConge;

        // Aucun pointage ET aucun congé → salaire plein (données manquantes)
        $aucunPointage = ($joursTravailles === 0 && $joursConge === 0);

        $joursSansSolde   = $aucunPointage ? 0 : max($joursOuvres - $joursPayes, 0);
        $salaireProratise = $aucunPointage
            ? round($salaireBase, 3)
            : round($salaireBase * ($joursPayes / max($joursOuvres, 1)), 3);

        $montantHS = $this->calculerHeuresSup($salaireBase, $heuresSup, $companyId);

        // retenueSansSolde conservée pour affichage sur fiche mais NON déduite du net
        // (le prorata couvre déjà les jours non travaillés)
        $retenueSansSolde = round($joursSansSolde * ($salaireBase / max($joursOuvres, 1)), 3);

        $salaireBrut = $cnss = $irpp = $css = $salaireNet = 0;
        $civpAneti   = 0;
        $karamaSubv  = 0;

        switch ($user->contract_type) {

            case 'CDI':
            case 'CDD':
                $salaireBrut = $salaireProratise + $montantHS + $primes + $indemnites;
                $impots      = $this->calculerImpotsCDI($salaireBrut, $user);
                $cnss        = $impots['cnss'];
                $irpp        = $impots['irpp'];
                $css         = $impots['css'];
                $salaireNet  = $salaireBrut - $cnss - $irpp - $css;
                break;

            case 'CIVP':
                $salaireBrut = $salaireProratise + $montantHS + $primes + $indemnites;
                $cnss        = 0;
                $irpp        = 0;
                $css         = 0;
                $salaireNet  = $salaireBrut;
                $civpAneti   = self::CIVP_ANETI;
                break;

            case 'Karama':
                $salaireBrut = max($salaireProratise + $montantHS + $primes + $indemnites, 200.0);
                $impots      = $this->calculerImpotssKarama($salaireBrut, $user);
                $cnss        = 0;
                $irpp        = $impots['irpp'];
                $css         = $impots['css'];
                $salaireNet  = ($salaireBrut - $irpp - $css) + self::KARAMA_SUBVENTION;
                $karamaSubv  = self::KARAMA_SUBVENTION;
                break;

            default:
                $salaireBrut = $salaireProratise + $montantHS + $primes + $indemnites;
                $impots      = $this->calculerImpotsCDI($salaireBrut, $user);
                $cnss        = $impots['cnss'];
                $irpp        = $impots['irpp'];
                $css         = $impots['css'];
                $salaireNet  = $salaireBrut - $cnss - $irpp - $css;
        }

        return [
            'contract_type'      => $user->contract_type,
            'work_schedule'      => $this->getRegimeLabel($companyId),
            'jours_ouvres'       => $joursOuvres,
            'jours_travailles'   => $joursTravailles,
            'jours_conge'        => $joursConge,
            'jours_sans_solde'   => $joursSansSolde,
            'jours_payes'        => $joursPayes,
            'base_salary'        => round($salaireBase, 3),
            'salaire_proratise'  => round($salaireProratise, 3),
            'overtime_hours'     => $heuresSup,
            'overtime_amount'    => round($montantHS, 3),
            'bonuses'            => round($primes, 3),
            'allowances'         => round($indemnites, 3),
            'retenue_sans_solde' => round($retenueSansSolde, 3),
            'gross_salary'       => round($salaireBrut, 3),
            'cnss'               => round($cnss, 3),
            'irpp'               => round($irpp, 3),
            'css'                => round($css, 3),
            'salaire_net'        => round($salaireNet, 3),
            'civp_aneti'         => $civpAneti,
            'karama_subvention'  => $karamaSubv,
            'discipline_score'   => $user->discipline_score ?? 100,
            'conges'             => $conges,
        ];
    }

    // ── Index paiements ───────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user         = auth()->user();
        $isPaymentDay = false;
        $query        = Payment::with('employer');

        if ($user->hasRole('rh')) {
            $company      = Company::find($user->company_id);
            $isPaymentDay = $company
                ? intval(date('d')) >= intval($company->payment_date)
                : false;
            $query->whereHas('user', fn($q) => $q->where('company_id', $user->company_id));
        }

        if ($request->filled('month')) {
            $query->where('month', (int) $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', (int) $request->year);
        }

        if ($request->filled('employer')) {
            $search = trim($request->employer);
            $mots   = array_filter(explode(' ', $search));

            $query->whereHas('user', function ($q) use ($search, $mots) {
                $q->where('email', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('first_name', 'like', "%$search%");

                if (count($mots) >= 2) {
                    $q->orWhere(function ($sub) use ($mots) {
                        foreach ($mots as $mot) {
                            $sub->where(function ($inner) use ($mot) {
                                $inner->where('last_name', 'like', "%$mot%")
                                      ->orWhere('first_name', 'like', "%$mot%");
                            });
                        }
                    });
                }
            });
        }

        $payments = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        return view('paiements.index', compact('payments', 'isPaymentDay'));
    }

    // ── Lancer le paiement mensuel ────────────────────────────────────────────

    public function initPayment()
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('rh') && !$authUser->hasRole('admin')) {
            return redirect()->back()->with('error_message', 'Accès refusé.');
        }

        $now      = Carbon::now();
        $moisInt  = (int) $now->format('m');
        $anneeInt = (int) $now->format('Y');
        $moisNom  = self::MOIS_NOMS[$moisInt];

        $users = User::role('employer')
            ->where('company_id', $authUser->company_id)
            ->whereNotNull('salary')
            ->whereDoesntHave('payments', function ($q) use ($moisInt, $anneeInt) {
                $q->where('month', $moisInt)
                  ->where('year', $anneeInt);
            })->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error_message',
                'Tous les employés ont déjà été payés pour '
                . $moisNom . ' ' . $anneeInt . '.'
            );
        }

        $count  = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                $calcul = $this->calculerSalaireParContrat($user, $moisInt, $anneeInt);

                Payment::create([
                    'reference'       => strtoupper(Str::random(10)),
                    'user_id'         => $user->id,
                    'month'           => $moisInt,
                    'year'            => $anneeInt,
                    'contract_type'   => $calcul['contract_type'],
                    'base_salary'     => $calcul['base_salary'],
                    'overtime_hours'  => $calcul['overtime_hours'],
                    'overtime_amount' => $calcul['overtime_amount'],
                    'bonuses'         => $calcul['bonuses'],
                    'allowances'      => $calcul['allowances'],
                    'gross_salary'    => $calcul['gross_salary'],
                    'cnss'            => $calcul['cnss'],
                    'irpp'            => $calcul['irpp'],
                    'css'             => $calcul['css'],
                    'amount'          => $calcul['salaire_net'],
                    'launch_date'     => now()->toDateString(),
                    'done_time'       => now(),
                ]);

                $count++;

            } catch (Exception $e) {
                $errors[] = "user#{$user->id} ({$user->last_name}) : " . $e->getMessage();
                Log::error("Erreur paie user#{$user->id} : " . $e->getMessage());
            }
        }

        if (!empty($errors)) {
            return redirect()->back()->with('error_message',
                $count . ' fiche(s) générée(s). Erreurs sur '
                . count($errors) . ' employé(s) : '
                . implode(' | ', array_slice($errors, 0, 3))
            );
        }

        return redirect()->back()->with('success_message',
            $count . ' fiche(s) de paie générée(s) pour '
            . $moisNom . ' ' . $anneeInt . '.'
        );
    }

    // ── PDF ───────────────────────────────────────────────────────────────────

    public function download_invoice(Payment $payment)
    {
        try {
            $fullPaymentInfo = Payment::with('employer')->findOrFail($payment->id);
            $moisInt = (int) $payment->month;
            $debut   = Carbon::create($payment->year, $moisInt, 1)->startOfMonth();
            $fin     = Carbon::create($payment->year, $moisInt, 1)->endOfMonth();

            $conges = Conge::where('user_id', $payment->user_id)
                ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
                ->where(fn($q) => $q
                    ->whereBetween('start_date', [$debut, $fin])
                    ->orWhereBetween('end_date', [$debut, $fin])
                )->get();

            $moisNom = self::MOIS_NOMS[$moisInt] ?? $moisInt;
            $pdf     = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges', 'moisNom'));

            return $pdf->download(
                'fiche-paie-' . $fullPaymentInfo->employer->last_name
                . '-' . $moisNom . '-' . $payment->year . '.pdf'
            );
        } catch (Exception $e) {
            return redirect()->back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function preview_invoice(Payment $payment)
    {
        try {
            $fullPaymentInfo = Payment::with('employer')->findOrFail($payment->id);
            $moisInt = (int) $payment->month;
            $debut   = Carbon::create($payment->year, $moisInt, 1)->startOfMonth();
            $fin     = Carbon::create($payment->year, $moisInt, 1)->endOfMonth();

            $conges = Conge::where('user_id', $payment->user_id)
                ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
                ->where(fn($q) => $q
                    ->whereBetween('start_date', [$debut, $fin])
                    ->orWhereBetween('end_date', [$debut, $fin])
                )->get();

            $moisNom = self::MOIS_NOMS[$moisInt] ?? $moisInt;
            $pdf     = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges', 'moisNom'));

            return $pdf->stream(
                'fiche-paie-' . $fullPaymentInfo->employer->last_name
                . '-' . $moisNom . '-' . $payment->year . '.pdf'
            );
        } catch (Exception $e) {
            return redirect()->back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }
}