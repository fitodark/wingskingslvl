<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Compras extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'compras';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idCompra';

    protected $fillable = [
        'proveedor',
        'monto',
        'observaciones',
        'status'
    ];

    public function comprasProductos()
    {
        return $this->hasMany('App\ComprasProductos', 'idCompra', 'idCompra');
    }

}
