<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorteCaja extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'corte_caja';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idCorte';

    protected $fillable = [
		'idUsuarioApertura',
		'idUsuarioCierre',
		'fechaApertura',
		'fechaCierre',
		'montoApertura',
		'montoCierre',
		'corteStatus'
    ];

    public function corteCajaMovimientos()
    {
        return $this->hasMany('App\CorteCajaMovimientos', 'idCorte', 'idCorte');
    }

    public function usuarioApertura()
    {
       return $this->hasOne('App\User', 'id', 'idUsuarioApertura');
    }

    public function usuarioCierre()
    {
       return $this->hasOne('App\User', 'id', 'idUsuarioCierre');
    }
}
