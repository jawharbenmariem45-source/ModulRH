<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;

class ContractTypeController extends Controller
{
    public function index()
    {
        $contracts = Contract::all();
        return view('admins.contrats.index', compact('contracts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:contracts,name',
            'details'       => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1',
        ]);

        Contract::create([
            'name'          => $request->name,
            'details'       => $request->details,
            'duration_days' => $request->duration_days,
            'active'        => true,
        ]);

        return redirect()->route('contracts.index')
            ->with('success_message', 'Type de contrat ajouté !');
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:contracts,name,' . $contract->id,
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

    public function destroy(Contract $contract)
    {
        $contract->delete();
        return redirect()->route('contracts.index')
            ->with('success_message', 'Type de contrat supprimé !');
    }

    public function toggle(Contract $contract)
    {
        $newValue = !$contract->active;
        $contract->update(['active' => $newValue]);
        $message = $newValue ? 'Contrat activé !' : 'Contrat désactivé !';
        return redirect()->route('contracts.index')
            ->with('success_message', $message);
    }
}