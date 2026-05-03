<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CongeController extends Controller
{
    // Affiche la liste
    public function index()
{
    $conges = Conge::all();
    
    // On définit les variables manquantes
    $regimeHoraire = 40; 
    $tauxHeureSupp = 1.25; // Exemple : +25%

    return view('conges.index', compact('conges', 'regimeHoraire', 'tauxHeureSupp'));
}

    // Affiche le formulaire (C'est cette fonction qui manquait !)
    public function create()
    {
        return view('conges.create');
    }

    // Enregistre la demande
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        Conge::create([
            'employer_id' => \Illuminate\Support\Facades\Auth::id(),
            'type' => $request->type,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'motif' => $request->motif,
            'statut' => 'en_attente',
        ]);

        return redirect()->route('conge.index')->with('success', 'Demande envoyée !');
    }

    public function accepter($id)
    {
        $conge = Conge::findOrFail($id);
        $conge->update(['statut' => 'accepte']);
        return back()->with('success', 'Congé accepté.');
    }

    public function rejeter($id)
    {
        $conge = Conge::findOrFail($id);
        $conge->update(['statut' => 'rejete']);
        return back()->with('error', 'Congé refusé.');
    }
}