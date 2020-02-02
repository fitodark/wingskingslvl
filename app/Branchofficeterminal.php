<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branchofficeterminal extends Model
{
    //
    protected $fillable = [
        'id', 'name', 'active', 'branchoffice_id'
    ];

    public function branchoffice()
    {
       return $this->hasOne('App\Branchoffice', 'id', 'branchoffice_id');
    }
}
