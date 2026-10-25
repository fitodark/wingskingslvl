<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Hash;

class PinAuthService
{
    /**
     * Regresa el usuario activo dueño del PIN, o null si no coincide.
     *
     * @param  string  $pin
     * @param  string|array|null  $roles  limita a usuarios con alguno de estos roles
     */
    public function validarPin($pin, $roles = null)
    {
        if (!is_string($pin) || $pin === '') {
            return null;
        }

        $query = User::where('status', true)->whereNotNull('pin');
        if ($roles) {
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', (array) $roles);
            });
        }

        foreach ($query->get() as $usuario) {
            if (Hash::check($pin, $usuario->pin)) {
                return $usuario;
            }
        }
        return null;
    }

    /**
     * Indica si otro usuario ya usa ese PIN. Los PIN se guardan cifrados,
     * así que hay que compararlo contra cada uno.
     *
     * @param  string  $pin
     * @param  int|null  $exceptoUserId  usuario que se está editando
     */
    public function pinEnUso($pin, $exceptoUserId = null)
    {
        $usuarios = User::whereNotNull('pin')
            ->when($exceptoUserId, function ($q) use ($exceptoUserId) {
                $q->where('id', '!=', $exceptoUserId);
            })->get(['id', 'pin']);

        foreach ($usuarios as $usuario) {
            if (Hash::check($pin, $usuario->pin)) {
                return true;
            }
        }
        return false;
    }
}
