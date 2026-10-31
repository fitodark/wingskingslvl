<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    // ventas.type
    const TIPO_LOCAL = 1;
    const TIPO_DOMICILIO = 2;
    const TIPO_PARA_LLEVAR = 3;

    const TIPOS = [
        self::TIPO_LOCAL => 'Local',
        self::TIPO_DOMICILIO => 'Domicilio',
        self::TIPO_PARA_LLEVAR => 'Para llevar',
    ];

    // ventas.estatus
    const ESTATUS_ABIERTA = 1;
    const ESTATUS_CERRADA = 2;
    const ESTATUS_CANCELADA = 3;

    const ESTATUS = [
        self::ESTATUS_ABIERTA => 'Abierto',
        self::ESTATUS_CERRADA => 'Finalizado',
        self::ESTATUS_CANCELADA => 'Cancelado',
    ];

    // ventas.tipo_envio (lo llena el agente; null = no es a domicilio o venta del POS)
    const ENVIO_GRATUITO = 1;
    const ENVIO_EXPRESS = 2;

    // ventas.payment_type (null mientras la venta está abierta)
    const PAGO_EFECTIVO = 1;
    const PAGO_TRANSFERENCIA = 2;

    const PAGOS = [
        self::PAGO_EFECTIVO => 'Efectivo',
        self::PAGO_TRANSFERENCIA => 'Transferencia',
    ];

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
      'nombre_cuenta',
      'telefono_recoge',
      'client_id',
      'montoTotal',
      'montoTotalDescuento',
      'montoSubtotal',
      'montoIva',
      'cantidadRecibida',
      'cantidadProductos',
      'type',
      'estatus',
      'activo',
      'order',
      'apply_discount',
      'payment_type',
      'id_user_close',
      'direccion_envio',
      'venta_agente',
      'tipo_envio',
      'monto_envio_express',
      'colonia_envio'
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

   public function userClose()
   {
      return $this->hasOne('App\User', 'id', 'id_user_close');
   }

   /**
    * Mesa y/o nombre de la cuenta, ej. "Mesa 3 - Cuenta de Patricia".
    * Ambos son opcionales, por eso se arma solo con lo que exista.
    */
   public function getEtiquetaCuentaAttribute()
   {
      $partes = array_filter([
         optional($this->dinerstable)->name,
         $this->nombre_cuenta,
      ], 'strlen');

      return implode(' - ', $partes);
   }

   /**
    * Cuántas órdenes de alitas activas tienen sabores que no cuadran
    * (pasa con ventas del agente, que se insertan directo en la base).
    */
   public function getSaboresConProblemaAttribute()
   {
      return $this->ventasProductos->filter(function ($vp) {
         return $vp->problema_sabores !== null;
      })->count();
   }

   public function esLocal()
   {
      return $this->type == self::TIPO_LOCAL;
   }

   public function esDomicilio()
   {
      return $this->type == self::TIPO_DOMICILIO;
   }

   public function esParaLlevar()
   {
      return $this->type == self::TIPO_PARA_LLEVAR;
   }

   public function esEnvioExpress()
   {
      return $this->tipo_envio == self::ENVIO_EXPRESS;
   }

   public function esEnvioGratuito()
   {
      return $this->tipo_envio == self::ENVIO_GRATUITO;
   }

   /**
    * Etiqueta para la bandeja y el paso 1, ej. "Envío express · $ 45.00 · Acatlima".
    * Solo pantalla: el monto express no entra a montoTotal ni a ningún ticket.
    */
   public function getEtiquetaEnvioAttribute()
   {
      if ($this->esEnvioExpress()) {
         $partes = ['Envío express'];
         // Sin monto = express pedido por el cliente en colonia gratuita; se acuerda por teléfono.
         $partes[] = $this->monto_envio_express !== null
            ? '$ ' . number_format($this->monto_envio_express, 2)
            : 'monto por acordar';
         if (!empty($this->colonia_envio)) {
            $partes[] = $this->colonia_envio;
         }
         return implode(' · ', $partes);
      }
      if ($this->esEnvioGratuito()) {
         return 'Envío gratuito';
      }
      return null;
   }

   public function estaAbierta()
   {
      return $this->estatus == self::ESTATUS_ABIERTA;
   }

   public function estaCerrada()
   {
      return $this->estatus == self::ESTATUS_CERRADA;
   }

   /**
    * La ronda actual tiene productos que todavía no se mandan a cocina/barra.
    */
   public function getTieneRondaPendienteAttribute()
   {
      return $this->ventasProductos()
         ->where('order', $this->order)
         ->where('delete_flag', true)
         ->activos()
         ->exists();
   }

   /**
    * Lo que se cobra: con descuento si se aplicó.
    */
   public function getMontoACobrarAttribute()
   {
      return $this->apply_discount == 1 ? $this->montoTotalDescuento : $this->montoTotal;
   }

   /**
    * Texto de ubicación para listas, resumen y tickets.
    */
   public function getUbicacionAttribute()
   {
      if ($this->esDomicilio()) {
         return 'Domicilio';
      }
      if ($this->esParaLlevar()) {
         return 'Para Llevar (Pasan a recoger)' . ($this->nombre_cuenta ? ' - ' . $this->nombre_cuenta : '')
            . ($this->telefono_recoge ? ' (' . $this->telefono_recoge . ')' : '');
      }
      return $this->etiqueta_cuenta ?: 'Sin mesa';
   }

}
