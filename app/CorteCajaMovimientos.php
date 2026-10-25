<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorteCajaMovimientos extends Model
{
    // corte_caja_movimientos.idTipo
    const TIPO_VENTA = 1;
    const TIPO_COMPRA = 2;
    const TIPO_COMPRA_LIBRE = 3;

    const TIPOS = [
        self::TIPO_VENTA => 'Venta',
        self::TIPO_COMPRA => 'Compra',
        self::TIPO_COMPRA_LIBRE => 'Compra libre',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'corte_caja_movimientos';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idCorteMovimiento';

    protected $fillable = [
        'idCorte',
        'idVenta',
        'idCompra',
        'descripcion',
        'monto',
        'idTipo'
    ];

    public function compra()
    {
       return $this->hasOne('App\Compras', 'idCompra', 'idCompra');
    }

    public function venta()
    {
       return $this->hasOne('App\Venta', 'ventaId', 'idVenta');
    }
}
