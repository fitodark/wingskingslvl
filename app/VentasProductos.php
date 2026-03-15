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
        'order',
        'estatus',
        'delete_flag',
        'id_user_delete',
        'id_user_create'
    ];

    public function product()
    {
       return $this->hasOne('App\Product', 'id', 'IdProducto');
    }

    public function userCreate()
    {
       return $this->hasOne('App\Product', 'id', 'id_user_create');
    }

    public function userDelete()
    {
       return $this->hasOne('App\Product', 'id', 'id_user_delete');
    }
}
