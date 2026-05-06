<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employer;
use App\Models\Departement;
use App\Models\Contract;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ContratController extends Controller
{
    public function index(Request $request)
{
    $user  = auth()->user();
    $query = Employer::with('departement');

    if ($request->filled('type_contrat')) {
        $query->where('type_contrat', $request->type_contrat);
    }
    if ($request->filled('department_id')) {
        $query->where('department_id', $request->department_id);
    }
    if ($request->filled('date_debut')) {
        $query->where('date_debut', '>=', $request->date_debut);
    }
    if ($request->filled('date_fin')) {
        $query->where('date_fin', '<=', $request->date_fin);
    }
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('nom', 'like', '%' . $request->search . '%')
              ->orWhere('prenom', 'like', '%' . $request->search . '%');
        });
    }

    $contrats     = $query->get();
    $departements = Departement::all();
    $employers    = Employer::orderBy('nom')->get();

    $alertes = Employer::whereNotNull('date_fin')
        ->get()
        ->filter(function($e) {
            try {
                $fin = Carbon::parse($e->date_fin);
                return $fin->gte(Carbon::today()) && $fin->lte(Carbon::today()->addDays(7));
            } catch (\Exception $e) {
                return false;
            }
        });

    return view('contrats.index', compact('contrats', 'departements', 'alertes', 'employers'));
}

    public function store(Request $request)
    {
        $request->validate([
            'employer_id'  => 'required|exists:employers,id',
            'type_contrat' => 'required|in:CDI,CDD,CIVP,Karama',
            'date_debut'   => 'required|date',
            'date_fin'     => 'nullable|date|after:date_debut',
        ]);

        $employer = Employer::find($request->employer_id);

        // ✅ Le boot() updated() gère automatiquement le sync dans employer_contract
        $employer->update([
            'type_contrat' => $request->type_contrat,
            'date_debut'   => $request->date_debut,
            'date_fin'     => $request->date_fin,
        ]);

        return redirect()->route('contrat.index')->with('success', 'Contrat ajouté avec succès !');
    }

    public function edit(Employer $employer)
    {
        $departements = Departement::all();
        return view('contrats.edit', compact('employer', 'departements'));
    }

    public function update(Request $request, Employer $employer)
    {
        $request->validate([
            'type_contrat' => 'required|in:CDI,CDD,CIVP,Karama',
            'rib'          => 'nullable',
            'cnss'         => 'nullable|digits:10',
            'date_debut'   => 'required|date',
            'date_fin'     => 'nullable|date|after:date_debut',
        ]);

        // ✅ Le boot() updated() gère automatiquement le sync dans employer_contract
        $employer->update($request->only([
            'type_contrat', 'rib', 'cnss', 'date_debut', 'date_fin',
        ]));

        return redirect()->route('contrat.index')->with('success', 'Contrat mis à jour avec succès !');
    }

    public function delete(Employer $employer)
    {
        $employer->update([
            'type_contrat' => null,
            'rib'          => null,
            'cnss'         => null,
            'date_debut'   => null,
            'date_fin'     => null,
        ]);

        return redirect()->route('contrat.index')->with('success', 'Contrat supprimé avec succès !');
    }

    public function downloadPdf(Employer $employer)
    {
        $pdf = Pdf::loadView('contrats.pdf', compact('employer'));
        return $pdf->download('contrat-' . $employer->nom . '-' . $employer->prenom . '.pdf');
    }
}