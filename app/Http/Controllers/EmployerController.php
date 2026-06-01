<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Departement;
use App\Models\Contract;
use App\Models\Post;
use App\Models\Schedule;
use App\Models\ResetCodePassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $query        = User::role('employer')->with('departement')
                            ->where('company_id', auth()->user()->company_id);

        if ($request->filled('searchorders')) {
            $search = trim($request->searchorders);
            $mots   = array_filter(explode(' ', $search));

            $query->where(function ($q) use ($search, $mots) {
                $q->where('email', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('first_name', 'like', "%$search%");

                if (count($mots) >= 2) {
                    $q->orWhere(function ($sub) use ($mots) {
                        foreach ($mots as $mot) {
                            $sub->where(function ($inner) use ($mot) {
                                $inner->where('last_name', 'like', "%$mot%")
                                      ->orWhere('first_name', 'like', "%$mot%");
                            });
                        }
                    });
                }
            });
        }

        if ($request->filled('departement')) {
            $query->where('department_id', $request->departement);
        }

        $employers = $query->paginate(10)->withQueryString();
        $contracts = Contract::where('active', true)->get();
        $posts     = Post::all();
        $schedules = Schedule::all();

        return view('employers.index', compact('employers', 'departements', 'contracts', 'posts', 'schedules'));
    }

    public function create()
    {
        $departements = Departement::all();
        $contracts    = Contract::where('active', true)->get();
        $posts        = Post::all();
        $schedules    = Schedule::all();
        return view('employers.create', compact('departements', 'contracts', 'posts', 'schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id'           => 'required|exists:departements,id',
            'post_id'                 => 'required|exists:posts,id',
            'schedule_id'             => 'required|exists:schedules,id',
            'last_name'               => 'required|string|max:255',
            'first_name'              => 'required|string|max:255',
            'email'                   => 'required|email|unique:users,email',
            'phone'                   => 'required|digits:8',
            'gender'                  => 'required|in:Homme,Femme',
            'contract_type'           => 'required',
            'salary'                  => 'required|numeric|min:1',
            'start_date'              => 'required|date',
            'end_date'                => $request->contract_type === 'CDI' ? 'nullable' : 'required|date|after:start_date',
            'rib'                     => 'nullable|string|max:23',
            'rib_image'               => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cnss'                    => 'nullable|digits:10',
            'family_head'             => 'nullable|boolean',
            'children_count'          => 'nullable|integer|min:0|max:4',
            'disabled_children_count' => 'nullable|integer|min:0',
            'student_children_count'  => 'nullable|integer|min:0',
        ]);

        try {
            $companyId    = auth()->user()->company_id;
            $ribImagePath = null;

            if ($request->hasFile('rib_image')) {
                $ribImagePath = $request->file('rib_image')->store('ribs', 'public');
            }

            $user = User::create([
                'name'                    => $request->first_name . ' ' . $request->last_name,
                'company_id'              => $companyId,
                'department_id'           => $request->department_id,
                'post_id'                 => $request->post_id,
                'schedule_id'             => $request->schedule_id,
                'last_name'               => $request->last_name,
                'first_name'              => $request->first_name,
                'email'                   => $request->email,
                'password'                => Hash::make(\Illuminate\Support\Str::random(16)),
                'phone'                   => $request->phone,
                'gender'                  => $request->gender,
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

            $user->syncRoles(['employer']);

            $code = rand(1000, 9000);
            ResetCodePassword::updateOrCreate(['email' => $user->email], ['code' => $code]);

            Notification::route('mail', $user->email)
                ->notify(new SendEmailToAdminAfterRegistrationNotification($code, $user->email));

            return redirect()->route('employer.index')
                ->with('success_message', 'Employé ajouté avec succès !');

        } catch (Exception $e) {
            return back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $departements = Departement::all();
        $contracts    = Contract::where('active', true)->get();
        $posts        = Post::all();
        $schedules    = Schedule::all();
        $employer     = $user;
        return view('employers.edit', compact('employer', 'departements', 'contracts', 'posts', 'schedules'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'department_id'           => 'required|exists:departements,id',
            'post_id'                 => 'required|exists:posts,id',
            'schedule_id'             => 'required|exists:schedules,id',
            'last_name'               => 'required|string|max:255',
            'first_name'              => 'required|string|max:255',
            'email'                   => 'required|email|unique:users,email,' . $user->id,
            'phone'                   => 'required|digits:8',
            'gender'                  => 'required|in:Homme,Femme',
            'contract_type'           => 'required',
            'salary'                  => 'required|numeric|min:1',
            'start_date'              => 'required|date',
            'end_date'                => $request->contract_type === 'CDI' ? 'nullable' : 'required|date|after:start_date',
            'rib'                     => 'nullable|string|max:23',
            'rib_image'               => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'cnss'                    => 'nullable|digits:10',
            'family_head'             => 'nullable|boolean',
            'children_count'          => 'nullable|integer|min:0|max:4',
            'disabled_children_count' => 'nullable|integer|min:0',
            'student_children_count'  => 'nullable|integer|min:0',
        ]);

        try {
            $data = [
                'name'                    => $request->first_name . ' ' . $request->last_name,
                'department_id'           => $request->department_id,
                'post_id'                 => $request->post_id,
                'schedule_id'             => $request->schedule_id,
                'last_name'               => $request->last_name,
                'first_name'              => $request->first_name,
                'email'                   => $request->email,
                'phone'                   => $request->phone,
                'gender'                  => $request->gender,
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
                if ($user->rib_image) Storage::disk('public')->delete($user->rib_image);
                $data['rib_image'] = $request->file('rib_image')->store('ribs', 'public');
            }

            $user->update($data);

            return redirect()->route('employer.index')
                ->with('success_message', 'Mise à jour réussie !');

        } catch (Exception $e) {
            return back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function delete(User $user)
    {
        if ($user->rib_image) Storage::disk('public')->delete($user->rib_image);

        $user->payments()->delete();
        $user->conges()->delete();
        $user->attendances()->delete();
        $user->contracts()->detach();
        $user->delete();

        return redirect()->route('employer.index')
            ->with('success_message', 'Employé supprimé.');
    }

    public function checkContracts()
    {
        $users = User::role('employer')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereDate('end_date', '<=', Carbon::today()->addDays(30))
            ->get();

        foreach ($users as $user) {
            try {
                $jours = Carbon::today()->diffInDays(Carbon::parse($user->end_date), false);
                Notification::route('mail', $user->email)
                    ->notify(new SendEmailToAdminAfterRegistrationNotification($jours, $user->email));
            } catch (\Exception $e) {}
        }

        return redirect()->back()
            ->with('success_message', $users->count() . ' alerte(s) envoyée(s) !');
    }
}