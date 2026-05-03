<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectRole
{
    public function handle(Request $request, Closure $next)
{
    // 1. Vérification pour le Guard Web (Admin, RH, Manager)
    if (Auth::guard('web')->check()) {
        $user = Auth::guard('web')->user();
        if ($user->hasRole('admin')) return redirect()->route('dashboard'); // Ajustez selon vos routes réelles
        if ($user->hasRole('rh')) return redirect()->route('dashboard');
        if ($user->hasRole('manager')) return redirect()->route('dashboard');       
    }

    // 2. Vérification pour le Guard Employer
    if (Auth::guard('employer')->check()) {
        return redirect()->route('employer_space.dashboard');
    }

    return $next($request);
}
}
