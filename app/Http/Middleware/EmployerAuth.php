<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('employer')->check()) {
            return redirect()->route('employer_space.login');
        }
        return $next($request);
    }
}