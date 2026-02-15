<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComprasProductos extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'compras_productos';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idCompraProducto';

    protected $fillable = [
		'idCompra',
		'idProducto',
		'cantidad',
		'monto'
    ];

    public function product()
    {
       return $this->hasOne('App\Product', 'id', 'idProducto');
    }
}
