<?php

namespace App\Http\Controllers;

use App\Models\ContractType;
use Illuminate\Http\Request;

class ContractTypeController extends Controller
{
    public function index()
    {
        $contracts = ContractType::all();
        return view('admins.contrats.index', compact('contracts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:contract_types,name',
            'details'       => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1',
        ]);

        ContractType::create([
            'name'          => $request->name,
            'details'       => $request->details,
            'duration_days' => $request->duration_days,
            'active'        => true,
        ]);

        return redirect()->route('contracts.index')
            ->with('success_message', 'Type de contrat ajouté !');
    }

    public function update(Request $request, ContractType $contract)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:contract_types,name,' . $contract->id,
            'details'       => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1',
        ]);

        $contract->update([
            'name'          => $request->name,
            'details'       => $request->details,
            'duration_days' => $request->duration_days,
        ]);

        return redirect()->route('contracts.index')
            ->with('success_message', 'Type de contrat mis à jour !');
    }

    public function destroy(ContractType $contract)
    {
        $contract->delete();
        return redirect()->route('contracts.index')
            ->with('success_message', 'Type de contrat supprimé !');
    }

    public function toggle(ContractType $contract)
    {
        $contract->update(['active' => !$contract->active]);
        $message = $contract->active ? 'Contrat activé !' : 'Contrat désactivé !';
        return redirect()->route('contracts.index')
            ->with('success_message', $message);
    }
}