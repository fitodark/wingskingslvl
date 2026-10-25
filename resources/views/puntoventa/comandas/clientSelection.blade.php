@extends('layouts.app')

@section('content')
@php
    // 0 = todavía sin elegir (no hay tipo preseleccionado en una venta nueva).
    $tipo = (int) old('type', $tipoElegido);
    $mesaSeleccionada = (string) old('table', $venta->dinerstable_id);
    // "Sin mesa" solo aparece marcada si así se guardó (o se envió); en una venta nueva no hay nada marcado.
    $sinMesaMarcada = old('table') !== null ? old('table') === '' : ($tipoElegido == 1 && !$venta->dinerstable_id);
    $cuenta = old('nombre_cuenta', $venta->nombre_cuenta);
    // El cliente (registro = dirección) solo aplica en domicilio y para llevar.
    $clienteActual = ($tipo != 1 && !empty($client)) ? $client->only(['id', 'name', 'phone', 'address', 'reference']) : null;
    $tipos = [
        1 => ['icono' => 'fa-utensils', 'nombre' => 'Mesa', 'detalle' => 'Consumen en el local'],
        2 => ['icono' => 'fa-motorcycle', 'nombre' => 'Domicilio', 'detalle' => 'Se envía al cliente'],
        3 => ['icono' => 'fa-bag-shopping', 'nombre' => 'Para llevar', 'detalle' => 'Pasan a recoger'],
    ];
