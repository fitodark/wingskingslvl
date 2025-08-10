<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VentasProductos extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ventasproductos';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ventasProductosId';

    protected $fillable = [
        'IdProducto',
        'IdVenta',
        'cantidad',
        'montoVenta',
        'descripcion',
        'order'
    ];

    public function product()
    {
       return $this->hasOne('App\Product', 'id', 'IdProducto');
    }
}
