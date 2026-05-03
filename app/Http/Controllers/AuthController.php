<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        // Déconnecter les sessions précédentes
        Auth::guard('web')->logout();
        Auth::guard('employer')->logout();

        return view('auth.login');
    }

    public function handleLogin(AuthRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        // Déconnecter les sessions précédentes
        Auth::guard('web')->logout();
        Auth::guard('employer')->logout();

        // 1. Tester table users (admin, rh, manager)
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // 2. Tester table employers
        if (Auth::guard('employer')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('employer_space.dashboard');
        }

        return back()->withErrors([
            'email' => 'Les identifiants ne correspondent pas.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('employer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}