@endphp
<div class="container wk-container wk-paso1">
<form id="tableSelectForm" class="wk-paso1-form" action="{{ route('update', ['venta' => $venta->ventaId]) }}" method="POST" novalidate>
    @csrf
    @method('PUT')
    <input type="hidden" name="action" value="{{ $action }}">
    <input type="hidden" name="ventaid" value="{{ $venta->ventaId }}">
    <input type="hidden" name="discountPercentage" value="{{ !empty($discountPercentage) ? $discountPercentage : 0 }}">
    <input type="hidden" name="clientId" id="ventaClientId" value="{{ $clienteActual['id'] ?? '' }}">

    <div class="wk-encabezado">
        <p class="h2 text-black">Nueva comanda <small class="text-muted h5">Folio {{ $venta->ventaId }}</small></p>
        <ol class="wk-pasos" aria-label="Pasos de la comanda">
            <li class="activo" aria-current="step">1 Ubicación</li>
            <li>2 Bebidas</li>
            <li>3 Alimentos</li>
            <li>4 Resumen</li>
        </ol>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. Para dónde es --}}
    <fieldset class="mb-3">
        <legend class="h5 font-weight-bold">¿Para dónde es?</legend>
        <div class="wk-tipos">
            @foreach ($tipos as $valor => $t)
                <div>
                    <input type="radio" class="wk-opcion-input" name="type" id="tipo{{ $valor }}" value="{{ $valor }}"
                        {{ $tipo == $valor ? 'checked' : '' }}>
                    <label class="wk-tipo" for="tipo{{ $valor }}">
                        <i class="fa-solid {{ $t['icono'] }}" aria-hidden="true"></i>
                        <span><strong>{{ $t['nombre'] }}</strong><small>{{ $t['detalle'] }}</small></span>
                    </label>
                </div>
            @endforeach
        </div>
    </fieldset>

    <div id="wkSinTipo" class="wk-vacio" @if ($tipo) hidden @endif>
        Elige para dónde es la comanda. Solo se pide lo necesario para ese tipo.
    </div>

    {{-- 2a. Mesa --}}
    <div id="panelMesa" class="wk-mesa-layout" @if ($tipo != 1) hidden @endif>
        <fieldset class="wk-panel">
            <div class="d-flex justify-content-between align-items-baseline flex-wrap">
                <legend class="h6 font-weight-bold w-auto mb-2">Mesa</legend>
                <small class="text-muted mb-2">Opcional si escribes el nombre de la cuenta</small>
            </div>
            <div class="wk-mesas">
                <div>
                    <input type="radio" class="wk-opcion-input" name="table" id="mesa0" value=""
                        data-nombre="Sin mesa" data-cuentas="[]" {{ $sinMesaMarcada ? 'checked' : '' }}>
                    <label class="wk-mesa" for="mesa0">Sin mesa<small>&nbsp;</small></label>
                </div>
                @foreach ($dinerstable as $mesa)
                    @php $abiertas = $cuentasPorMesa[$mesa->id] ?? []; @endphp
                    <div>
                        <input type="radio" class="wk-opcion-input" name="table" id="mesa{{ $mesa->id }}" value="{{ $mesa->id }}"
                            data-nombre="{{ $mesa->name }}" data-cuentas="{{ json_encode($abiertas) }}"
                            {{ $mesaSeleccionada === (string) $mesa->id ? 'checked' : '' }}>
                        <label class="wk-mesa" for="mesa{{ $mesa->id }}">{{ $mesa->name }}
                            <small>{{ count($abiertas) ? count($abiertas) . (count($abiertas) == 1 ? ' cuenta abierta' : ' cuentas abiertas') : "\u{00a0}" }}</small>
                        </label>
                    </div>
                @endforeach
            </div>
        </fieldset>
        <div class="wk-panel">
            <label for="cuentaMesa" class="h6 font-weight-bold">Nombre de la cuenta</label>
            <input type="text" class="form-control form-control-lg" name="nombre_cuenta" id="cuentaMesa"
                maxlength="60" placeholder="Ej. Cuenta de Patricia" autocomplete="off"
                value="{{ $tipo == 1 ? $cuenta : '' }}" @if ($tipo != 1) disabled @endif>
            <p class="text-muted small mb-0 mt-2">Para separar cuentas en la misma mesa, o abrir una cuenta sin mesa.</p>
            <div id="avisoMesa" class="alert alert-warning small mt-2 mb-0" role="status" hidden></div>
        </div>
    </div>

    {{-- 2b. Para llevar --}}
    <div id="panelLlevar" class="wk-panel mb-3" @if ($tipo != 3) hidden @endif>
        <label for="cuentaLlevar" class="h6 font-weight-bold">¿Quién recoge?</label>
        <input type="text" class="form-control form-control-lg" name="nombre_cuenta" id="cuentaLlevar"
            maxlength="60" placeholder="Nombre para llamarle al entregar" autocomplete="off"
            value="{{ $tipo == 3 ? $cuenta : '' }}" @if ($tipo != 3) disabled @endif>
        <button type="button" id="abrirBuscarCliente" class="btn btn-link px-0 mt-1">O busca un cliente registrado</button>
    </div>

    {{-- 2c. Cliente: domicilio (obligatorio) y para llevar (opcional). Cada registro es una dirección. --}}
    <div id="panelCliente" class="wk-panel" data-cliente="{{ json_encode($clienteActual) }}"
        @if (!($tipo == 2 || ($tipo == 3 && $clienteActual))) hidden @endif>
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
            <label for="buscarCliente" id="tituloCliente" class="h6 font-weight-bold mb-0">Cliente</label>
            <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#addClientModal"
                data-ventaid="{{ $venta->ventaId }}">
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Cliente nuevo
            </button>
        </div>

        <div id="buscador">
            <div class="wk-buscar">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" id="buscarCliente" class="form-control form-control-lg" placeholder="Teléfono o nombre"
                    autocomplete="off" aria-describedby="buscarAyuda" aria-controls="resultadosCliente">
            </div>
            <p id="buscarAyuda" class="text-muted small mt-2 mb-0">Escribe al menos 2 letras o números.</p>
            <div id="resultadosCliente" class="list-group wk-resultados mt-2" aria-live="polite"></div>
        </div>

        <div id="fichaCliente" class="wk-ficha" hidden>
            <div class="wk-ficha-datos">
                <strong id="fichaNombre" class="d-block h5 mb-1"></strong>
                <span id="fichaTelefono" class="d-block"></span>
                <span class="d-block"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <span id="fichaDireccion"></span></span>
                <span class="d-block text-muted small">Referencia: <span id="fichaReferencia"></span></span>
                @if (!empty($venta->direccion_envio))
                    <span class="d-block mt-2"><i class="fa-solid fa-comment" aria-hidden="true"></i>
                        Dirección de envío capturada por WhatsApp: <strong>{{ $venta->direccion_envio }}</strong></span>
                @endif
            </div>
            <div class="wk-ficha-acciones">
                <button type="button" id="cambiarCliente" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i> Cambiar</button>
                <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#editClientModal"
                    data-ventaid="{{ $venta->ventaId }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button>
                <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#clientSalesDetailsModal">
                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i> Historial</button>
                <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#addClientModal"
                    data-ventaid="{{ $venta->ventaId }}" data-mismo-telefono="1">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Nueva dirección</button>
            </div>
        </div>

        <div id="otrasDirecciones" class="mt-3" hidden>
            <p class="h6 font-weight-bold mb-2">Otras direcciones de este teléfono</p>
            <div id="listaDirecciones" class="list-group wk-resultados"></div>
        </div>
    </div>

    {{-- Barra fija: resumen y Siguiente siempre a la vista --}}
    <div class="wk-paso1-barra">
        <button type="submit" form="cancelarVentaForm" class="btn btn-outline-secondary btn-lg">Cancelar</button>
        <div class="wk-resumen" aria-live="polite">
            <strong id="resumenPaso1">Sin elegir</strong>
            <small id="ayudaPaso1" class="text-muted">Elige Mesa, Domicilio o Para llevar</small>
        </div>
        <button type="submit" id="btnSiguiente" class="btn btn-primary btn-lg" name="next">
            Siguiente <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </button>
    </div>
</form>
{{-- Fuera del form principal (no se pueden anidar forms); el botón Cancelar lo envía con form="..." --}}
<form id="cancelarVentaForm" method="POST" action="{{ route('cancelarVenta', $venta['ventaId']) }}">
    @csrf
</form>
@include('puntoventa.comandas.salesdetailmodal.clientSalesDetailsModal')
</div>

@include('puntoventa.comandas.addClientModal')
@include('puntoventa.comandas.editClientModal')
@endsection

@push('scripts')
<script src="{{ asset('js/comandas.js') }}"></script>
@endpush
