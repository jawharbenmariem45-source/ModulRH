<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\ResetCodePassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployerAuthController extends Controller
{
    public function showLogin()
    {
        return view('employer_auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('employer')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('employer_space.dashboard');
        }

        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('employer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showDefinePassword($email)
    {
        return view('auth.validate-account-employer', compact('email'));
    }

    public function submitDefinePassword(Request $request, $email)
    {
        $request->validate([
            'code'             => 'required',
            'password'         => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        // Vérifier le code
        $resetCode = ResetCodePassword::where('email', $email)
            ->where('code', $request->code)
            ->first();

        if (!$resetCode) {
            return back()->with('error_message', 'Code invalide.');
        }

        // Mettre à jour le mot de passe
        $employer = Employer::where('email', $email)->first();

        if (!$employer) {
            return back()->with('error_message', 'Employé introuvable.');
        }

        $employer->password         = Hash::make($request->password);
        
        $employer->save();

        // Supprimer le code
        ResetCodePassword::where('email', $email)->delete();

        return redirect()->route('login')
            ->with('success_message', 'Mot de passe défini avec succès. Connectez-vous !');
    }
}