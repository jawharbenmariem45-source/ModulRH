<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendEmailToAdminAfterRegistrationNotification;

class CongeController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $companyId = $user->company_id;

        if ($user->hasRole('manager')) {
            $conges = Leave::with(['user.departement'])
                ->whereHas('user', fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'pending')
                ->latest()
                ->paginate(10);
        } else {
            $conges = Leave::with(['user.departement'])
                ->whereHas('user', fn($q) => $q->where('company_id', $companyId))
                ->latest()
                ->paginate(10);
        }

        return view('conges.index', compact('conges'));
    }

    public function create()
    {
        return view('conges.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'       => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:500',
        ]);

        Leave::create([
            'user_id'    => auth()->id(),
            'type'       => $request->type,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'reason'     => $request->reason,
            'status'     => 'pending',
        ]);

        return redirect()->route('conge.index')->with('success', 'Demande envoyée !');
    }

    public function accepter($id)
    {
        $conge = Leave::with('user')->findOrFail($id);
        $user  = $conge->user;

        if (!$user) {
            return back()->with('error', 'Employé introuvable.');
        }

        $joursConge = $conge->days_count ?? 0;

        $congesPris = $user->leaves()
            ->where('status', 'approved')
            ->where('id', '!=', $conge->id)
            ->sum('days_count');

        $joursAccordes = 12;
        $solde         = $joursAccordes - $congesPris;

        if (!in_array($conge->type, ['Maladie', 'Maternité']) && $joursConge > $solde) {
            return back()->with('error',
                "Solde insuffisant : l'employé a {$solde} jour(s) disponible(s) mais demande {$joursConge} jour(s)."
            );
        }

        $conge->update(['status' => 'approved']);

        try {
            Notification::route('mail', $user->email)
                ->notify(new SendEmailToAdminAfterRegistrationNotification(
                    'Votre congé du ' . $conge->start_date . ' au ' . $conge->end_date . ' a été approuvé.',
                    $user->email
                ));
        } catch (\Exception $e) {}

        return back()->with('success', 'Congé approuvé.');
    }

    public function rejeter($id)
    {
        $conge = Leave::with('user')->findOrFail($id);
        $user  = $conge->user;

        $conge->update(['status' => 'rejected']);

        try {
            if ($user) {
                Notification::route('mail', $user->email)
                    ->notify(new SendEmailToAdminAfterRegistrationNotification(
                        'Votre congé du ' . $conge->start_date . ' au ' . $conge->end_date . ' a été refusé.',
                        $user->email
                    ));
            }
        } catch (\Exception $e) {}

        return back()->with('error', 'Congé refusé.');
    }
}