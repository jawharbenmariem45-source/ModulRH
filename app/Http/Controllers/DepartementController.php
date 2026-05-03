<?php

namespace App\Http\Controllers;

use App\Http\Requests\saveDepartementRequest;
use App\Models\Departement;
use Exception;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

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
        //
    }


    //Interractionavec la bdd
    public function store(saveDepartementRequest $request)
    {
        try {
            $departement = new Departement();
            $departement->name = $request->name;
            $departement->save();

            return redirect()
                ->route('departement.index')
                ->with('success_message', 'Departement enregistre');
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
    public function update(Departement $departement, saveDepartementRequest $request)
    {
     //
    }

    public function destroy(Departement $departement)
    {
        try {
            $departement->delete();
            return redirect()
                ->route('departement.index')
                ->with('success_message', 'Departement supprime');
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
