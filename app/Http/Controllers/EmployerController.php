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
        $departements = Departement::all();
        $query        = Employer::with('departement');

        if ($request->filled('searchorders')) {
            $search = $request->searchorders;
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'like', "%$search%")
                  ->orWhere('first_name', 'like', "%$search%")
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
            'department_id'           => 'required|exists:departements,id',
            'last_name'               => 'required|string|max:255',
            'first_name'              => 'required|string|max:255',
            'email'                   => 'required|email|unique:employers,email',
            'phone'                   => 'required|digits:8',
            'contract_type'           => 'required',
            'start_date'              => 'required|date',
            'end_date'                => $request->contract_type === 'CDI' ? 'nullable' : 'nullable|date|after:start_date',
            'rib'                     => 'nullable|string|max:23',
            'rib_image'               => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cnss'                    => 'nullable|digits:10',
            'salary'                  => 'nullable|numeric|min:0',
            'family_head'             => 'nullable|boolean',
            'children_count'          => 'nullable|integer|min:0|max:4',
            'disabled_children_count' => 'nullable|integer|min:0',
            'student_children_count'  => 'nullable|integer|min:0',
        ]);

        try {
            $companyId = \App\Models\User::find(auth()->id())->company_id;

            $ribImagePath = null;
            if ($request->hasFile('rib_image')) {
                $ribImagePath = $request->file('rib_image')->store('ribs', 'public');
            }

            $employer = Employer::create([
                'department_id'           => $request->department_id,
                'company_id'              => $companyId,
                'last_name'               => $request->last_name,
                'first_name'              => $request->first_name,
                'email'                   => $request->email,
                'password'                => Hash::make(\Illuminate\Support\Str::random(16)),
                'phone'                   => $request->phone,
                'contract_type'           => $request->contract_type,
                'start_date'              => $request->start_date,
                'end_date'                => $request->end_date,
                'rib'                     => $request->rib,
                'rib_image'               => $ribImagePath,
                'cnss'                    => $request->cnss,
                'salary'                  => $request->salary,
                'family_head'             => $request->boolean('family_head'),
                'children_count'          => $request->children_count ?? 0,
                'disabled_children_count' => $request->disabled_children_count ?? 0,
                'student_children_count'  => $request->student_children_count ?? 0,
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
            'department_id'           => 'required|exists:departements,id',
            'last_name'               => 'required|string|max:255',
            'first_name'              => 'required|string|max:255',
            'email'                   => 'required|email|unique:employers,email,' . $employer->id,
            'phone'                   => 'required|digits:8',
            'contract_type'           => 'required',
            'start_date'              => 'required|date',
            'end_date'                => $request->contract_type === 'CDI' ? 'nullable' : 'nullable|date|after:start_date',
            'rib'                     => 'nullable|string|max:23',
            'rib_image'               => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cnss'                    => 'nullable|digits:10',
            'salary'                  => 'nullable|numeric|min:0',
            'family_head'             => 'nullable|boolean',
            'children_count'          => 'nullable|integer|min:0|max:4',
            'disabled_children_count' => 'nullable|integer|min:0',
            'student_children_count'  => 'nullable|integer|min:0',
        ]);

        try {
            $data = [
                'department_id'           => $request->department_id,
                'last_name'               => $request->last_name,
                'first_name'              => $request->first_name,
                'email'                   => $request->email,
                'phone'                   => $request->phone,
                'contract_type'           => $request->contract_type,
                'start_date'              => $request->start_date,
                'end_date'                => $request->end_date,
                'rib'                     => $request->rib,
                'cnss'                    => $request->cnss,
                'salary'                  => $request->salary,
                'family_head'             => $request->boolean('family_head'),
                'children_count'          => $request->children_count ?? 0,
                'disabled_children_count' => $request->disabled_children_count ?? 0,
                'student_children_count'  => $request->student_children_count ?? 0,
            ];

            if ($request->hasFile('rib_image')) {
                if ($employer->rib_image) {
                    Storage::disk('public')->delete($employer->rib_image);
                }
                $data['rib_image'] = $request->file('rib_image')->store('ribs', 'public');
            }

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

        $employer->salaires()->delete();
        $employer->payments()->delete();
        $employer->conges()->delete();
        $employer->attendances()->delete();
        $employer->contracts()->detach();
        $employer->delete();

        return redirect()->route('employer.index')
            ->with('success_message', 'Employé supprimé.');
    }

    public function showContracts()
    {
        $employer = Auth::guard('employer')->user();
        $jours    = null;

        if ($employer->end_date) {
            try {
                $jours = Carbon::today()->diffInDays(Carbon::parse($employer->end_date), false);
            } catch (\Exception $e) {}
        }

        return view('contrats.contratemploye', compact('employer', 'jours'));
    }

    public function checkContracts()
    {
        $employers = Employer::whereNotNull('end_date')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereDate('end_date', '<=', Carbon::today()->addDays(30))
            ->get();

        foreach ($employers as $employer) {
            try {
                $jours = Carbon::today()->diffInDays(Carbon::parse($employer->end_date), false);
                Notification::route('mail', $employer->email)
                    ->notify(new SendEmailToAdminAfterRegistrationNotification($jours, $employer->email));
            } catch (\Exception $e) {}
        }

        return redirect()->back()
            ->with('success_message', $employers->count() . ' alerte(s) envoyée(s) !');
    }
}