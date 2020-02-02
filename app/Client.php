<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    //
    protected $fillable = [
        'id', 'name', 'phone', 'address', 'reference', 'active'
    ];

}
