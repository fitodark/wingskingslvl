<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromotionsClients extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'promotions_clients';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    // protected $productosTemp = [];

    protected $fillable = [
        'IdVenta',
        'client_id',
        'porcentaje',
        'cantidadVentas',
        'montoDescuento',
        'estatus'
    ];

    public function client()
    {
       return $this->hasOne('App\Client', 'id', 'client_id');
    }

    public function venta()
    {
       return $this->hasOne('App\Venta', 'id', 'IdVenta');
    }
}
