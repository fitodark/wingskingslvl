@extends('layouts.app')

@section('content')
<div class="container-fluid">
{{-- <form action="{{ route('addCliente', $venta->ventaId) }}" method="POST" enctype="multipart/form-data"> --}}
<form id="tableSelectForm" action="{{ route('ventas.update', $venta->ventaId) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
{{-- @method('GET') --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Seleccionar Ubicación</div>

                <input type="text" hidden name="type" id="type" value="{{ $type }}">
                <input type="text" hidden name="action" value="{{ $action }}">
                <input type="text" hidden name="ventaid" value="{{ $venta->ventaId }}">
                <input type="text" hidden name="clientId" id="clientId">
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <label for="exampleFormControlSelect2">Ubicación:</label>
                            {{-- <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Local</button>
                            <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">Domicilio</button> --}}

                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio1" name="customRadio" class="custom-control-input"
                                data-toggle="collapse" data-target="#collapseOne" aria-controls="collapseOne" checked>
                                <label class="custom-control-label" for="customRadio1">Local</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio2" name="customRadio" class="custom-control-input"
                                data-toggle="collapse" data-target="#collapseTwo" aria-controls="collapseTwo">
                                <label class="custom-control-label" for="customRadio2">Domicilio</label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="accordion" id="accordionExample">
                                <div class="card">
                                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <strong>Mesa:</strong>
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
                                <div class="card">
                                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                        <div class="card-body">
                                            <div class="form-row">
                                                <div class="form-group col-md-12">
                                                    <label for="client">Buscar Cliente</label>
                                                    {{-- <input id="client" name="client" type="text" class="form-control" aria-describedby="emailHelp" placeholder="Nombre del cliente">
                                                    <div id="clientList"></div> --}}
                                                    <input class="typeahead form-control" type="text" id="findClient" placeholder="Buscar Cliente...">
                                                    <br>
                                                    <div class="mx-auto" style="width: 200px;">
                                                        <button type="button" id="addClient" class="btn btn-info" data-toggle="modal"
                                                        data-target="#addClientModal"
                                                        data-ventaid="{{ $venta->ventaId }}">Agregar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </li>
                    </ul>
                </div>

                <div class="card-footer">
                    <div class="mx-auto" style="width: 200px;">
                        <nav aria-label="Page navigation example">
                          <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link disabled" href="#" aria-label="Previous">
                                  <span aria-hidden="true">&laquo;</span>
                                  <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li class="page-item">
                                {{-- <a class="btn btn-warning" href="#" role="button">Cancelar</a> --}}
                                <a class="btn btn-warning" href="{{ route('cancelarVenta') }}" role="button">Cancelar</a>
                            </li>
                            <li class="page-item">
                                <button type="submit" class="btn btn-primary" name="next">Siguiente</button>
                                {{-- <a class="btn btn-primary" role="button"
                                href="{{ route('drinksTab',
                                  [$venta['ventaId'], 1, $table, $arrayClient->name, $arrayClient->phone, $arrayClient->address]) }}"
                                >Siguiente</a> --}}
                            </li>
                          </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
@include('puntoventa.comandas.addClientModal')
@endsection
