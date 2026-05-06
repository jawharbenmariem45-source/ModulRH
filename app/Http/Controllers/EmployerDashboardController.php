<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Configuration;
use Illuminate\Http\Request;
use App\Models\Conge;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Employer;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployerDashboardController extends Controller
{
    // =============================================
    // CALCUL JOURS CONGÉS (normes tunisiennes)
    // =============================================
    private function calculerJoursConges(Employer $employer): int
    {
        if (!$employer->date_debut) {
            return 12;
        }

        $dateDebut      = Carbon::parse($employer->date_debut);
        $anciennete     = $dateDebut->diffInYears(Carbon::now());
        $moisTravailles = (int) $dateDebut->diffInMonths(Carbon::now());

        if ($moisTravailles < 1) {
            $moisTravailles = 1;
        }

        switch ($employer->type_contrat) {

            case 'CDI':
                if ($anciennete < 1) {
                    return min($moisTravailles, 12);
                }
                $base = 12;
                if ($anciennete > 5) {
                    $base += ($anciennete - 5);
                }
                return min($base, 30);

            case 'CDD':
                if ($employer->date_fin) {
                    $moisContrat = (int) Carbon::parse($employer->date_debut)
                                               ->diffInMonths(Carbon::parse($employer->date_fin));
                    return min($moisTravailles, max($moisContrat, 1));
                }
                return min($moisTravailles, 12);

            case 'CIVP':
                return min($moisTravailles, 12);

            case 'Karama':
                return min($moisTravailles, 12);

            default:
                return min($moisTravailles, 12);
        }
    }

    // =============================================
    // HELPER SOLDE CONGÉS
    // =============================================
    private function getSoldeConges(Employer $employer): array
    {
        $joursAccordes = $this->calculerJoursConges($employer);
        $conges        = $employer->conges()->get();

        $congesPris = $conges->where('statut', 'accepte')->sum(function($conge) {
            return Carbon::parse($conge->date_debut)
                         ->diffInDays(Carbon::parse($conge->date_fin)) + 1;
        });

        $solde = $joursAccordes - $congesPris;

        return compact('joursAccordes', 'congesPris', 'solde');
    }

    // =============================================
    // HELPER — mois en entier
    // =============================================
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

    // =============================================
    // DASHBOARD
    // =============================================
    public function dashboard()
{
    $employer = auth('employer')->user();

    if ($employer->type_contrat === 'CDI') {
        $contrat = true;
    } elseif ($employer->type_contrat && $employer->date_fin) {
        try {
            $contrat = Carbon::parse($employer->date_fin)->isFuture();
        } catch (\Exception $e) {
            $contrat = true;
        }
    } else {
        $contrat = $employer->type_contrat ? true : false;
    }

    $congesEnAttente = $employer->conge()
        ->whereIn('statut', ['en_attente', 'En attente', 'en attente'])
        ->count();

    $congesApprouves = $employer->conge()
        ->whereIn('statut', ['accepte', 'Approuvé', 'approuvé', 'APPROUVE', 'Approuve'])
        ->count();

    $totalPaiements   = $employer->payments()->count();
    $dernierConges    = $employer->conge()->latest()->take(5)->get();
    $dernierPaiements = $employer->payments()->latest()->take(5)->get();

    return view('dashboard.employer', compact(
        'contrat',
        'congesEnAttente',
        'congesApprouves',
        'totalPaiements',
        'dernierConges',
        'dernierPaiements'
    ));
}

    // =============================================
    // CONTRAT (lecture seule)
    // =============================================
    public function contrat()
    {
        $employer = auth('employer')->user();
        $jours    = null;
        $statut   = 'Actif';

        if ($employer->type_contrat === 'CDI') {
            $statut = 'Actif';
        } elseif ($employer->date_fin) {
            $jours = Carbon::today()->diffInDays(Carbon::parse($employer->date_fin), false);
            if ($jours < 0)       $statut = 'Expiré';
            elseif ($jours <= 30) $statut = 'Expire bientôt';
            else                  $statut = 'Actif';
        }

        return view('employers.contracts', compact('employer', 'jours', 'statut'));
    }

    // =============================================
    // PAIEMENTS
    // =============================================
    public function paiements(Request $request)
    {
        $employer = auth('employer')->user();

        $defaultPaymentDateQuery = Configuration::where('company_id', $employer->company_id)
            ->where('type', 'PAYMENT_DATEE')->first();
        $defaultPaymentDate = $defaultPaymentDateQuery->value ?? null;
        $isPaymentDay       = intval(date('d')) == intval($defaultPaymentDate);

        $query = Payment::where('employer_id', $employer->id);

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('employers.paiements', compact('payments', 'isPaymentDay'));
    }

    // =============================================
    // DOWNLOAD PAIEMENT
    // =============================================
    public function downloadPaiement($paymentId)
    {
        $employer        = auth('employer')->user();
        $fullPaymentInfo = Payment::with('employer')->findOrFail($paymentId);

        if ($fullPaymentInfo->employer_id !== $employer->id) {
            abort(403);
        }

        $moisInt = $this->moisEnInt($fullPaymentInfo->month);
        $debut   = Carbon::create($fullPaymentInfo->year, $moisInt, 1)->startOfMonth();
        $fin     = Carbon::create($fullPaymentInfo->year, $moisInt, 1)->endOfMonth();

        $conges = Conge::where('employer_id', $employer->id)
            ->where('statut', 'accepte')
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('date_debut', [$debut, $fin])
                  ->orWhereBetween('date_fin', [$debut, $fin]);
            })->get();

        $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
        return $pdf->download('facture_' . $fullPaymentInfo->employer->nom . '.pdf');
    }

    // =============================================
    // PREVIEW PAIEMENT
    // =============================================
    public function previewPaiement($paymentId)
    {
        $employer        = auth('employer')->user();
        $fullPaymentInfo = Payment::with('employer')->findOrFail($paymentId);

        if ($fullPaymentInfo->employer_id !== $employer->id) {
            abort(403);
        }

        $moisInt = $this->moisEnInt($fullPaymentInfo->month);
        $debut   = Carbon::create($fullPaymentInfo->year, $moisInt, 1)->startOfMonth();
        $fin     = Carbon::create($fullPaymentInfo->year, $moisInt, 1)->endOfMonth();

        $conges = Conge::where('employer_id', $employer->id)
            ->where('statut', 'accepte')
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('date_debut', [$debut, $fin])
                  ->orWhereBetween('date_fin', [$debut, $fin]);
            })->get();

        $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
        return $pdf->stream('facture_' . $fullPaymentInfo->employer->nom . '.pdf');
    }

    // =============================================
    // CONGÉS
    // =============================================
    public function conges()
    {
        $employer = auth('employer')->user();
        $conges   = $employer->conges()->orderBy('created_at', 'desc')->get();

        [
            'joursAccordes' => $joursAccordes,
            'congesPris'    => $congesPris,
            'solde'         => $solde,
        ] = $this->getSoldeConges($employer);

        return view('conges.conges', compact(
            'employer',
            'conges',
            'congesPris',
            'solde',
            'joursAccordes'
        ));
    }

    public function createConge()
    {
        $employer = auth('employer')->user();

        [
            'joursAccordes' => $joursAccordes,
            'congesPris'    => $congesPris,
            'solde'         => $solde,
        ] = $this->getSoldeConges($employer);

        return view('employers.conges_create', compact('joursAccordes', 'congesPris', 'solde'));
    }

    public function storeConge(Request $request)
    {
        $request->validate([
            'date_debut'  => 'required|date|after_or_equal:today',
            'date_fin'    => 'required|date|after:date_debut',
            'type'        => 'required',
            'motif'       => 'nullable|string|max:500',
        ]);

        $employer = auth('employer')->user();

        [
            'solde' => $solde,
        ] = $this->getSoldeConges($employer);

        $jours = Carbon::parse($request->date_debut)
                       ->diffInDays(Carbon::parse($request->date_fin)) + 1;

        if (!in_array($request->type, ['Maladie', 'Maternité']) && $jours > $solde) {
            return back()
                ->withInput()
                ->with('error', "Vous ne pouvez pas demander $jours jour(s). Il vous reste seulement $solde jour(s) de congé.");
        }

        Conge::create([
            'employer_id'  => $employer->id,
            'date_debut'   => $request->date_debut,
            'date_fin'     => $request->date_fin,
            'nombre_jours' => $jours,
            'type'         => $request->type,
            'motif'        => $request->motif,
            'statut'       => 'en_attente',
        ]);

        return redirect()->route('employer_space.conges')
            ->with('success', 'Demande soumise avec succès !');
    }

    public function editConge(Conge $conge)
    {
        $employer = auth('employer')->user();
        if ($conge->employer_id !== $employer->id || $conge->statut !== 'en_attente') {
            return redirect()->route('employer_space.conges')
                ->with('error', 'Modification impossible.');
        }

        [
            'joursAccordes' => $joursAccordes,
            'congesPris'    => $congesPris,
            'solde'         => $solde,
        ] = $this->getSoldeConges($employer);

        return view('employers.conge_edit', compact('conge', 'joursAccordes', 'congesPris', 'solde'));
    }

    public function updateConge(Request $request, Conge $conge)
    {
        $employer = auth('employer')->user();
        if ($conge->employer_id !== $employer->id || $conge->statut !== 'en_attente') {
            return redirect()->route('employer_space.conges');
        }

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after:date_debut',
            'type'       => 'required|in:Congé Annuel,Maladie,Maternité,Sans solde',
            'motif'      => 'nullable|string|max:500',
        ]);

        ['solde' => $solde] = $this->getSoldeConges($employer);

        $jours = Carbon::parse($request->date_debut)
                       ->diffInDays(Carbon::parse($request->date_fin)) + 1;

        if (!in_array($request->type, ['Maladie', 'Maternité']) && $jours > $solde) {
            return back()
                ->withInput()
                ->with('error', "Vous ne pouvez pas demander $jours jour(s). Il vous reste seulement $solde jour(s) de congé.");
        }

        $conge->update([
            'date_debut'   => $request->date_debut,
            'date_fin'     => $request->date_fin,
            'nombre_jours' => $jours,
            'type'         => $request->type,
            'motif'        => $request->motif,
        ]);

        return redirect()->route('employer_space.conges')
            ->with('success', 'Demande modifiée avec succès !');
    }

    public function deleteConge(Conge $conge)
    {
        $employer = auth('employer')->user();

        if ($conge->employer_id !== $employer->id || $conge->statut !== 'en_attente') {
            return redirect()->route('employer_space.conges')
                ->with('error', 'Suppression impossible.');
        }

        $conge->delete();

        return redirect()->route('employer_space.conges')
            ->with('success', 'Demande annulée avec succès.');
    }
}