<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendEmailToAdminAfterRegistrationNotification;

class CongeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('manager')) {
            $conges = Conge::with(['employer.departement'])
                ->where('status', 'En attente')
                ->latest()
                ->paginate(10);
        } else {
            $conges = Conge::with(['employer.departement'])
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

        Conge::create([
            'employer_id' => auth()->id(),
            'type'        => $request->type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'reason'      => $request->reason,
            'status'      => 'En attente',
        ]);

        return redirect()->route('conge.index')->with('success', 'Demande envoyée !');
    }

    public function accepter($id)
    {
        $conge    = Conge::with('employer')->findOrFail($id);
        $employer = $conge->employer;

        if (!$employer) {
            return back()->with('error', 'Employé introuvable.');
        }

        $joursConge = $conge->days_count ?? 0;

        $congesPris = $employer->conges()
            ->where('status', 'Approuvé')
            ->where('id', '!=', $conge->id)
            ->sum('days_count');

        $joursAccordes = 12;
        $solde         = $joursAccordes - $congesPris;

        if (!in_array($conge->type, ['Maladie', 'Maternité']) && $joursConge > $solde) {
            return back()->with('error',
                "Solde insuffisant : l'employé a {$solde} jour(s) disponible(s) mais demande {$joursConge} jour(s)."
            );
        }

        $conge->update(['status' => 'Approuvé']);

        try {
            Notification::route('mail', $employer->email)
                ->notify(new SendEmailToAdminAfterRegistrationNotification(
                    'Votre congé du ' . $conge->start_date . ' au ' . $conge->end_date . ' a été approuvé.',
                    $employer->email
                ));
        } catch (\Exception $e) {}

        return back()->with('success', 'Congé approuvé.');
    }

    public function rejeter($id)
    {
        $conge    = Conge::with('employer')->findOrFail($id);
        $employer = $conge->employer;

        $conge->update(['status' => 'Refusé']);

        try {
            Notification::route('mail', $employer->email)
                ->notify(new SendEmailToAdminAfterRegistrationNotification(
                    'Votre congé du ' . $conge->start_date . ' au ' . $conge->end_date . ' a été refusé.',
                    $employer->email
                ));
        } catch (\Exception $e) {}

        return back()->with('error', 'Congé refusé.');
    }
}