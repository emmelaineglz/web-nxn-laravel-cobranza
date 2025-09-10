<?php
// app/Http/Middleware/IdleTimeout.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdleTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $timeout = config('session.lifetime') * 60; // segundos
        $lastActivity = $request->session()->get('last_activity_time');

        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login', ['expired' => 1, 'mins' => config('session.lifetime')])
                ->withErrors(['message' => 'Tu sesión ha expirado por inactividad.']);
        }

        // Actualiza marca de tiempo en cada request
        $request->session()->put('last_activity_time', time());

        return $next($request);
    }
}
