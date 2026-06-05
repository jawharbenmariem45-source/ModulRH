<?php

namespace App\Http\Controllers;

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
    private static array $FERIES_FIXES = [
        '01-01', '03-20', '04-09', '05-01', '06-01',
        '07-25', '08-13', '10-15', '12-17',
    ];

    private static array $FERIES_VARIABLES_2026 = [
        '2026-03-20', '2026-03-21', '2026-05-26',
        '2026-05-27', '2026-06-15', '2026-08-24',
    ];

    private function estJourFerie(Carbon $date): bool
    {
        if (in_array($date->format('m-d'), self::$FERIES_FIXES)) return true;
        return in_array($date->format('Y-m-d'), self::$FERIES_VARIABLES_2026);
    }

    public static function getJoursFeries2026(): array
    {
        return [
            '2026-01-01' => 'Nouvel An',
            '2026-03-20' => 'Fête de l\'Indépendance + Aïd al-Fitr Jour 1',
            '2026-03-21' => 'Aïd al-Fitr - Jour 2',
            '2026-04-09' => 'Jour des Martyrs',
            '2026-05-01' => 'Fête du Travail',
            '2026-05-26' => 'Aïd al-Adha - Jour 1',
            '2026-05-27' => 'Aïd al-Adha - Jour 2',
            '2026-06-01' => 'Fête de la Jeunesse',
            '2026-06-15' => 'Ras El Am Hégirien (Nouvel An islamique)',
            '2026-07-25' => 'Fête de la République',
            '2026-08-13' => 'Fête de la Femme',
            '2026-08-24' => 'Mouled (Anniversaire du Prophète Mohamed)',
            '2026-10-15' => 'Fête de l\'Évacuation',
            '2026-12-17' => 'Fête de la Révolution',
        ];
    }

    private function compterJoursOuvres(string $startDate, string $endDate): int
    {
        try {
            $debut = Carbon::parse($startDate)->startOfDay();
            $fin   = Carbon::parse($endDate)->startOfDay();
            $jours = 0;
            $jour  = $debut->copy();
            while ($jour->lte($fin)) {
                if (!$jour->isWeekend() && !$this->estJourFerie($jour)) $jours++;
                $jour->addDay();
            }
            return $jours;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculerSoldeConges(User $user): array
    {
        if ($user->contract_type === 'CIVP') {
            return [
                'droits_annuels'   => 0,
                'jours_acquis'     => 0,
                'jours_pris'       => 0,
                'solde'            => 0,
                'taux_mensuel'     => 0,
                'anciennete_ans'   => 0,
                'bonus_anciennete' => 0,
                'note'             => 'CIVP : pas de congé légal (stage)',
            ];
        }

        $ancienneteAns  = 0;
        $moisTravailles = 0;

        if ($user->start_date) {
            try {
                $dateEmbauche   = Carbon::parse($user->start_date);
                $ancienneteAns  = (int) $dateEmbauche->diffInYears(Carbon::now());
                $moisTravailles = (int) $dateEmbauche->diffInMonths(Carbon::now());
            } catch (\Exception $e) {}
        }

        $bonusAnciennete = 0;
        if ($ancienneteAns >= 20)     $bonusAnciennete = 4;
        elseif ($ancienneteAns >= 15) $bonusAnciennete = 3;
        elseif ($ancienneteAns >= 10) $bonusAnciennete = 2;
        elseif ($ancienneteAns >= 5)  $bonusAnciennete = 1;

        $droitsAnnuels = 22 + $bonusAnciennete;
        $tauxMensuel   = round($droitsAnnuels / 12, 3);
        $joursAcquis   = min(round($moisTravailles * $tauxMensuel, 1), $droitsAnnuels);

        $congesApprouves = Conge::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotIn('type', ['Maladie', 'Maternité'])
            ->get();

        $joursPris = 0;
        foreach ($congesApprouves as $conge) {
            $joursPris += $this->compterJoursOuvres($conge->start_date, $conge->end_date);
        }

        $solde = max(round($joursAcquis - $joursPris, 1), 0);
        $note  = $bonusAnciennete > 0
            ? "Bonus ancienneté : +{$bonusAnciennete}j ({$ancienneteAns} ans de service — Art. 115 Code du Travail)"
            : '';

        return [
            'droits_annuels'   => $droitsAnnuels,
            'jours_acquis'     => $joursAcquis,
            'jours_pris'       => $joursPris,
            'solde'            => $solde,
            'taux_mensuel'     => $tauxMensuel,
            'anciennete_ans'   => $ancienneteAns,
            'bonus_anciennete' => $bonusAnciennete,
            'note'             => $note,
        ];
    }

    private function validerDemandeConge(User $user, string $startDate, string $endDate, string $type): array
    {
        if (in_array($type, ['Maladie', 'Maternité'])) {
            $jours = $this->compterJoursOuvres($startDate, $endDate);
            return ['valide' => true, 'jours' => $jours, 'message' => ''];
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

        $congesEnAttente  = $user->conges()->where('status', 'pending')->count();
        $congesApprouves  = $user->conges()->where('status', 'approved')->count();
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
        $user         = auth()->user();
        $company      = Company::find($user->company_id);
        $isPaymentDay = $company ? intval(date('d')) == intval($company->payment_date) : false;

        $query = Payment::where('user_id', $user->id);

        // ✅ month stocké en int — pas de strtoupper
        if ($request->filled('month')) $query->where('month', $request->month);
        if ($request->filled('year'))  $query->where('year', $request->year);

        $payments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        return view('employers.paiements', compact('payments', 'isPaymentDay'));
    }

    public function downloadPaiement($paymentId)
    {
        $user            = auth()->user();
        $fullPaymentInfo = Payment::with('employer')->findOrFail($paymentId);
        if ($fullPaymentInfo->user_id !== $user->id) abort(403);

        [$debut, $fin] = $this->getPeriodeMois($fullPaymentInfo);

        $conges = Conge::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(fn($q) => $q->whereBetween('start_date', [$debut, $fin])
                ->orWhereBetween('end_date', [$debut, $fin]))->get();

        $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
        return $pdf->download('facture_' . $user->last_name . '_' . $fullPaymentInfo->month . '_' . $fullPaymentInfo->year . '.pdf');
    }

    public function previewPaiement($paymentId)
    {
        $user            = auth()->user();
        $fullPaymentInfo = Payment::with('employer')->findOrFail($paymentId);
        if ($fullPaymentInfo->user_id !== $user->id) abort(403);

        [$debut, $fin] = $this->getPeriodeMois($fullPaymentInfo);

        $conges = Conge::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(fn($q) => $q->whereBetween('start_date', [$debut, $fin])
                ->orWhereBetween('end_date', [$debut, $fin]))->get();

        $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
        return $pdf->stream('facture_' . $user->last_name . '_' . $fullPaymentInfo->month . '_' . $fullPaymentInfo->year . '.pdf');
    }

    private function getPeriodeMois($payment): array
    {
        $moisInt = is_numeric($payment->month) ? (int) $payment->month : 1;
        return [
            Carbon::create($payment->year, $moisInt, 1)->startOfMonth(),
            Carbon::create($payment->year, $moisInt, 1)->endOfMonth(),
        ];
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

        $user       = auth()->user();
        $validation = $this->validerDemandeConge($user, $request->start_date, $request->end_date, $request->type);

        if (!$validation['valide']) {
            return back()->withInput()->with('error', $validation['message']);
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('leaves/documents', 'public');
        }

        Conge::create([
            'user_id'    => $user->id,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'days_count' => $validation['jours'],
            'type'       => $request->type,
            'reason'     => $request->reason,
            'document'   => $documentPath,
            'status'     => 'pending',
        ]);

        return redirect()->route('employer_space.conges')
            ->with('success', "Demande soumise : {$validation['jours']} jour(s) ouvré(s) demandé(s).");
    }

    public function editConge(Conge $conge)
    {
        $user = auth()->user();

        if ($conge->user_id !== $user->id || $conge->status !== 'pending') {
            return redirect()->route('employer_space.conges')->with('error', 'Modification impossible.');
        }

        $soldeConges = $this->calculerSoldeConges($user);
        return view('employers.conge_edit', compact('conge', 'soldeConges'));
    }

    public function updateConge(Request $request, Conge $conge)
    {
        $user = auth()->user();

        if ($conge->user_id !== $user->id || $conge->status !== 'pending') {
            return redirect()->route('employer_space.conges');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'       => 'required|in:Congé Annuel,Maladie,Maternité,Sans solde',
            'reason'     => 'nullable|string|max:500',
            'document'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $validation = $this->validerDemandeConge($user, $request->start_date, $request->end_date, $request->type);

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
            'days_count' => $validation['jours'],
            'type'       => $request->type,
            'reason'     => $request->reason,
            'document'   => $documentPath,
        ]);

        return redirect()->route('employer_space.conges')
            ->with('success', 'Demande modifiée avec succès !');
    }

    public function deleteConge(Conge $conge)
    {
        $user = auth()->user();

        if ($conge->user_id !== $user->id || $conge->status !== 'pending') {
            return redirect()->route('employer_space.conges')->with('error', 'Suppression impossible.');
        }

        if ($conge->document) Storage::disk('public')->delete($conge->document);
        $conge->delete();

        return redirect()->route('employer_space.conges')
            ->with('success', 'Demande annulée avec succès.');
    }
}