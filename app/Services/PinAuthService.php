<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Hash;

class PinAuthService
{
    public function validarPin($pin, $rol = null)
    {
        // Traer usuarios activos (y opcionalmente por rol)
        $query = User::where('status', true);

        if ($rol) {
            $query->where('rol', $rol);
        }

        $usuarios = $query->get();

        foreach ($usuarios as $usuario) {
            if ($usuario->pin && Hash::check($pin, $usuario->pin)) {
                return $usuario; // ✅ usuario autorizado
            }
        }

        return null; // ❌ PIN inválido
    }
}