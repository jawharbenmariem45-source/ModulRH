<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
{
    $credentials = $request->only(['email', 'password']);

    // Vérifier si c'est un employer
    $isEmployer = \App\Models\Employer::where('email', $credentials['email'])->exists();

    if (!$isEmployer) {
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            return match(true) {
                $user->hasRole('admin')   => redirect()->route('dashboard'),
                $user->hasRole('rh')      => redirect()->route('dashboard'),
                $user->hasRole('manager') => redirect()->route('dashboard'),
                default => redirect()->route('login')->withErrors(['email' => 'Rôle non reconnu.']),
            };
        }
    }

    // Guard employer
    if (Auth::guard('employer')->attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('employer_space.dashboard');
    }

    return back()->withErrors([
        'email' => 'Les identifiants ne correspondent pas.',
    ])->withInput();
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
