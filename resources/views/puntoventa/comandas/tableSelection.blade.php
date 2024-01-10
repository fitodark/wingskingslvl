@extends('layouts.app')

@section('content')
<div class="container example-container">
<form id="tableSelectForm" action="{{ route('ventas.update', $venta->ventaId) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
    <div class="row">
        <div class="col-md-12">
            <p class="h2 text-black">Ubicación</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    <input type="text" hidden name="type" id="type" value="{{ $type }}">
                    <input type="text" hidden name="action" value="{{ $action }}">
                    <input type="text" hidden name="ventaid" value="{{ $venta->ventaId }}">
                    <input type="text" hidden name="clientId" id="clientId">
                    
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="customRadio1" name="customRadio" class="custom-control-input"
                                    data-toggle="collapse" data-target="#collapseOne" aria-controls="collapseOne" checked>
                                    <label class="custom-control-label" for="customRadio1">Consumo Local</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="customRadio2" name="customRadio" class="custom-control-input"
                                    data-toggle="collapse" data-target="#collapseTwo" aria-controls="collapseTwo">
                                    <label class="custom-control-label" for="customRadio2">Consumo Domicilio</label>
                                </div>
                            </li>
                            <li class="list-group-item">
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
                                    <div class="card">
                                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="form-group row">
                                                    <label for="client" class="col-sm-2 col-form-label">Buscar Cliente</label>
                                                    <div class="col-sm-7">
                                                        <input class="typeahead form-control" type="text" id="findClient" placeholder="Buscar Cliente...">
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <button type="button" id="addClient" class="btn btn-outline-success" data-toggle="modal"
                                                            data-target="#addClientModal"
                                                            data-ventaid="{{ $venta->ventaId }}">Agregar Cliente</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </li>
                        </ul>
                </div>

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
@endsection
