<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'detail', 'price', 'type', 'image', 'active',
        'promotion_type',
        'delivery_available'
    ];
}
