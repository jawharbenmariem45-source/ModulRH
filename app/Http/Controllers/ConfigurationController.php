<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        $company = Company::find(auth()->user()->company_id);
        return view('config.index', compact('company'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'payment_date'  => 'nullable|integer|min:1|max:31',
            'work_schedule' => 'nullable|in:40h,48h',
        ]);

        Company::where('id', auth()->user()->company_id)
            ->update([
                'payment_date'  => $request->input('payment_date'),
                'work_schedule' => $request->input('work_schedule'),
            ]);

        return redirect()->back()->with('success_message', 'Configuration enregistrée !');
    }
}