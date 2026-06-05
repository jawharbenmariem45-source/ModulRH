<?php

namespace App\Http\Controllers;

use App\Models\ResetCodePassword;
use App\Models\User;
use App\Notifications\SendEmailToAdminAfterRegistrationNotification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'rh', 'manager', 'employer']);
        })->orWhereDoesntHave('roles')->paginate(10);

        $permissions = Permission::all();

        return view('admins/index', compact('admins', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,rh,manager',
        ]);

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);

            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            $code = rand(1000, 4000);
            ResetCodePassword::updateOrCreate(['email' => $user->email], ['code' => $code]);

            Notification::route('mail', $user->email)
                ->notify(new SendEmailToAdminAfterRegistrationNotification($code, $user->email));

            return redirect()->route('administrateurs.index')
                ->with('success_message', 'Membre ajouté avec succès');

        } catch (Exception $e) {
            return back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function update(Request $request, User $administrateur)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $administrateur->id,
            'role'  => 'required|in:admin,rh,manager,employer',
        ]);

        try {
            $administrateur->update([
                'name'  => $request->name,
                'email' => $request->email,
            ]);

            $administrateur->syncRoles([$request->role]);

            $administrateur->syncPermissions($request->permissions ?? []);

            return redirect()->route('administrateurs.index')
                ->with('success_message', 'Membre mis à jour avec succès');

        } catch (Exception $e) {
            return back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function delete(User $user)
    {
        try {
            if (Auth::id() === $user->id) {
                return redirect()->back()->with('error_message', 'Vous ne pouvez pas supprimer votre propre compte');
            }

            $user->delete();
            return redirect()->back()->with('success_message', 'Membre supprimé avec succès');

        } catch (Exception $e) {
            return redirect()->back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }

    public function defineAccess($email)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            return view('auth.validate-account', compact('email'));
        }

        return redirect()->route('login');
    }

    public function submitDefineAccess(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $user->password          = Hash::make($request->password);
                $user->email_verified_at = Carbon::now();
                $user->save();

                ResetCodePassword::where('email', $user->email)->delete();

                return redirect()->route('login')
                    ->with('success_message', 'Vos accès ont été correctement définis');
            }
        } catch (Exception $e) {
            return back()->with('error_message', 'Erreur : ' . $e->getMessage());
        }
    }
}