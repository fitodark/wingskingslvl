<?php

namespace App\Http\Middleware;

use Closure;

class CheckAgenteToken
{
    /**
     * Autoriza al agente de WhatsApp con un token fijo en el encabezado X-Agente-Token.
     * El token se define en .env como AGENTE_TOKEN; si está vacío se rechaza todo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $esperado = (string) config('services.agente.token');
        $recibido = $request->header('X-Agente-Token');

        if ($esperado === '' || !is_string($recibido) || !hash_equals($esperado, $recibido)) {
            return response()->json(['success' => false, 'error' => 'No autorizado'], 401);
        }
        return $next($request);
    }
}
