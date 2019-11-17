<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ventas';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ventaId';

    // protected $productosTemp = [];

    protected $fillable = [
      'IdUsuario',
      'branchofficeterminal_id',
      'dinerstable_id',
      'client_id',
      'montoTotal',
      'montoSubtotal',
      'montoIva',
      'cantidadRecibida',
      'cantidadProductos',
      'type',
      'estatus',
      'activo',
      'order'
    ];

    public function ventasProductos()
    {
        return $this->hasMany('App\VentasProductos', 'IdVenta', 'ventaId');
    }

    public function terminal()
    {
       return $this->hasOne('App\Branchofficeterminal', 'id', 'branchofficeterminal_id');
    }

    public function usuario()
    {
       return $this->hasOne('App\User', 'id', 'IdUsuario');
    }

    public function dinerstable()
    {
       return $this->hasOne('App\Dinerstable', 'id', 'dinerstable_id');
    }

    public function client()
    {
       return $this->hasOne('App\Client', 'id', 'client_id');
    }
}
