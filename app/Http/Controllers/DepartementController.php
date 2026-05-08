<?php

namespace App\Http\Controllers;

use App\Http\Requests\saveDepartementRequest;
use App\Models\Departement;
use Exception;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    public function index()
    {
        $departements = Departement::paginate(10);
        return view('departements.index', compact('departements'));
    }

    public function create()
    {
        return view('departements.create');
    }

    public function edit(Departement $departement)
    {
        return view('departements.edit', compact('departement'));
    }

    public function store(saveDepartementRequest $request)
    {
        try {
            $departement       = new Departement();
            $departement->name = $request->name;
            $departement->save();

            return redirect()
                ->route('departement.index')
                ->with('success_message', 'Département enregistré');

        } catch (Exception $e) {
            return redirect()
                ->route('departement.index')
                ->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function update(Departement $departement, saveDepartementRequest $request)
    {
        try {
            $departement->name = $request->name;
            $departement->save();

            return redirect()
                ->route('departement.index')
                ->with('success_message', 'Département mis à jour');

        } catch (Exception $e) {
            return redirect()
                ->route('departement.index')
                ->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy(Departement $departement)
    {
        try {
            foreach ($departement->employers as $employer) {
                $employer->salaires()->delete();
                $employer->payments()->delete();
                $employer->conges()->delete();
                $employer->attendances()->delete();
                $employer->contracts()->detach();
                $employer->delete();
            }

            $departement->delete();

            return redirect()
                ->route('departement.index')
                ->with('success_message', 'Département supprimé');

        } catch (Exception $e) {
            return redirect()
                ->route('departement.index')
                ->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }
}