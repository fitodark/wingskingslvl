@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header"><h3>Resumen de Ventas</h3></div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
              <form action="{{ route('findSales') }}" method="POST">
                  @csrf
                  <div class="form-group row">
                      <label for="findDate" class="col-sm-1 col-form-label">Busqueda: </label>
                      <div class="col-sm-3">
                          <div class="input-group">
                                <input type="text" class="form-control datepicker" name="date">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-th"></span>
                                </div>
                            </div>
                      </div>
                      <div class="col-sm-5">
                          <button type="submit" class="btn btn-primary mb-2">Buscar</button>
                      </div>
                      <label for="total" class="col-sm-1 col-form-label">TOTAL: </label>
                      <div class="col-sm-2">
                          @isset($total)
                              <input type="text" readonly class="form-control" id="total" value="@money($total)">
                          @endisset
                      </div>
                  </div>
              </form>
            </li>
            <li class="list-group-item">
              @include('puntoventa.comandas.ventas')
            </li>
        </ul>
    </div>
</div>
@endsection
