<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Permite el paso solo si el usuario tiene alguno de los roles indicados.
     * Uso en rutas: ->middleware('role:admin,encargado')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!$request->user() || !$request->user()->hasAnyRole($roles)) {
            abort(403, 'No tienes permiso para entrar a esta sección.');
        }
        return $next($request);
    }
}
