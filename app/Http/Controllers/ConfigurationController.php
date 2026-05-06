<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        if (!$companyId) {
            return redirect()->route('dashboard')
                ->with('error_message', 'Vous n\'êtes pas lié à une entreprise.');
        }

        $configs = [
            'PAYMENT_DATEE'  => Configuration::where('company_id', $companyId)->where('type', 'PAYMENT_DATEE')->value('value') ?? '',
            'REGIME_HORAIRE' => Configuration::where('company_id', $companyId)->where('type', 'REGIME_HORAIRE')->value('value') ?? '40h',
        ];

        return view('config.index', compact('configs'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'PAYMENT_DATEE'  => 'nullable|integer|min:1|max:31',
            'REGIME_HORAIRE' => 'nullable|in:40h,48h',
        ]);

        $companyId = auth()->user()->company_id;

        if (!$companyId) {
            return redirect()->back()->with('error_message', 'Pas de company');
        }

        $allowed = ['PAYMENT_DATEE', 'REGIME_HORAIRE'];

        foreach ($allowed as $type) {
            if ($request->has($type)) {
                Configuration::updateOrCreate(
                    ['company_id' => $companyId, 'type' => $type],
                    ['value' => $request->input($type)]
                );
            }
        }

        return redirect()->back()->with('success_message', 'Configuration enregistrée !');
    }
}