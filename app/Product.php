<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // products.type (los nombres están también en configs, key = productsType)
    const TIPO_BARRA = 1;
    const TIPO_ALITAS = 2;
    const TIPO_COCINA = 3;
    const TIPO_INSUMO = 4;

    const TIPOS = [
        self::TIPO_BARRA => 'Barra',
        self::TIPO_ALITAS => 'Cocina (Alitas)',
        self::TIPO_COCINA => 'Cocina (General)',
        self::TIPO_INSUMO => 'Insumo',
    ];

    // Lo que imprime la comanda de cocina
    const TIPOS_COCINA = [self::TIPO_ALITAS, self::TIPO_COCINA];

    protected $fillable = [
        'name', 'detail', 'piezas', 'price', 'type', 'image', 'active',
        'promotion_type',
        'delivery_available'
    ];

    /**
     * Alitas/boneless: la orden lleva sabores y se valida contra piezas.
     */
    public function llevaSabores()
    {
        return $this->type == self::TIPO_ALITAS;
    }

    public function esBebida()
    {
        return $this->type == self::TIPO_BARRA;
    }
}
