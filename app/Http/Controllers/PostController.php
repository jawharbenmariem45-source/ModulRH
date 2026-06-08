<?php

namespace App\Http\Controllers;

use App\Models\Poste;
use App\Models\Departement;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $postes       = Poste::with('departement')->paginate(10);
        $departements = Departement::all();
        return view('posts.index', compact('postes', 'departements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'departement_id' => 'required|exists:departements,id',
            'name'           => 'required|string|max:255',
        ]);

        Poste::create($request->only('departement_id', 'name'));

        return redirect()->route('postes.index')
            ->with('success_message', 'Poste ajouté avec succès.');
    }

    public function update(Request $request, Poste $poste)
    {
        $request->validate([
            'departement_id' => 'required|exists:departements,id',
            'name'           => 'required|string|max:255',
        ]);

        $poste->update($request->only('departement_id', 'name'));

        return redirect()->route('postes.index')
            ->with('success_message', 'Poste mis à jour.');
    }

    public function destroy(Poste $poste)
    {
        $poste->delete();
        return redirect()->route('postes.index')
            ->with('success_message', 'Poste supprimé.');
    }
}