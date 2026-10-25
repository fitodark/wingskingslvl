@extends('layouts.app')

@section('content')
<div class="container wk-container">
<form id="tableSelectForm" action="{{ route('update', ['venta' => $venta->ventaId, 'client' => !empty($client)? $client->id:''] ) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <p class="h2 text-black mb-2">Información del Cliente:</p>
        <div class="mb-2">
            <button type="button" id="addClient" class="btn btn-outline-success" data-toggle="modal"
                data-target="#addClientModal"
                data-ventaid="{{ $venta->ventaId }}">
                <i class="fa-solid fa-user-plus"></i> Agregar Cliente</button>
            <button type="button" id="editClient" class="btn btn-outline-primary" data-toggle="modal"
                data-target="#editClientModal"
                data-ventaid="{{ $venta->ventaId }}">
                <i class="fa-solid fa-user-pen"></i> Editar Cliente</button>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-9 col-md-8">
            <input class="typeahead form-control form-control-lg" type="text" id="findClient" placeholder="Buscar Cliente..." autocomplete="off">
        </div>
        <div class="col-3 col-md-4">
            <button type="button" id="cleanClient" class="btn btn-outline-primary btn-lg">Limpiar</button>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <input type="text" hidden name="clientId" id="clientId" value="{{ !empty($client)? $client->id:'' }}">
            <label for="clientName" class="form-label">Nombre:</label>
            <input name="clientName" id="clientName" class="form-control" type="text" disabled
            value="{{ !empty($client)? $client->name:'' }}">

            <label for="clientPhone" class="form-label">Telefono:</label>
            <input name="clientPhone" id="clientPhone" class="form-control" type="text" disabled
                value="{{ !empty($client)? $client->phone:'' }}">
        </div>
        <div class="col-md-6">
            <label for="clientAddress" class="form-label">Dirección:</label>
            <input name="clientAddress" id="clientAddress" class="form-control" type="text" disabled
            value="{{ !empty($client)? $client->address:'' }}">

            <label for="clientReference" class="form-label">Referencia:</label>
            <input name="clientReference" id="clientReference" class="form-control" type="text" disabled
                value="{{ !empty($client)? $client->reference:'' }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-6">
            <input type="text" hidden name="discountPercentage" value="{{ !empty($discountPercentage)? $discountPercentage:0 }}">
            <label for="promotion" class="form-label">Aplica promoción:</label>
            <input name="promotion" id="promotion" class="form-control" type="text" disabled
            value="@discount((!empty($discountPercentage)? $discountPercentage:0))">
        </div>
        <div class="col-6">
            <label for="clientSalesDetails">Historial</label><br>
            <button type="button" id="clientSalesDetails" class="btn btn-outline-primary" data-toggle="modal"
                data-target="#clientSalesDetailsModal"
                data-clientId="{{ !empty($client)? $client->id:'' }}"
                data-sales="">Ver detalle</button>
            @include('puntoventa.comandas.salesdetailmodal.clientSalesDetailsModal')
        </div>
    </div>
    <br>
    <p class="h2 text-black">Ubicación</p>

    @php
        $tipoVenta = (int) old('type', !empty($venta->type) ? $venta->type : 1);
        $mesaSeleccionada = old('table', $table->id);
    @endphp
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <input type="text" hidden name="type" id="type" value="{{ $tipoVenta }}">
                    <input type="text" hidden name="action" value="{{ $action }}">
                    <input type="text" hidden name="ventaid" value="{{ $venta->ventaId }}">
                    <input type="text" hidden name="client" id="client" value="{{ !empty($client)? $client:'' }}">

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio1" name="customRadio" class="custom-control-input"
                                    {{ $tipoVenta == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="customRadio1">Local</label>
                            </div>
                            <div id="collapseOne" class="collapse {{ $tipoVenta == 1 ? 'show' : '' }}">
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label" for="table">Mesa</label>
                                        <div class="col-md-7">
                                            <select class="custom-select" name="table" id="table">
                                                <option value="">Sin mesa</option>
                                                @foreach ($dinerstable as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ ($value->id == $mesaSeleccionada) ? 'selected' : '' }}>{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio2" name="customRadio" class="custom-control-input"
                                    {{ $tipoVenta == 2 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="customRadio2">Domicilio</label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio3" name="customRadio" class="custom-control-input"
                                    {{ $tipoVenta == 3 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="customRadio3">Para Llevar (Pasan a recoger)</label>
                            </div>
                        </li>
                        <li class="list-group-item" id="cuentaGroup" style="{{ $tipoVenta == 2 ? 'display:none' : '' }}">
                            <div class="form-group row mb-0">
                                <label class="col-md-3 col-form-label" for="nombre_cuenta">Nombre de la cuenta</label>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" name="nombre_cuenta" id="nombre_cuenta"
                                        maxlength="60" placeholder="Ej. Cuenta de Patricia (opcional)" autocomplete="off"
                                        value="{{ old('nombre_cuenta', $venta->nombre_cuenta) }}">
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-footer">
            <div class="wk-wizard-nav">
                <button type="submit" form="cancelarVentaForm" class="btn btn-outline-secondary custom">Cancelar</button>
                <button type="submit" class="btn btn-primary custom" name="next">Siguiente</button>
            </div>
        </div>
    </div>

</form>
{{-- Fuera del form principal (no se pueden anidar forms); el botón Cancelar lo envía con form="..." --}}
<form id="cancelarVentaForm" method="POST" action="{{ route('cancelarVenta', $venta['ventaId']) }}">
    @csrf
</form>
</div>

@include('puntoventa.comandas.addClientModal')
@include('puntoventa.comandas.editClientModal')
@endsection

@push('scripts')
<script src="{{ asset('vendor/typeahead/bootstrap3-typeahead.js') }}"></script>
<script src="{{ asset('js/comandas.js') }}"></script>
@endpush
