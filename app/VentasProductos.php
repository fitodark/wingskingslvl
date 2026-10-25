<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VentasProductos extends Model
{
    // ventasproductos.estatus (baja lógica)
    const ESTATUS_ELIMINADO = 0;
    const ESTATUS_ACTIVO = 1;

    const ESTATUS = [
        self::ESTATUS_ACTIVO => 'Cobrado',
        self::ESTATUS_ELIMINADO => 'Eliminado',
    ];

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

    public function venta()
    {
       return $this->belongsTo('App\Venta', 'IdVenta', 'ventaId');
    }

    public function userCreate()
    {
       return $this->hasOne('App\User', 'id', 'id_user_create');
    }

    public function userDelete()
    {
       return $this->hasOne('App\User', 'id', 'id_user_delete');
    }

    public function estaActivo()
    {
       return $this->estatus == self::ESTATUS_ACTIVO;
    }

    /**
     * Ya se mandó a cocina/barra (delete_flag = 0). Desde aquí eliminarlo pide PIN.
     */
    public function estaComandado()
    {
       return !$this->delete_flag;
    }

    public function scopeActivos($query)
    {
       return $query->where($this->getTable() . '.estatus', self::ESTATUS_ACTIVO);
    }

    /**
     * Problema con los sabores de una orden de alitas (ej. "Sabores suman 50 de 15 piezas"),
     * o null si está bien o el producto no lleva sabores.
     */
    public function getProblemaSaboresAttribute()
    {
       if (!$this->estaActivo() || !$this->product || !$this->product->llevaSabores()) {
          return null;
       }
       return \App\Support\SaboresOrden::problema($this->descripcion, $this->product->piezas);
    }
}
