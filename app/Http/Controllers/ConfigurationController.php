<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $config    = Configuration::where('company_id', $companyId)->first();

        return view('config.index', compact('config'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'PAYMENT_DATEE'  => 'nullable|integer|min:1|max:31',
            'REGIME_HORAIRE' => 'nullable|in:40h,48h',
        ]);

        $companyId = auth()->user()->company_id;

        Configuration::updateOrCreate(
            ['company_id' => $companyId],
            [
                'payment_date'   => $request->input('PAYMENT_DATEE'),
                'regime_horaire' => $request->input('REGIME_HORAIRE'),
            ]
        );

        return redirect()->back()->with('success_message', 'Configuration enregistrée !');
    }
}