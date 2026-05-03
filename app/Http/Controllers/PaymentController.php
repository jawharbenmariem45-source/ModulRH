<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Payment;
use App\Models\Conge;
use App\Models\Attendance;
use App\Models\Configuration;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    // =========================================================
    // CONSTANTES FISCALES 2025 (Tunisie)
    // =========================================================
    const CNSS_TAUX_PLAFO = 0.0918;
    const CNSS_PLAFOND    = 6000;
    const CNSS_MALADIE    = 0.01;
    const CSS_TAUX        = 0.005;
    const JOURS_OUVRES    = 26;
    const CONGES_PAR_MOIS = 1;

    // =========================================================
    // RÉGIME HORAIRE
    // =========================================================
    private function getHeuresJournee(int $companyId): float
    {
        $regime = Configuration::where('company_id', $companyId)
            ->where('type', 'REGIME_HORAIRE')
            ->value('value') ?? '40h';

        return $regime === '48h' ? 9.6 : 8.0;
    }

    private function getHeuresMensuelles(int $companyId): float
    {
        return self::JOURS_OUVRES * $this->getHeuresJournee($companyId);
    }

    private function getRegimeLabel(int $companyId): string
    {
        return Configuration::where('company_id', $companyId)
            ->where('type', 'REGIME_HORAIRE')
            ->value('value') ?? '40h';
    }

    // =========================================================
    // POINTAGE
    // =========================================================
    private function getPointage(Employer $employer, int $mois, int $annee): array
    {
        $heuresJournee = $this->getHeuresJournee($employer->company_id);

        $attendances = Attendance::where('employer_id', $employer->id)
            ->whereMonth('date', $mois)
            ->whereYear('date', $annee)
            ->get();

        $joursTravailles = 0;
        $joursAbsent     = 0;
        $minutesTravail  = 0;

        foreach ($attendances as $att) {

            if ($att->status === 'absent') {
                $joursAbsent++;
                continue;
            }

            if ($att->status === 'on_leave') {
                continue;
            }

            $minutesJour = 0;

            if ($att->check_in_morning_time && $att->check_out_morning_time) {
                $minutesJour += Carbon::parse($att->check_in_morning_time)
                    ->diffInMinutes(Carbon::parse($att->check_out_morning_time));
            }

            if ($att->check_in_afternoon_time && $att->check_out_afternoon_time) {
                $minutesJour += Carbon::parse($att->check_in_afternoon_time)
                    ->diffInMinutes(Carbon::parse($att->check_out_afternoon_time));
            }

            if ($minutesJour > 0) {
                $joursTravailles++;
                $minutesTravail += $minutesJour;
            }
        }

        $heuresTravaillees = $minutesTravail / 60;
        $heuresNormales    = $joursTravailles * $heuresJournee;
        $heuresSup         = max(round($heuresTravaillees - $heuresNormales, 2), 0);

        return [
            'jours_travailles' => $joursTravailles,
            'jours_sans_solde' => $joursAbsent,
            'heures_sup'       => $heuresSup,
            'regime'           => $this->getRegimeLabel($employer->company_id),
        ];
    }

    // =========================================================
    // CONGÉS — statut 'accepte' (pas 'Approuvé')
    // =========================================================
    private function getConges(Employer $employer, int $mois, int $annee): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        $congesApprouves = Conge::where('employer_id', $employer->id)
            ->where('statut', 'accepte') // ← corrigé
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('date_debut', [$debut, $fin])
                  ->orWhereBetween('date_fin', [$debut, $fin])
                  ->orWhere(function ($q2) use ($debut, $fin) {
                      $q2->where('date_debut', '<=', $debut)
                         ->where('date_fin', '>=', $fin);
                  });
            })->get();

        $joursConge = 0;
        foreach ($congesApprouves as $c) {
            $d = Carbon::parse($c->date_debut)->max($debut);
            $f = Carbon::parse($c->date_fin)->min($fin);
            if ($f->gte($d)) {
                $joursConge += $c->nombre_jours ?? ($d->diffInWeekdays($f) + 1);
            }
        }

        $dateEmbauche   = Carbon::parse($employer->date_debut ?? $employer->created_at);
        $moisAnciennete = $dateEmbauche->diffInMonths(Carbon::create($annee, $mois, 1));
        $soldeAcquis    = $moisAnciennete * self::CONGES_PAR_MOIS;

        $totalPris = Conge::where('employer_id', $employer->id)
            ->where('statut', 'accepte') // ← corrigé
            ->where('date_fin', '<', $debut)
            ->sum('nombre_jours');

        $soldeRestant = max($soldeAcquis - $totalPris - $joursConge, 0);

        return [
            'jours_conge'    => $joursConge,
            'solde_acquis'   => round($soldeAcquis, 1),
            'solde_pris'     => round($totalPris + $joursConge, 1),
            'solde_restant'  => round($soldeRestant, 1),
            'provision_mois' => self::CONGES_PAR_MOIS,
        ];
    }

    // =========================================================
    // CNSS salarié
    // =========================================================
    private function calculerCNSS(float $brut): float
    {
        $partPlafonnee = min($brut, self::CNSS_PLAFOND) * self::CNSS_TAUX_PLAFO;
        $partMaladie   = $brut * self::CNSS_MALADIE;
        return round($partPlafonnee + $partMaladie, 3);
    }

    // =========================================================
    // IRPP annuel — barème 2025
    // =========================================================
    private function calculerIRPPAnnuel(float $base): float
    {
        if ($base <= 0)      return 0;
        if ($base <= 5000)   return 0;
        if ($base <= 10000)  return ($base - 5000) * 0.15;
        if ($base <= 20000)  return 750  + ($base - 10000) * 0.25;
        if ($base <= 30000)  return 3250 + ($base - 20000) * 0.30;
        if ($base <= 40000)  return 6250 + ($base - 30000) * 0.33;
        if ($base <= 50000)  return 9550 + ($base - 40000) * 0.36;
        if ($base <= 70000)  return 13150 + ($base - 50000) * 0.38;
        return 20750 + ($base - 70000) * 0.40;
    }

    // =========================================================
    // HEURES SUPPLÉMENTAIRES
    // =========================================================
    private function calculerHeuresSup(float $salaireBase, float $heuresSup, int $companyId): float
    {
        if ($heuresSup <= 0) return 0;

        $heuresMensuelles = $this->getHeuresMensuelles($companyId);
        $tauxHoraire      = $salaireBase / $heuresMensuelles;
        $regime           = $this->getRegimeLabel($companyId);

        if ($regime === '48h') {
            return round($heuresSup * $tauxHoraire * 1.75, 3);
        }

        if ($heuresSup <= 8) {
            return round($heuresSup * $tauxHoraire * 1.25, 3);
        }

        return round((8 * $tauxHoraire * 1.25) + (($heuresSup - 8) * $tauxHoraire * 1.50), 3);
    }

    // =========================================================
    // CALCUL PRINCIPAL
    // =========================================================
    private function calculerSalaireParContrat(
        Employer $employer,
        int $mois,
        int $annee,
        float $primes     = 0,
        float $indemnites = 0
    ): array {
        $salaireBase = $employer->salaire ?? 0;
        $companyId   = $employer->company_id;
        $regime      = $this->getRegimeLabel($companyId);

        $pointage        = $this->getPointage($employer, $mois, $annee);
        $joursTravailles = $pointage['jours_travailles'];
        $joursSansSolde  = $pointage['jours_sans_solde'];
        $heuresSup       = $pointage['heures_sup'];

        $conges     = $this->getConges($employer, $mois, $annee);
        $joursConge = $conges['jours_conge'];
        $joursPayes = $joursTravailles + $joursConge;

        $salaireProratise = round($salaireBase * ($joursPayes / self::JOURS_OUVRES), 3);
        $montantHS        = $this->calculerHeuresSup($salaireBase, $heuresSup, $companyId);

        $tauxJournalier   = $salaireBase / self::JOURS_OUVRES;
        $retenueSansSolde = round($joursSansSolde * $tauxJournalier, 3);

        $salaireBrut = $salaireProratise + $montantHS + $primes + $indemnites;

        $cnss             = $this->calculerCNSS($salaireBrut);
        $imposableMensuel = $salaireBrut - $cnss;

        $fraisProAnnuel       = min($imposableMensuel * 12 * 0.10, 2000);
        $deductionChefFamille = $employer->chef_famille ? 300 : 0;

        $baremeEnfants    = [90, 75, 60, 45];
        $deductionEnfants = 0;
        $nbEnfants        = min($employer->nombre_enfants ?? 0, 4);
        for ($i = 0; $i < $nbEnfants; $i++) {
            $deductionEnfants += $baremeEnfants[$i];
        }

        $deductionInfirmes = ($employer->nombre_enfants_infirmes ?? 0) * 2000;
        $totalDeductions   = $fraisProAnnuel + $deductionChefFamille + $deductionEnfants + $deductionInfirmes;

        $baseIRPPAnnuelle = max(($imposableMensuel * 12) - $totalDeductions, 0);
        $irppMensuel      = round($this->calculerIRPPAnnuel($baseIRPPAnnuelle) / 12, 3);
        $css              = round($salaireBrut * self::CSS_TAUX, 3);

        $salaireNet = $salaireBrut - $cnss - $irppMensuel - $css - $retenueSansSolde;

        switch ($employer->type_contrat) {
            case 'CIVP':
                $cnss        = 0;
                $irppMensuel = 0;
                $css         = 0;
                $salaireNet  = $salaireBrut - $retenueSansSolde;
                break;

            case 'Karama':
                $cnss        = round($cnss * 0.5, 3);
                $irppMensuel = 0;
                $css         = 0;
                $salaireNet  = $salaireBrut - $cnss - $retenueSansSolde;
                break;
        }

        return [
            'type_contrat'        => $employer->type_contrat,
            'regime_horaire'      => $regime,
            'jours_travailles'    => $joursTravailles,
            'jours_conge'         => $joursConge,
            'jours_sans_solde'    => $joursSansSolde,
            'jours_payes'         => $joursPayes,
            'salaire_base'        => round($salaireBase, 3),
            'salaire_proratise'   => round($salaireProratise, 3),
            'heures_sup'          => $heuresSup,
            'montant_heures_sup'  => round($montantHS, 3),
            'primes'              => round($primes, 3),
            'indemnites'          => round($indemnites, 3),
            'retenue_sans_solde'  => round($retenueSansSolde, 3),
            'salaire_brut'        => round($salaireBrut, 3),
            'cnss'                => round($cnss, 3),
            'irpp'                => round($irppMensuel, 3),
            'css'                 => round($css, 3),
            'salaire_net'         => round($salaireNet, 3),
            'conges'              => $conges,
        ];
    }

    // =========================================================
    // INDEX
    // =========================================================
    public function index(Request $request)
    {
        $user         = auth()->user();
        $isPaymentDay = false;

        $query = Payment::with('employer');

        if ($user->hasRole('rh')) {
            $config = Configuration::where('company_id', $user->company_id)
                ->where('type', 'PAYMENT_DATEE')->first();
            $isPaymentDay = $config
                ? intval(date('d')) == intval($config->value)
                : false;

            $query->whereHas('employer', fn($q) =>
                $q->where('company_id', $user->company_id)
            );
        }

        if ($request->filled('month'))    $query->where('month', $request->month);
        if ($request->filled('year'))     $query->where('year', $request->year);
        if ($request->filled('employer')) {
            $s = $request->employer;
            $query->whereHas('employer', fn($q) =>
                $q->where('nom', 'like', "%$s%")->orWhere('prenom', 'like', "%$s%")
            );
        }

        $payments = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('paiements.index', compact('payments', 'isPaymentDay'));
    }

    // =========================================================
    // INIT PAYMENT
    // =========================================================
    public function initPayment()
    {
        $user = auth()->user();

        if (!$user->hasRole('rh') && !$user->hasRole('admin')) {
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
        $currentMonthFrench = $monthMapping[strtoupper($now->format('F'))] ?? strtoupper($now->format('F'));
        $currentYear        = $now->format('Y');
        $moisInt            = (int) $now->format('m');
        $anneeInt           = (int) $currentYear;

        $employers = Employer::where('company_id', $user->company_id)
            ->whereNotNull('salaire')
            ->whereDoesntHave('payments', fn($q) =>
                $q->where('month', $currentMonthFrench)->where('year', $currentYear)
            )->get();

        if ($employers->isEmpty()) {
            return redirect()->back()->with('error_message',
                'Tous les employés ont déjà été payés pour ' . $currentMonthFrench . ' ' . $currentYear . '.'
            );
        }

        $count = 0;

        foreach ($employers as $employer) {
            try {
                $calcul = $this->calculerSalaireParContrat($employer, $moisInt, $anneeInt);

                Payment::create([
                    'reference'          => strtoupper(Str::random(10)),
                    'employer_id'        => $employer->id,
                    'month'              => $currentMonthFrench,
                    'year'               => $currentYear,
                    'type_contrat'       => $calcul['type_contrat'],
                    'salaire_base'       => $calcul['salaire_base'],
                    'heures_sup'         => $calcul['heures_sup'],
                    'montant_heures_sup' => $calcul['montant_heures_sup'],
                    'primes'             => $calcul['primes'],
                    'indemnites'         => $calcul['indemnites'],
                    'salaire_brut'       => $calcul['salaire_brut'],
                    'cnss'               => $calcul['cnss'],
                    'irpp'               => $calcul['irpp'],
                    'css'                => $calcul['css'],
                    'amount'             => $calcul['salaire_net'],
                    'launch_date'        => now(),
                    'done_time'          => now(),
                    'status'             => 'SUCCESS',
                ]);

                $count++;

            } catch (Exception $e) {
                Log::error("Erreur paie employer#{$employer->id} : " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success_message',
            $count . ' fiche(s) de paie générée(s) pour ' . $currentMonthFrench . ' ' . $currentYear . '.'
        );
    }

    // =========================================================
    // HELPER — mois en entier
    // =========================================================
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

    // =========================================================
    // DOWNLOAD INVOICE
    // =========================================================
    public function download_invoice(Payment $payment)
    {
        try {
            $fullPaymentInfo = Payment::with('employer')->findOrFail($payment->id);

            $moisInt = $this->moisEnInt($payment->month);
            $debut   = Carbon::create($payment->year, $moisInt, 1)->startOfMonth();
            $fin     = Carbon::create($payment->year, $moisInt, 1)->endOfMonth();

            $conges = Conge::where('employer_id', $payment->employer_id)
                ->where('statut', 'accepte') // ← corrigé
                ->where(function ($q) use ($debut, $fin) {
                    $q->whereBetween('date_debut', [$debut, $fin])
                      ->orWhereBetween('date_fin', [$debut, $fin]);
                })->get();

            $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
            return $pdf->download(
                'fiche-paie-' . $fullPaymentInfo->employer->nom . '-' . $payment->month . '-' . $payment->year . '.pdf'
            );
        } catch (Exception $e) {
            return redirect()->back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================
    // PREVIEW INVOICE
    // =========================================================
    public function preview_invoice(Payment $payment)
    {
        try {
            $fullPaymentInfo = Payment::with('employer')->findOrFail($payment->id);

            $moisInt = $this->moisEnInt($payment->month);
            $debut   = Carbon::create($payment->year, $moisInt, 1)->startOfMonth();
            $fin     = Carbon::create($payment->year, $moisInt, 1)->endOfMonth();

            $conges = Conge::where('employer_id', $payment->employer_id)
                ->where('statut', 'accepte') // ← corrigé
                ->where(function ($q) use ($debut, $fin) {
                    $q->whereBetween('date_debut', [$debut, $fin])
                      ->orWhereBetween('date_fin', [$debut, $fin]);
                })->get();

            $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
            return $pdf->stream(
                'fiche-paie-' . $fullPaymentInfo->employer->nom . '-' . $payment->month . '-' . $payment->year . '.pdf'
            );
        } catch (Exception $e) {
            return redirect()->back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }
}