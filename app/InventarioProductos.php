<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventarioProductos extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventario_productos';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idInventarioProducto';

    protected $fillable = [
        'idProducto',
        'cantidad',
        'cantidadMinima',
        'cantidadMaxima'
    ];

    public function product()
    {
       return $this->hasOne('App\Product', 'id', 'idProducto');
    }

}
