<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Conge;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Employer;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployerDashboardController extends Controller
{
    private function calculerJoursConges(Employer $employer): int
    {
        if (!$employer->start_date) {
            return 12;
        }

        try {
            $dateDebut      = Carbon::parse($employer->start_date);
            $anciennete     = $dateDebut->diffInYears(Carbon::now());
            $moisTravailles = (int) $dateDebut->diffInMonths(Carbon::now());
        } catch (\Exception $e) {
            return 12;
        }

        if ($moisTravailles < 1) {
            $moisTravailles = 1;
        }

        switch ($employer->contract_type) {
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
                if ($employer->end_date) {
                    $moisContrat = (int) Carbon::parse($employer->start_date)
                                               ->diffInMonths(Carbon::parse($employer->end_date));
                    return min($moisTravailles, max($moisContrat, 1));
                }
                return min($moisTravailles, 12);

            case 'CIVP':
            case 'Karama':
            default:
                return min($moisTravailles, 12);
        }
    }

    private function getSoldeConges(Employer $employer): array
    {
        $joursAccordes = $this->calculerJoursConges($employer);
        $conges        = $employer->conges()->get();

        $congesPris = $conges->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
                             ->sum(function ($conge) {
                                 try {
                                     return Carbon::parse($conge->start_date)
                                                  ->diffInDays(Carbon::parse($conge->end_date)) + 1;
                                 } catch (\Exception $e) {
                                     return 0;
                                 }
                             });

        $solde = $joursAccordes - $congesPris;

        return compact('joursAccordes', 'congesPris', 'solde');
    }

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

    public function dashboard()
    {
        $employer = auth('employer')->user();

        if ($employer->contract_type === 'CDI') {
            $contrat = true;
        } elseif ($employer->contract_type && $employer->end_date) {
            try {
                $contrat = Carbon::parse($employer->end_date)->isFuture();
            } catch (\Exception $e) {
                $contrat = true;
            }
        } else {
            $contrat = $employer->contract_type ? true : false;
        }

        $congesEnAttente = $employer->conges()
            ->whereIn('status', ['En attente', 'en attente', 'en_attente'])
            ->count();

        $congesApprouves = $employer->conges()
            ->whereIn('status', ['Approuvé', 'approuvé', 'accepte', 'APPROUVE'])
            ->count();

        $totalPaiements   = $employer->payments()->count();
        $dernierConges    = $employer->conges()->latest()->take(5)->get();
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

    public function contrat()
    {
        $employer = auth('employer')->user();
        $jours    = null;
        $statut   = 'Actif';

        if ($employer->contract_type === 'CDI') {
            $statut = 'Actif';
        } elseif ($employer->end_date) {
            try {
                $jours = Carbon::today()->diffInDays(Carbon::parse($employer->end_date), false);
                if ($jours < 0)       $statut = 'Expiré';
                elseif ($jours <= 30) $statut = 'Expire bientôt';
                else                  $statut = 'Actif';
            } catch (\Exception $e) {
                $statut = 'Actif';
            }
        }

        return view('employers.contracts', compact('employer', 'jours', 'statut'));
    }

    public function paiements(Request $request)
    {
        $employer = auth('employer')->user();

        $company      = Company::find($employer->company_id);
        $isPaymentDay = $company
            ? intval(date('d')) == intval($company->payment_date)
            : false;

        $query = Payment::where('employer_id', $employer->id);

        if ($request->filled('month')) {
            $query->where('month', strtoupper($request->month));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('employers.paiements', compact('payments', 'isPaymentDay'));
    }

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
            ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('start_date', [$debut, $fin])
                  ->orWhereBetween('end_date', [$debut, $fin]);
            })->get();

        $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
        return $pdf->download('facture_' . $fullPaymentInfo->employer->last_name . '.pdf');
    }

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
            ->whereIn('status', ['Approuvé', 'approuvé', 'accepte'])
            ->where(function ($q) use ($debut, $fin) {
                $q->whereBetween('start_date', [$debut, $fin])
                  ->orWhereBetween('end_date', [$debut, $fin]);
            })->get();

        $pdf = Pdf::loadView('paiements.facture', compact('fullPaymentInfo', 'conges'));
        return $pdf->stream('facture_' . $fullPaymentInfo->employer->last_name . '.pdf');
    }

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
            'employer', 'conges', 'congesPris', 'solde', 'joursAccordes'
        ));
    }

    public function createConge()
    {
        return redirect()->route('employer_space.conges');
    }

    public function storeConge(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',
            'type'       => 'required|in:Congé Annuel,Maladie,Maternité,Sans solde',
            'reason'     => 'nullable|string|max:500',
            'document'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $employer = auth('employer')->user();

        ['solde' => $solde] = $this->getSoldeConges($employer);

        $jours = Carbon::parse($request->start_date)
                       ->diffInDays(Carbon::parse($request->end_date)) + 1;

        if (!in_array($request->type, ['Maladie', 'Maternité']) && $jours > $solde) {
            return back()
                ->withInput()
                ->with('error', "Vous ne pouvez pas demander $jours jour(s). Il vous reste seulement $solde jour(s) de congé.");
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('leaves/documents', 'public');
        }

        Conge::create([
            'employer_id' => $employer->id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'days_count'  => $jours,
            'type'        => $request->type,
            'reason'      => $request->reason,
            'document'    => $documentPath,
            'status'      => 'En attente',
        ]);

        return redirect()->route('employer_space.conges')
            ->with('success', 'Demande soumise avec succès !');
    }

    public function editConge(Conge $conge)
    {
        $employer = auth('employer')->user();

        if ($conge->employer_id !== $employer->id || !in_array($conge->status, ['En attente', 'en attente', 'en_attente'])) {
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

        if ($conge->employer_id !== $employer->id || !in_array($conge->status, ['En attente', 'en attente', 'en_attente'])) {
            return redirect()->route('employer_space.conges');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'type'       => 'required|in:Congé Annuel,Maladie,Maternité,Sans solde',
            'reason'     => 'nullable|string|max:500',
            'document'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        ['solde' => $solde] = $this->getSoldeConges($employer);

        $jours = Carbon::parse($request->start_date)
                       ->diffInDays(Carbon::parse($request->end_date)) + 1;

        if (!in_array($request->type, ['Maladie', 'Maternité']) && $jours > $solde) {
            return back()
                ->withInput()
                ->with('error', "Vous ne pouvez pas demander $jours jour(s). Il vous reste seulement $solde jour(s) de congé.");
        }

        $documentPath = $conge->document;
        if ($request->hasFile('document')) {
            if ($conge->document) {
                Storage::disk('public')->delete($conge->document);
            }
            $documentPath = $request->file('document')->store('leaves/documents', 'public');
        }

        $conge->update([
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'days_count' => $jours,
            'type'       => $request->type,
            'reason'     => $request->reason,
            'document'   => $documentPath,
        ]);

        return redirect()->route('employer_space.conges')
            ->with('success', 'Demande modifiée avec succès !');
    }

    public function deleteConge(Conge $conge)
    {
        $employer = auth('employer')->user();

        if ($conge->employer_id !== $employer->id || !in_array($conge->status, ['En attente', 'en attente', 'en_attente'])) {
            return redirect()->route('employer_space.conges')
                ->with('error', 'Suppression impossible.');
        }

        if ($conge->document) {
            Storage::disk('public')->delete($conge->document);
        }

        $conge->delete();

        return redirect()->route('employer_space.conges')
            ->with('success', 'Demande annulée avec succès.');
    }
}