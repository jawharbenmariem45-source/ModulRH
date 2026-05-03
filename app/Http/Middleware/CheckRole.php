<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Utilise Spatie hasRole()
        if (!Auth::user()->hasRole($roles)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}