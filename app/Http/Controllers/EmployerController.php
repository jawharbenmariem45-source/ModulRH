<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Departement;
use App\Models\ResetCodePassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SendEmailToAdminAfterRegistrationNotification;
use Exception;

class EmployerController extends Controller
{
    public function index(Request $request)
{
    $user         = auth()->user();
    $departements = Departement::all();
    $query        = Employer::with('departement');

    // ✅ RH voit tous les employés sans restriction company
    if ($request->filled('searchorders')) {
        $search = $request->searchorders;
        $query->where(function ($q) use ($search) {
            $q->where('nom', 'like', "%$search%")
              ->orWhere('prenom', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%");
        });
    }

    if ($request->filled('departement')) {
        $query->where('department_id', $request->departement);
    }

    $employers = $query->paginate(10)->withQueryString();
    $contracts = \App\Models\Contract::where('active', true)->get();

    return view('employers.index', compact('employers', 'departements', 'contracts'));
}

    public function create()
    {
        $departements = Departement::all();
        $contracts    = \App\Models\Contract::where('active', true)->get();
        return view('employers.create', compact('departements', 'contracts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id'            => 'required|exists:departements,id',
            'nom'                      => 'required|string|max:255',
            'prenom'                   => 'required|string|max:255',
            'email'                    => 'required|email|unique:employers,email',
            'numero_telephone'         => 'required|digits:8',
            'type_contrat'             => 'required',
            'date_debut'               => 'required|date',
            'date_fin'                 => $request->type_contrat === 'CDI' ? 'nullable' : 'nullable|date|after:date_debut',
            'rib'                      => 'nullable|string|max:23',
            'rib_image'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cnss'                     => 'nullable|digits:10',
            'salaire'                  => 'nullable|numeric|min:0',
            'chef_famille'             => 'nullable|boolean',
            'nombre_enfants'           => 'nullable|integer|min:0|max:4',
            'nombre_enfants_infirmes'  => 'nullable|integer|min:0',
            'nombre_enfants_etudiants' => 'nullable|integer|min:0',
        ]);

        try {
            $companyId = \App\Models\User::find(auth()->id())->company_id;

            $ribImagePath = null;
            if ($request->hasFile('rib_image')) {
                $ribImagePath = $request->file('rib_image')->store('ribs', 'public');
            }

            // ✅ Le boot() created() s'occupe automatiquement de l'attach dans employer_contract
            $employer = Employer::create([
                'department_id'            => $request->department_id,
                'company_id'               => $companyId,
                'nom'                      => $request->nom,
                'prenom'                   => $request->prenom,
                'email'                    => $request->email,
                'password'                 => Hash::make(\Illuminate\Support\Str::random(16)),
                'numero_telephone'         => $request->numero_telephone,
                'type_contrat'             => $request->type_contrat,
                'date_debut'               => $request->date_debut,
                'date_fin'                 => $request->date_fin,
                'rib'                      => $request->rib,
                'rib_image'                => $ribImagePath,
                'cnss'                     => $request->cnss,
                'salaire'                  => $request->salaire,
                'chef_famille'             => $request->boolean('chef_famille'),
                'nombre_enfants'           => $request->nombre_enfants ?? 0,
                'nombre_enfants_infirmes'  => $request->nombre_enfants_infirmes ?? 0,
                'nombre_enfants_etudiants' => $request->nombre_enfants_etudiants ?? 0,
            ]);

            $code = rand(1000, 9000);
            ResetCodePassword::updateOrCreate(['email' => $employer->email], ['code' => $code]);

            Notification::route('mail', $employer->email)
                ->notify(new SendEmailToAdminAfterRegistrationNotification($code, $employer->email));

            return redirect()->route('employer.index')
                ->with('success_message', 'Employé ajouté avec succès !');

        } catch (Exception $e) {
            return back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function edit(Employer $employer)
    {
        $departements = Departement::all();
        $contracts    = \App\Models\Contract::where('active', true)->get();
        return view('employers.edit', compact('employer', 'departements', 'contracts'));
    }

    public function update(Request $request, Employer $employer)
    {
        $request->validate([
            'department_id'            => 'required|exists:departements,id',
            'nom'                      => 'required|string|max:255',
            'prenom'                   => 'required|string|max:255',
            'email'                    => 'required|email|unique:employers,email,' . $employer->id,
            'numero_telephone'         => 'required|digits:8',
            'type_contrat'             => 'required',
            'date_debut'               => 'required|date',
            'date_fin'                 => $request->type_contrat === 'CDI' ? 'nullable' : 'nullable|date|after:date_debut',
            'rib'                      => 'nullable|string|max:23',
            'rib_image'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cnss'                     => 'nullable|digits:10',
            'salaire'                  => 'nullable|numeric|min:0',
            'chef_famille'             => 'nullable|boolean',
            'nombre_enfants'           => 'nullable|integer|min:0|max:4',
            'nombre_enfants_infirmes'  => 'nullable|integer|min:0',
            'nombre_enfants_etudiants' => 'nullable|integer|min:0',
        ]);

        try {
            $data = $request->except(['_token', '_method', 'rib_image']);
            $data['chef_famille']             = $request->boolean('chef_famille');
            $data['nombre_enfants']           = $request->nombre_enfants ?? 0;
            $data['nombre_enfants_infirmes']  = $request->nombre_enfants_infirmes ?? 0;
            $data['nombre_enfants_etudiants'] = $request->nombre_enfants_etudiants ?? 0;

            if ($request->hasFile('rib_image')) {
                if ($employer->rib_image) {
                    Storage::disk('public')->delete($employer->rib_image);
                }
                $data['rib_image'] = $request->file('rib_image')->store('ribs', 'public');
            }

            // ✅ Le boot() updated() s'occupe automatiquement du sync dans employer_contract
            $employer->update($data);

            return redirect()->route('employer.index')
                ->with('success_message', 'Mise à jour réussie !');

        } catch (Exception $e) {
            return back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function delete(Employer $employer)
    {
        if ($employer->rib_image) {
            Storage::disk('public')->delete($employer->rib_image);
        }
        $employer->delete();
        return redirect()->route('employer.index')
            ->with('success_message', 'Employé supprimé.');
    }

    public function showContracts()
    {
        $employer = Auth::guard('employer')->user();
        $jours    = null;

        if ($employer->date_fin) {
            $jours = Carbon::today()->diffInDays(Carbon::parse($employer->date_fin), false);
        }

        return view('contrats.contratemploye', compact('employer', 'jours'));
    }

    public function checkContracts()
    {
        $employers = Employer::whereNotNull('date_fin')
            ->whereDate('date_fin', '>=', Carbon::today())
            ->whereDate('date_fin', '<=', Carbon::today()->addDays(30))
            ->get();

        foreach ($employers as $employer) {
            $jours = Carbon::today()->diffInDays(Carbon::parse($employer->date_fin), false);
            Notification::route('mail', $employer->email)
                ->notify(new SendEmailToAdminAfterRegistrationNotification($jours, $employer->email));
        }

        return redirect()->back()
            ->with('success_message', $employers->count() . ' alerte(s) envoyée(s) !');
    }
}