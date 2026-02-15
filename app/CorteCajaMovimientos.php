<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorteCajaMovimientos extends Model
{
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
