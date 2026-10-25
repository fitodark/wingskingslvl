<?php

namespace App\Exceptions;

/**
 * Regla de negocio de una venta que no se cumple (venta cerrada, sabores que no cuadran, etc.).
 * El mensaje ya va listo para mostrarse al usuario; $campo indica a qué se refiere.
 */
class VentaException extends \RuntimeException
{
    const VENTA_CERRADA = 'venta';

    public $campo;

    public function __construct($mensaje, $campo = 'venta')
    {
        parent::__construct($mensaje);
        $this->campo = $campo;
    }

    public static function ventaCerrada()
    {
        return new static('Esta venta ya fue cerrada.', self::VENTA_CERRADA);
    }

    public function esVentaCerrada()
    {
        return $this->campo === self::VENTA_CERRADA;
    }
}
