<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Departement;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $query     = User::role('employer')
                         ->with('departement')
                         ->where('company_id', $companyId);

        if ($request->filled('type_contrat')) {
            $query->where('contract_type', $request->type_contrat);
        }
        if ($request->filled('department_id')) {
            $query->where('departement_id', $request->department_id);
        }
        if ($request->filled('date_debut')) {
            $query->where('start_date', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('end_date', '<=', $request->date_fin);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $mots   = array_filter(explode(' ', $search));
            $query->where(function ($q) use ($search, $mots) {
                $q->where('last_name', 'like', "%$search%")
                  ->orWhere('first_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
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

        $contrats     = $query->paginate(10)->withQueryString();
        $departements = Departement::all();
        $employers    = User::role('employer')
                            ->where('company_id', $companyId)
                            ->orderBy('last_name')
                            ->get();

        // Alertes séparées — pas de filter() sur le paginator
        $alertes = User::role('employer')
            ->where('company_id', $companyId)
            ->whereNotNull('end_date')
            ->whereRaw('end_date >= ?', [Carbon::today()])
            ->whereRaw('end_date <= ?', [Carbon::today()->addDays(7)])
            ->get();

        return view('contrats.index', compact('contrats', 'departements', 'alertes', 'employers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employer_id'  => 'required|exists:users,id',
            'type_contrat' => 'required|in:CDI,CDD,CIVP,Karama',
            'date_debut'   => 'required|date',
            'date_fin'     => 'nullable|date|after:date_debut',
        ]);

        User::find($request->employer_id)->update([
            'contract_type' => $request->type_contrat,
            'start_date'    => $request->date_debut,
            'end_date'      => $request->date_fin,
        ]);

        return redirect()->route('contrat.index')
            ->with('success', 'Contrat ajouté avec succès !');
    }

    public function update(Request $request, User $contrat)
    {
        $request->validate([
            'type_contrat' => 'required|in:CDI,CDD,CIVP,Karama',
            'rib'          => 'nullable',
            'cnss'         => 'nullable|digits:10',
            'date_debut'   => 'required|date',
            'date_fin'     => 'nullable|date|after:date_debut',
        ]);

        $contrat->update([
            'contract_type' => $request->type_contrat,
            'rib'           => $request->rib,
            'cnss'          => $request->cnss,
            'start_date'    => $request->date_debut,
            'end_date'      => $request->date_fin,
        ]);

        return redirect()->route('contrat.index')
            ->with('success', 'Contrat mis à jour avec succès !');
    }

    public function delete(User $contrat)
    {
        $contrat->update([
            'contract_type' => null,
            'rib'           => null,
            'cnss'          => null,
            'start_date'    => null,
            'end_date'      => null,
        ]);

        return redirect()->route('contrat.index')
            ->with('success', 'Contrat supprimé avec succès !');
    }

    public function downloadPdf(User $contrat)
    {
        $employer = $contrat;
        $pdf      = Pdf::loadView('contrats.pdf', compact('employer'));
        return $pdf->download('contrat-' . $contrat->last_name . '-' . $contrat->first_name . '.pdf');
    }
}