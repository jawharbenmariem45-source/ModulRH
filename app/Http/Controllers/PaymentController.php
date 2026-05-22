<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Conge;
use App\Models\Attendance;
use App\Models\Company;
use App\Console\Commands\CalculateDisciplineScores;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    // ── Constantes officielles Tunisie 2026 ───────────────────────────────────

    // CDI / CDD
    const CNSS_TAUX_SALARIE = 0.0968; // 9.18% + 0.50% assurance perte d'emploi
    const CNSS_PLAFOND      = 6000;   // plafond mensuel CNSS
    const CSS_TAUX          = 0.005;  // 0.5% contribution solidarité
    const CSS_SEUIL_AAI     = 5000;   // CSS applicable si AAI > 5000 TND/an
    const FRAIS_PRO_TAUX    = 0.20;   // abattement forfaitaire professionnel 20%
    const FRAIS_PRO_MAX     = 2000;   // plafonné à 2000 TND/an

    // Karama
    const KARAMA_SUBVENTION = 400.0;  // subvention État exonérée IRPP et CNSS

    // CIVP
    const CIVP_ANETI        = 200.0;  // bourse ANETI versée directement au stagiaire

    // Congés / Discipline
    const CONGES_PAR_MOIS   = 1;      // 1 jour de congé acquis par mois travaillé
    const DISCIPLINE_SEUIL  = 70;     // score min pour ETL favorable

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

    private function etlCorrigerPointage(Attendance $att, bool $discipline): Attendance
    {
        $date = Carbon::parse($att->date);

        if ($discipline) {
            // Score ≥ 70 : pointage incomplet complété aux heures normales
            if ($att->morning_check_in && !$att->morning_check_out)
                $att->morning_check_out = $date->copy()->setTime(12, 0)->toDateTimeString();
            if (!$att->morning_check_in && $att->morning_check_out)
                $att->morning_check_in = $date->copy()->setTime(8, 0)->toDateTimeString();
            if ($att->afternoon_check_in && !$att->afternoon_check_out)
                $att->afternoon_check_out = $date->copy()->setTime(17, 0)->toDateTimeString();
            if (!$att->afternoon_check_in && $att->afternoon_check_out)
                $att->afternoon_check_in = $date->copy()->setTime(13, 0)->toDateTimeString();
        } else {
            // Score < 70 : demi-journée incomplète = supprimée (absence)
            if ($att->morning_check_in && !$att->morning_check_out) {
                $att->morning_check_in  = null;
                $att->morning_check_out = null;
            }
            if ($att->afternoon_check_in && !$att->afternoon_check_out) {
                $att->afternoon_check_in  = null;
                $att->afternoon_check_out = null;
            }
        }
        return $att;
    }

    private function getPointage(User $user, int $mois, int $annee): array
    {
        $discipline  = ($user->discipline_score ?? 100) >= self::DISCIPLINE_SEUIL;
        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $mois)
            ->whereYear('date', $annee)
            ->get();

        $joursTravailles = 0;
        $joursAbsent     = 0;
        $minutesTravail  = 0;

        foreach ($attendances as $att) {
            if ($att->status === 'absent')   { $joursAbsent++; continue; }
            if ($att->status === 'on_leave') { continue; }

            $att         = $this->etlCorrigerPointage($att, $discipline);
            $minutesJour = 0;

            if ($att->morning_check_in && $att->morning_check_out) {
                try { $minutesJour += Carbon::parse($att->morning_check_in)
                    ->diffInMinutes(Carbon::parse($att->morning_check_out)); }
                catch (\Exception $e) {}
            }
            if ($att->afternoon_check_in && $att->afternoon_check_out) {
                try { $minutesJour += Carbon::parse($att->afternoon_check_in)
                    ->diffInMinutes(Carbon::parse($att->afternoon_check_out)); }
                catch (\Exception $e) {}
            }

            if ($minutesJour > 0) { $joursTravailles++; $minutesTravail += $minutesJour; }
            else { $joursAbsent++; }
        }

        $heuresTravaillees = $minutesTravail / 60;
        $heuresNormales    = $joursTravailles * $this->getHeuresJournee();
        $heuresSup         = max(round($heuresTravaillees - $heuresNormales, 2), 0);

        return [
            'jours_travailles' => $joursTravailles,
            'jours_sans_solde' => $joursAbsent,
            'heures_sup'       => $heuresSup,
            'regime'           => $this->getRegimeLabel($user->company_id),
            'discipline'       => $discipline,
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
            // Régime 48h/semaine : toutes les heures sup à +75%
            return round($heuresSup * $tauxHoraire * 1.75, 3);
        }

        // Régime < 48h (ex: 40h/semaine)
        // Heures normales mensuelles 40h = 176h, plafond 48h = 208h
        // Différence = 32h/mois avant de passer à +50%
        $heuresAvant48 = ($this->getJoursOuvres($companyId) * 48 / 5) - $heuresMensuelles;
        $heuresAvant48 = max($heuresAvant48, 0);

        if ($heuresSup <= $heuresAvant48) {
            // Jusqu'à atteindre 48h hebdo → +25%
            return round($heuresSup * $tauxHoraire * 1.25, 3);
        }

        // Au-delà de 48h → +50%
        $tranche1 = $heuresAvant48 * $tauxHoraire * 1.25;
        $tranche2 = ($heuresSup - $heuresAvant48) * $tauxHoraire * 1.50;
        return round($tranche1 + $tranche2, 3);
    }

    private function calculerIRPPAnnuel(float $aai): float
    {
        if ($aai <= 0)      return 0;
        if ($aai <= 5000)   return 0;
        if ($aai <= 10000)  return ($aai - 5000)  * 0.15;
        if ($aai <= 20000)  return 750  + ($aai - 10000) * 0.25;
        if ($aai <= 30000)  return 3250 + ($aai - 20000) * 0.30;
        if ($aai <= 40000)  return 6250 + ($aai - 30000) * 0.33;
        if ($aai <= 50000)  return 9550 + ($aai - 40000) * 0.36;
        if ($aai <= 70000)  return 13150 + ($aai - 50000) * 0.38;
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

    /**
     * CDI / CDD
     * ─────────
     * CNSS     = min(brut, 6000) × 9.68%
     * SNC      = brut - CNSS
     * FraisPro = min(SNC × 12 × 20%, 2000 TND/an)
     * AAI      = (SNC × 12) - FraisPro - déductions famille
     * IRPP     = barème progressif(AAI) / 12
     * CSS      = (AAI × 0.5%) / 12  si AAI > 5000
     */
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

    /**
     * Karama
     * ──────
     * CNSS      = 0 (État prend en charge salariale + patronale)
     * FraisPro  = min(brut × 12 × 20%, 2000 TND/an)
     * AAI       = (brut × 12) - FraisPro - déductions famille
     *             (subvention 400 TND exonérée → pas incluse dans AAI)
     * IRPP      = barème progressif(AAI) / 12
     * CSS       = (AAI × 0.5%) / 12  si AAI > 5000
     * Net final = (brut - IRPP - CSS) + 400 TND subvention État
     */
    private function calculerImpotssKarama(float $brutEmployeur, User $user): array
    {
        $fraisPro = min($brutEmployeur * 12 * self::FRAIS_PRO_TAUX, self::FRAIS_PRO_MAX);
        $aai      = max(($brutEmployeur * 12) - $fraisPro - $this->deductionsFamille($user), 0);
        $irpp     = round($this->calculerIRPPAnnuel($aai) / 12, 3);
        $css      = $aai > self::CSS_SEUIL_AAI
                        ? round(($aai * self::CSS_TAUX) / 12, 3) : 0;

        return ['cnss' => 0, 'irpp' => $irpp, 'css' => $css];
    }

    /**
     * Calcul principal par type de contrat
     */
    private function calculerSalaireParContrat(
        User $user,
        int $mois,
        int $annee,
        float $primes     = 0,
        float $indemnites = 0
    ): array {
        $salaireBase  = floatval($user->salary ?? 0);
        $companyId    = $user->company_id;
        $joursOuvres  = $this->getJoursOuvres($companyId);

        $pointage        = $this->getPointage($user, $mois, $annee);
        $joursTravailles = $pointage['jours_travailles'];
        $joursSansSolde  = $pointage['jours_sans_solde'];
        $heuresSup       = $pointage['heures_sup'];

        $conges     = $this->getConges($user, $mois, $annee);
        $joursConge = $conges['jours_conge'];
        $joursPayes = $joursTravailles + $joursConge;

        // Salaire proratisé selon jours payés / jours ouvrés du mois
        $salaireProratise = round($salaireBase * ($joursPayes / max($joursOuvres, 1)), 3);
        $montantHS        = $this->calculerHeuresSup($salaireBase, $heuresSup, $companyId);
        $retenueSansSolde = round($joursSansSolde * ($salaireBase / max($joursOuvres, 1)), 3);

        $salaireBrut = $cnss = $irpp = $css = $salaireNet = 0;
        $civpAneti   = 0; // part ANETI pour CIVP (informative)
        $karamaSubv  = 0; // subvention État pour Karama (informative)

        switch ($user->contract_type) {

            // ── CDI / CDD ────────────────────────────────────────────────────
            case 'CDI':
            case 'CDD':
                $salaireBrut = $salaireProratise + $montantHS + $primes + $indemnites;
                $impots      = $this->calculerImpotsCDI($salaireBrut, $user);
                $cnss        = $impots['cnss'];
                $irpp        = $impots['irpp'];
                $css         = $impots['css'];
                $salaireNet  = $salaireBrut - $cnss - $irpp - $css - $retenueSansSolde;
                break;

            // ── CIVP ─────────────────────────────────────────────────────────
            // Exonération totale : CNSS = IRPP = CSS = 0
            // Brut entreprise = Net entreprise
            // + 200 TND ANETI versés directement au stagiaire (non déduits ici)
            case 'CIVP':
                $salaireBrut = $salaireProratise + $montantHS + $primes + $indemnites;
                $cnss        = 0;
                $irpp        = 0;
                $css         = 0;
                $salaireNet  = $salaireBrut - $retenueSansSolde;
                $civpAneti   = self::CIVP_ANETI; // mention informative sur fiche
                break;

            // ── Karama ───────────────────────────────────────────────────────
            // CNSS = 0 (État prend en charge)
            // IRPP calculé sur brut employeur uniquement (subvention exonérée)
            // Net final = (brut - IRPP - CSS) + 400 TND subvention État
            case 'Karama':
                $salaireBrut = max($salaireProratise + $montantHS + $primes + $indemnites, 200.0);
                $impots      = $this->calculerImpotssKarama($salaireBrut, $user);
                $cnss        = 0;
                $irpp        = $impots['irpp'];
                $css         = $impots['css'];
                $salaireNet  = ($salaireBrut - $irpp - $css - $retenueSansSolde) + self::KARAMA_SUBVENTION;
                $karamaSubv  = self::KARAMA_SUBVENTION; // mention informative sur fiche
                break;

            // ── Défaut (traité comme CDI) ─────────────────────────────────
            default:
                $salaireBrut = $salaireProratise + $montantHS + $primes + $indemnites;
                $impots      = $this->calculerImpotsCDI($salaireBrut, $user);
                $cnss        = $impots['cnss'];
                $irpp        = $impots['irpp'];
                $css         = $impots['css'];
                $salaireNet  = $salaireBrut - $cnss - $irpp - $css - $retenueSansSolde;
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
            'civp_aneti'         => $civpAneti,   // 200 TND ANETI (CIVP)
            'karama_subvention'  => $karamaSubv,  // 400 TND État (Karama)
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
            $isPaymentDay = $company ? intval(date('d')) == intval($company->payment_date) : false;
            $query->whereHas('user', fn($q) => $q->where('company_id', $user->company_id));
        }

        if ($request->filled('month'))    $query->where('month', $request->month);
        if ($request->filled('year'))     $query->where('year', $request->year);
        if ($request->filled('employer')) {
            $s = $request->employer;
            $query->whereHas('user', fn($q) =>
                $q->where('last_name', 'like', "%$s%")
                  ->orWhere('first_name', 'like', "%$s%")
            );
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

        $monthMapping = [
            'JANUARY'   => 'JANVIER',  'FEBRUARY'  => 'FEVRIER',
            'MARCH'     => 'MARS',     'APRIL'     => 'AVRIL',
            'MAY'       => 'MAI',      'JUNE'      => 'JUIN',
            'JULY'      => 'JUILLET',  'AUGUST'    => 'AOUT',
            'SEPTEMBER' => 'SEPTEMBRE','OCTOBER'   => 'OCTOBRE',
            'NOVEMBER'  => 'NOVEMBRE', 'DECEMBER'  => 'DECEMBRE',
        ];

        $now                = Carbon::now();
        $currentMonthFrench = $monthMapping[strtoupper($now->format('F'))]
                              ?? strtoupper($now->format('F'));
        $currentYear        = $now->format('Y');
        $moisInt            = (int) $now->format('m');
        $anneeInt           = (int) $currentYear;

        // Employés de la company pas encore payés ce mois
        $users = User::role('employer')
            ->where('company_id', $authUser->company_id)
            ->whereNotNull('salary')
            ->whereDoesntHave('payments', fn($q) =>
                $q->where('month', $currentMonthFrench)->where('year', $currentYear)
            )->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error_message',
                'Tous les employés ont déjà été payés pour '
                . $currentMonthFrench . ' ' . $currentYear . '.'
            );
        }

        // Recalcul du score discipline avant le paiement
        foreach ($users as $user) {
            $user->update([
                'discipline_score' => CalculateDisciplineScores::calculerScore($user),
            ]);
        }

        $count = 0;
        foreach ($users as $user) {
            try {
                $calcul = $this->calculerSalaireParContrat($user, $moisInt, $anneeInt);

                Payment::create([
                    'reference'       => strtoupper(Str::random(10)),
                    'user_id'         => $user->id,
                    'month'           => $currentMonthFrench,
                    'year'            => $currentYear,
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
                    'launch_date'     => now(),
                    'done_time'       => now(),
                ]);

                $count++;
            } catch (Exception $e) {
                Log::error("Erreur paie user#{$user->id} : " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success_message',
            $count . ' fiche(s) de paie générée(s) pour '
            . $currentMonthFrench . ' ' . $currentYear . '.'
        );
    }

    // ── PDF ───────────────────────────────────────────────────────────────────

    private function moisEnInt(string $mois): int
    {
        $map = [
            'JANVIER'   => 1,  'FEVRIER'  => 2,  'MARS'      => 3,
            'AVRIL'     => 4,  'MAI'      => 5,  'JUIN'      => 6,
            'JUILLET'   => 7,  'AOUT'     => 8,  'SEPTEMBRE' => 9,
            'OCTOBRE'   => 10, 'NOVEMBRE' => 11, 'DECEMBRE'  => 12,
        ];
        return $map[strtoupper($mois)] ?? 1;
    }

    public function download_invoice(Payment $payment)
    {
        try {
            $fullPaymentInfo = Payment::with('employer')->findOrFail($payment->id);
            $moisInt = $this->moisEnInt($payment->month);
            $debut   = Carbon::create($payment->year, $moisInt, 1)->startOfMonth();
            $fin     = Carbon::create($payment->year, $moisInt, 1)->endOfMonth();

            $conges = Conge::where('user_id', $payment->user_id)
                ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
                ->where(fn($q) => $q
                    ->whereBetween('start_date', [$debut, $fin])
                    ->orWhereBetween('end_date', [$debut, $fin])
                )->get();

            $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
            return $pdf->download(
                'fiche-paie-' . $fullPaymentInfo->employer->last_name
                . '-' . $payment->month . '-' . $payment->year . '.pdf'
            );
        } catch (Exception $e) {
            return redirect()->back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function preview_invoice(Payment $payment)
    {
        try {
            $fullPaymentInfo = Payment::with('employer')->findOrFail($payment->id);
            $moisInt = $this->moisEnInt($payment->month);
            $debut   = Carbon::create($payment->year, $moisInt, 1)->startOfMonth();
            $fin     = Carbon::create($payment->year, $moisInt, 1)->endOfMonth();

            $conges = Conge::where('user_id', $payment->user_id)
                ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
                ->where(fn($q) => $q
                    ->whereBetween('start_date', [$debut, $fin])
                    ->orWhereBetween('end_date', [$debut, $fin])
                )->get();

            $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
            return $pdf->stream(
                'fiche-paie-' . $fullPaymentInfo->employer->last_name
                . '-' . $payment->month . '-' . $payment->year . '.pdf'
            );
        } catch (Exception $e) {
            return redirect()->back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }
}