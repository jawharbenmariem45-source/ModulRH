<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendEmailToAdminAfterRegistrationNotification;

class CongeController extends Controller
{
    /**
     * Afficher la liste des congés filtrée selon le rôle de l'utilisateur.
     */
    public function index()
    {
        $user      = auth()->user();
        $companyId = $user->company_id;

        // Requête de base limitée à l'entreprise de l'utilisateur connecté
        $query = Leave::with(['user.departement'])
            ->whereHas('user', fn($q) => $q->where('company_id', $companyId));

        // Filtrage strict : si c'est un manager (et qu'il n'a pas de droits globaux RH/Admin/Owner)
        if ($user->hasRole('manager') && !$user->hasAnyRole(['rh', 'admin', 'owner'])) {
            $departementId = $user->departement_id;

            // Il ne voit QUE les demandes "en attente" des employés de SON département
            if ($departementId) {
                $query->where('status', 'pending')
                      ->whereHas('user', fn($q) => $q->where('departement_id', $departementId));
            } else {
                // Sécurité : Si le manager n'a exceptionnellement aucun département assigné, il ne voit rien
                $query->whereRaw('1 = 0');
            }
        }

        // Récupération des données filtrées avec pagination
        $conges = $query->latest()->paginate(10);

        return view('conges.index', compact('conges'));
    }

    /**
     * Afficher le formulaire de demande de congé.
     */
    public function create()
    {
        return view('conges.create');
    }

    /**
     * Enregistrer une nouvelle demande de congé.
     */
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

    /**
     * Approuver une demande de congé avec vérification des droits et du solde.
     */
    public function accepter($id)
    {
        $conge       = Leave::with('user')->findOrFail($id);
        $user        = $conge->user;
        $currentUser = auth()->user();

        if (!$user) {
            return back()->with('error', 'Employé introuvable.');
        }

        // Sécurité : Un manager ne peut pas approuver le congé d'un employé d'un autre département
        if ($currentUser->hasRole('manager') && !$currentUser->hasAnyRole(['rh', 'admin', 'owner'])) {
            if ($user->departement_id !== $currentUser->departement_id) {
                abort(403, 'Action non autorisée pour ce département.');
            }
        }

        $joursConge = $conge->days_count ?? 0;

        // Calcul du solde consommé (hors demande actuelle)
        $congesPris = $user->leaves()
            ->where('status', 'approved')
            ->where('id', '!=', $conge->id)
            ->sum('days_count');

        $joursAccordes = 12; // Base standard ou selon vos paramètres
        $solde         = $joursAccordes - $congesPris;

        // Vérification du solde pour les congés soumis à restriction (ex: annuel, sans solde...)
        if (!in_array($conge->type, ['Maladie', 'Maternité']) && $joursConge > $solde) {
            return back()->with('error',
                "Solde insuffisant : l'employé a {$solde} jour(s) disponible(s) mais en demande {$joursConge}."
            );
        }

        $conge->update(['status' => 'approved']);

        // Envoi de la notification par email
        try {
            Notification::route('mail', $user->email)
                ->notify(new SendEmailToAdminAfterRegistrationNotification(
                    'Votre congé du ' . $conge->start_date . ' au ' . $conge->end_date . ' a été approuvé.',
                    $user->email
                ));
        } catch (\Exception $e) {}

        return back()->with('success', 'Congé approuvé avec succès.');
    }

    /**
     * Refuser une demande de congé avec vérification des droits.
     */
    public function rejeter($id)
    {
        $conge       = Leave::with('user')->findOrFail($id);
        $user        = $conge->user;
        $currentUser = auth()->user();

        // Sécurité : Un manager ne peut pas refuser le congé d'un employé d'un autre département
        if ($user && $currentUser->hasRole('manager') && !$currentUser->hasAnyRole(['rh', 'admin', 'owner'])) {
            if ($user->departement_id !== $currentUser->departement_id) {
                abort(403, 'Action non autorisée pour ce département.');
            }
        }

        $conge->update(['status' => 'rejected']);

        // Envoi de la notification par email
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