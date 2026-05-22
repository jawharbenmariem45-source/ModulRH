<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Conge;
use Carbon\Carbon;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployerDashboardController extends Controller
{
    // ── Jours fériés officiels Tunisie ────────────────────────────────────────
    // Mis à jour annuellement (fêtes religieuses variables)
    private static array $JOURS_FERIES = [
        // Fixes
        '01-01', // Nouvel An
        '03-20', // Fête de l'Indépendance
        '04-09', // Jour des Martyrs
        '05-01', // Fête du Travail
        '06-01', // Fête de la Jeunesse
        '07-25', // Fête de la République
        '08-13', // Fête de la Femme
        '10-15', // Fête de l'Évacuation
    ];

    // Jours fériés variables 2026 (religieux)
    private static array $JOURS_FERIES_VARIABLES = [
        '2026-03-20', // Aïd el-Fitr
        '2026-03-21', // Aïd el-Fitr
        '2026-06-27', // Aïd el-Adha
        '2026-06-28', // Aïd el-Adha
        '2026-06-25', // Ras el-Am Hijri
        '2026-09-04', // Mouled
    ];

    /**
     * Vérifie si une date est un jour férié tunisien
     */
    private function estJourFerie(Carbon $date): bool
    {
        $mmjj = $date->format('m-d');
        foreach (self::$JOURS_FERIES as $ferie) {
            if ($mmjj === $ferie) return true;
        }
        return in_array($date->format('Y-m-d'), self::$JOURS_FERIES_VARIABLES);
    }

    /**
     * Compte les jours ouvrés à déduire entre deux dates
     * (lundi-vendredi, hors jours fériés tunisiens)
     */
    private function compterJoursOuvres(string $startDate, string $endDate): int
    {
        try {
            $debut = Carbon::parse($startDate)->startOfDay();
            $fin   = Carbon::parse($endDate)->startOfDay();
            $jours = 0;
            $jour  = $debut->copy();

            while ($jour->lte($fin)) {
                if (!$jour->isWeekend() && !$this->estJourFerie($jour)) {
                    $jours++;
                }
                $jour->addDay();
            }
            return $jours;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calcul du solde de congés selon le Code du Travail tunisien 2026
     *
     * Règles :
     * - CIVP : 0 jour légal (stage, pas de congé payé)
     * - CDI/CDD/Karama : 1.833 jour ouvré par mois travaillé
     * - Bonus ancienneté (Art. 117 Code du Travail) :
     *   +1j après 5 ans, +2j après 10 ans, +3j après 15 ans, +4j après 20 ans
     * - Déduction : jours ouvrés uniquement (lundi-vendredi, hors fériés)
     */
    private function calculerSoldeConges(User $user): array
    {
        // CIVP : pas de congé légal
        if ($user->contract_type === 'CIVP') {
            return [
                'droits_annuels'  => 0,
                'jours_acquis'    => 0,
                'jours_pris'      => 0,
                'solde'           => 0,
                'taux_mensuel'    => 0,
                'anciennete_ans'  => 0,
                'bonus_anciennete'=> 0,
                'note'            => 'CIVP : pas de congé légal (stage)',
            ];
        }

        if (!$user->start_date) {
            return [
                'droits_annuels'  => 22,
                'jours_acquis'    => 22,
                'jours_pris'      => 0,
                'solde'           => 22,
                'taux_mensuel'    => 1.833,
                'anciennete_ans'  => 0,
                'bonus_anciennete'=> 0,
                'note'            => '',
            ];
        }

        try {
            $dateEmbauche  = Carbon::parse($user->start_date);
            $maintenant    = Carbon::now();
            $ancienneteAns = (int) $dateEmbauche->diffInYears($maintenant);
            $moisTravailles = (int) $dateEmbauche->diffInMonths($maintenant);
        } catch (\Exception $e) {
            $ancienneteAns  = 0;
            $moisTravailles = 0;
        }

        // Taux de base : 1.833 jour/mois = 22 jours/an
        $tauxMensuel = 1.833;

        // Bonus ancienneté (Art. 117 Code du Travail tunisien)
        $bonusAnciennete = 0;
        if ($ancienneteAns >= 20)     $bonusAnciennete = 4;
        elseif ($ancienneteAns >= 15) $bonusAnciennete = 3;
        elseif ($ancienneteAns >= 10) $bonusAnciennete = 2;
        elseif ($ancienneteAns >= 5)  $bonusAnciennete = 1;

        $droitsAnnuels = 22 + $bonusAnciennete;
        $tauxMensuelAjuste = $droitsAnnuels / 12;

        // Jours acquis sur la période travaillée (plafonné aux droits annuels)
        $joursAcquis = min(
            round($moisTravailles * $tauxMensuelAjuste, 1),
            $droitsAnnuels
        );

        // Jours pris (congés approuvés, comptés en jours ouvrés)
        $congesApprouves = Conge::where('user_id', $user->id)
            ->where('status', 'Approuvé')
            ->whereNotIn('type', ['Maladie', 'Maternité'])
            ->get();

        $joursPris = 0;
        foreach ($congesApprouves as $conge) {
            $joursPris += $this->compterJoursOuvres($conge->start_date, $conge->end_date);
        }

        $solde = max(round($joursAcquis - $joursPris, 1), 0);

        return [
            'droits_annuels'   => $droitsAnnuels,
            'jours_acquis'     => $joursAcquis,
            'jours_pris'       => $joursPris,
            'solde'            => $solde,
            'taux_mensuel'     => round($tauxMensuelAjuste, 3),
            'anciennete_ans'   => $ancienneteAns,
            'bonus_anciennete' => $bonusAnciennete,
            'note'             => $bonusAnciennete > 0
                ? "Bonus ancienneté : +{$bonusAnciennete}j (Art. 117 Code du Travail)"
                : '',
        ];
    }

    /**
     * Vérifie si une demande de congé est valide selon le solde disponible
     */
    private function validerDemandeConge(User $user, string $startDate, string $endDate, string $type): array
    {
        // Maladie et Maternité ne déduisent pas le solde annuel
        if (in_array($type, ['Maladie', 'Maternité'])) {
            return ['valide' => true, 'jours' => 0, 'message' => ''];
        }

        $joursOuvres = $this->compterJoursOuvres($startDate, $endDate);
        $solde       = $this->calculerSoldeConges($user);

        if ($joursOuvres > $solde['solde']) {
            return [
                'valide'  => false,
                'jours'   => $joursOuvres,
                'message' => "Solde insuffisant. Vous demandez {$joursOuvres} jour(s) ouvré(s) mais il vous reste {$solde['solde']} jour(s).",
            ];
        }

        return ['valide' => true, 'jours' => $joursOuvres, 'message' => ''];
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $user = auth()->user();

        if ($user->contract_type === 'CDI') {
            $contrat = true;
        } elseif ($user->contract_type && $user->end_date) {
            try { $contrat = Carbon::parse($user->end_date)->isFuture(); }
            catch (\Exception $e) { $contrat = true; }
        } else {
            $contrat = $user->contract_type ? true : false;
        }

        $congesEnAttente  = $user->conges()->whereIn('status', ['En attente', 'en attente', 'en_attente'])->count();
        $congesApprouves  = $user->conges()->whereIn('status', ['Approuvé', 'approuvé', 'accepte', 'APPROUVE'])->count();
        $totalPaiements   = $user->payments()->count();
        $dernierConges    = $user->conges()->latest()->take(5)->get();
        $dernierPaiements = $user->payments()->latest()->take(5)->get();
        $soldeConges      = $this->calculerSoldeConges($user);

        return view('dashboard.employer', compact(
            'contrat', 'congesEnAttente', 'congesApprouves',
            'totalPaiements', 'dernierConges', 'dernierPaiements', 'soldeConges'
        ));
    }

    public function contrat()
    {
        $user   = auth()->user();
        $jours  = null;
        $statut = 'Actif';

        if ($user->contract_type === 'CDI') {
            $statut = 'Actif';
        } elseif ($user->end_date) {
            try {
                $jours = Carbon::today()->diffInDays(Carbon::parse($user->end_date), false);
                if ($jours < 0)       $statut = 'Expiré';
                elseif ($jours <= 30) $statut = 'Expire bientôt';
                else                  $statut = 'Actif';
            } catch (\Exception $e) { $statut = 'Actif'; }
        }

        $employer = $user;
        return view('employers.contracts', compact('employer', 'jours', 'statut'));
    }

    public function paiements(Request $request)
    {
        $user = auth()->user();
        $company      = Company::find($user->company_id);
        $isPaymentDay = $company ? intval(date('d')) == intval($company->payment_date) : false;

        $query = Payment::where('user_id', $user->id);
        if ($request->filled('month')) $query->where('month', strtoupper($request->month));
        if ($request->filled('year'))  $query->where('year', $request->year);

        $payments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        return view('employers.paiements', compact('payments', 'isPaymentDay'));
    }

    public function downloadPaiement($paymentId)
    {
        $user            = auth()->user();
        $fullPaymentInfo = Payment::with('employer')->findOrFail($paymentId);
        if ($fullPaymentInfo->user_id !== $user->id) abort(403);

        $moisMap = [
            'JANVIER'=>1,'FEVRIER'=>2,'MARS'=>3,'AVRIL'=>4,'MAI'=>5,'JUIN'=>6,
            'JUILLET'=>7,'AOUT'=>8,'SEPTEMBRE'=>9,'OCTOBRE'=>10,'NOVEMBRE'=>11,'DECEMBRE'=>12,
        ];
        $moisInt = $moisMap[strtoupper($fullPaymentInfo->month)] ?? 1;
        $debut   = Carbon::create($fullPaymentInfo->year, $moisInt, 1)->startOfMonth();
        $fin     = Carbon::create($fullPaymentInfo->year, $moisInt, 1)->endOfMonth();

        $conges = Conge::where('user_id', $user->id)
            ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('start_date', [$debut, $fin])
                  ->orWhereBetween('end_date', [$debut, $fin]);
            })->get();

        $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
        return $pdf->download('facture_' . $user->last_name . '.pdf');
    }

    public function previewPaiement($paymentId)
    {
        $user            = auth()->user();
        $fullPaymentInfo = Payment::with('employer')->findOrFail($paymentId);
        if ($fullPaymentInfo->user_id !== $user->id) abort(403);

        $moisMap = [
            'JANVIER'=>1,'FEVRIER'=>2,'MARS'=>3,'AVRIL'=>4,'MAI'=>5,'JUIN'=>6,
            'JUILLET'=>7,'AOUT'=>8,'SEPTEMBRE'=>9,'OCTOBRE'=>10,'NOVEMBRE'=>11,'DECEMBRE'=>12,
        ];
        $moisInt = $moisMap[strtoupper($fullPaymentInfo->month)] ?? 1;
        $debut   = Carbon::create($fullPaymentInfo->year, $moisInt, 1)->startOfMonth();
        $fin     = Carbon::create($fullPaymentInfo->year, $moisInt, 1)->endOfMonth();

        $conges = Conge::where('user_id', $user->id)
            ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('start_date', [$debut, $fin])
                  ->orWhereBetween('end_date', [$debut, $fin]);
            })->get();

        $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
        return $pdf->stream('facture_' . $user->last_name . '.pdf');
    }

    public function conges()
    {
        $user        = auth()->user();
        $conges      = $user->conges()->orderBy('created_at', 'desc')->get();
        $soldeConges = $this->calculerSoldeConges($user);
        $employer    = $user;

        return view('conges.conges', compact('employer', 'conges', 'soldeConges'));
    }

    public function createConge()
    {
        return redirect()->route('employer_space.conges');
    }

    public function storeConge(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'       => 'required|in:Congé Annuel,Maladie,Maternité,Sans solde',
            'reason'     => 'nullable|string|max:500',
            'document'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = auth()->user();

        // Calculer les jours ouvrés réels
        $joursOuvres = $this->compterJoursOuvres($request->start_date, $request->end_date);

        // Valider le solde (sauf maladie/maternité)
        $validation = $this->validerDemandeConge($user, $request->start_date, $request->end_date, $request->type);
        if (!$validation['valide']) {
            return back()->withInput()->with('error', $validation['message']);
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('leaves/documents', 'public');
        }

        // Jours calendaires pour affichage
        $joursCalendaires = Carbon::parse($request->start_date)
            ->diffInDays(Carbon::parse($request->end_date)) + 1;

        Conge::create([
            'user_id'    => $user->id,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'days_count' => $joursOuvres, // on stocke les jours ouvrés déduits
            'type'       => $request->type,
            'reason'     => $request->reason,
            'document'   => $documentPath,
            'status'     => 'En attente',
        ]);

        return redirect()->route('employer_space.conges')
            ->with('success', "Demande soumise : {$joursOuvres} jour(s) ouvré(s) demandé(s).");
    }

    public function editConge(Conge $conge)
    {
        $user = auth()->user();

        if ($conge->user_id !== $user->id || !in_array($conge->status, ['En attente', 'en attente', 'en_attente'])) {
            return redirect()->route('employer_space.conges')->with('error', 'Modification impossible.');
        }

        $soldeConges = $this->calculerSoldeConges($user);
        return view('employers.conge_edit', compact('conge', 'soldeConges'));
    }

    public function updateConge(Request $request, Conge $conge)
    {
        $user = auth()->user();

        if ($conge->user_id !== $user->id || !in_array($conge->status, ['En attente', 'en attente', 'en_attente'])) {
            return redirect()->route('employer_space.conges');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'       => 'required|in:Congé Annuel,Maladie,Maternité,Sans solde',
            'reason'     => 'nullable|string|max:500',
            'document'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $joursOuvres = $this->compterJoursOuvres($request->start_date, $request->end_date);
        $validation  = $this->validerDemandeConge($user, $request->start_date, $request->end_date, $request->type);

        if (!$validation['valide']) {
            return back()->withInput()->with('error', $validation['message']);
        }

        $documentPath = $conge->document;
        if ($request->hasFile('document')) {
            if ($conge->document) Storage::disk('public')->delete($conge->document);
            $documentPath = $request->file('document')->store('leaves/documents', 'public');
        }

        $conge->update([
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'days_count' => $joursOuvres,
            'type'       => $request->type,
            'reason'     => $request->reason,
            'document'   => $documentPath,
        ]);

        return redirect()->route('employer_space.conges')->with('success', 'Demande modifiée avec succès !');
    }

    public function deleteConge(Conge $conge)
    {
        $user = auth()->user();

        if ($conge->user_id !== $user->id || !in_array($conge->status, ['En attente', 'en attente', 'en_attente'])) {
            return redirect()->route('employer_space.conges')->with('error', 'Suppression impossible.');
        }

        if ($conge->document) Storage::disk('public')->delete($conge->document);
        $conge->delete();

        return redirect()->route('employer_space.conges')->with('success', 'Demande annulée avec succès.');
    }
}