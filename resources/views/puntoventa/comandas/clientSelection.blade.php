@extends('layouts.app')

@section('content')
<div class="container example-container">
<form id="tableSelectForm" action="{{ route('update', ['venta' => $venta->ventaId, 'client' => !empty($client)? $client->id:''] ) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
    <div class="row">
        <div class="col-md-8">
            <p class="h2 text-black">Información del Cliente:</p>
        </div>
        <div class="col-md-2">
            <button type="button" id="addClient" class="btn btn-outline-success" data-toggle="modal"
                data-target="#addClientModal"
                data-ventaid="{{ $venta->ventaId }}">Agregar Cliente</button>
		</div>
        <div class="col-md-2">
            <button type="button" id="editClient" class="btn btn-outline-primary" data-toggle="modal"
                data-target="#editClientModal"
                data-ventaid="{{ $venta->ventaId }}">Editar Cliente</button>
		</div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <input class="typeahead form-control form-control-lg" type="text" id="findClient" placeholder="Buscar Cliente...">
        </div>
        <div class="col-md-6">
            <button type="button" id="cleanClient" class="btn btn-outline-primary" 
                data-target="#cleanClientInfo">Limpiar</button>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col">
            <input type="text" hidden name="clientId" id="clientId" value="{{ !empty($client)? $client->id:'' }}">
            <label for="clientName" class="form-label">Nombre:</label>
            <input name="clientName" id="clientName" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="{{ !empty($client)? $client->name:'' }}">

            <label for="clientPhone" class="form-label">Telefono:</label>
            <input name="clientPhone" id="clientPhone" class="form-control" type="text" aria-label="Disabled input example" disabled
                value="{{ !empty($client)? $client->phone:'' }}">
        </div>
        <div class="col">
            <label for="clientAddress" class="form-label">Dirección:</label>
            <input name="clientAddress" id="clientAddress" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="{{ !empty($client)? $client->address:'' }}">

            <label for="clientReference" class="form-label">Referencia:</label>
            <input name="clientReference" id="clientReference" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="{{ !empty($client)? $client->reference:'' }}">
        </div>
    </div>
    <div class="row">
        <div class="col">
            <input type="text" hidden name="discountPercentage" value="{{ !empty($discountPercentage)? $discountPercentage:0 }}">
            <label for="promotion" class="form-label">Aplica promoción:</label>
            <input name="promotion" id="promotion" class="form-control" type="text" aria-label="Disabled input example" disabled
            value="@discount((!empty($discountPercentage)? $discountPercentage:0))">
        </div>
        <div class="col">
            <label for="clientSalesDetails">Historial</label><br>
            <button type="button" id="clientSalesDetails" class="btn btn-outline-primary" data-toggle="modal"
                data-target="#clientSalesDetailsModal"
                data-clientId="{{ !empty($client)? $client->id:'' }}"
                data-sales="">Ver detalle</button>
            @include('puntoventa.comandas.salesdetailmodal.clientSalesDetailsModal')
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-10">
            <p class="h2 text-black">Ubicación</p>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <input type="text" hidden name="type" id="type" value="{{ !empty($venta)? $venta->type:'' }}">
                    <input type="text" hidden name="action" value="{{ $action }}">
                    <input type="text" hidden name="ventaid" value="{{ $venta->ventaId }}">
                    <input type="text" hidden name="client" id="client" value="{{ !empty($client)? $client:'' }}">

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio1" name="customRadio" class="custom-control-input"
                                data-toggle="collapse" data-target="#collapseOne" aria-controls="collapseOne" checked>
                                <label class="custom-control-label" for="customRadio1">Local</label>
                            </div>
                            <div class="accordion" id="accordionExample">
                                <div class="card">
                                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                        <div class="card-body">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Seleccionar Mesa</label>
                                                <div class="col-sm-7">
                                                    <select class="custom-select" name="table" id="table" value="{{ $table->id }}">
                                                        @foreach ($dinerstable as $value)
                                                        <option disable="true" value="{{ $value->id }}"
                                                            {{ ($value->id == $table->id) ? 'selected' : '' }}>{{$value->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            
                        </li>
                        <li class="list-group-item">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio2" name="customRadio" class="custom-control-input"
                                data-toggle="collapse" data-target="#collapseTwo" aria-controls="collapseTwo">
                                <label class="custom-control-label" for="customRadio2">Domicilio</label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio3" name="customRadio" class="custom-control-input"
                                data-toggle="collapse" data-target="#collapseTree" aria-controls="collapseTree">
                                <label class="custom-control-label" for="customRadio3">Para Llevar (Pasan a recoger)</label>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-footer text-center">
                    <div class="row justify-content-center">
                        <div class="col-4"></div>
                        <div class="col-1 justify-content-center">
                            <a class="page-link disabled" href="#" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="sr-only">Previous</span>
                            </a>
                        </div>
                        <div class="col-2 justify-content-center">
                            <a class="btn btn-outline-secondary custom" href="{{ route('cancelarVenta') }}" role="button">Cancelar</a>
                        </div>
                        <div class="col-2 justify-content-center">
                            <button type="submit" class="btn btn-primary custom" name="next">Siguiente</button>
                        </div>
                        <div class="col-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
</div>

@include('puntoventa.comandas.addClientModal')
@include('puntoventa.comandas.editClientModal')
@endsection
