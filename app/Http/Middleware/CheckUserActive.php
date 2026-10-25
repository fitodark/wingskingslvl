<?php

namespace App\Http\Middleware;

use Closure;

class CheckUserActive
{
    /**
     * Cierra la sesión de un usuario desactivado y lo regresa al login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() && !$request->user()->status) {
            auth()->logout();
            $request->session()->invalidate();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu usuario está inactivo. Contacta al administrador.']);
        }
        return $next($request);
    }
}
