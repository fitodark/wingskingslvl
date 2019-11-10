@extends('layouts.app')

@section('content')

  <div class="container-fluid">
      <div class="card">
          <div class="card-header">Comandas</div>

          <div class="card-body" style="background-color: brown;">

{{-- <form method="POST" action="{{ route('addventa') }}">
  @csrf --}}
  <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" data-type="multi" data-interval="false">
      <ol class="carousel-indicators">
          <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
          <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
          <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
          <li data-target="#carouselExampleIndicators" data-slide-to="3"></li>
      </ol>
      <div class="carousel-inner">
          {{-- <div class="carousel-item {{ ($active == 0) ? 'active' : '' }}">
              @include('puntoventa.comandas.ventas')
          </div> --}}
          <div class="carousel-item {{ ($active == 0) ? 'active' : '' }}">
              @include('puntoventa.comandas.tableSelection')
          </div>
          <div class="carousel-item {{ ($active == 1) ? 'active' : '' }}">
              @include('puntoventa.comandas.drinksTab')
          </div>
          <div class="carousel-item {{ ($active == 2) ? 'active' : '' }}">
              @include('puntoventa.comandas.foodsTab')
          </div>
          <div class="carousel-item {{ ($active == 3) ? 'active' : '' }}">
              <h1>Resumen</h1>
              <div class="mx-auto" style="width: 200px;">
              <button type="submit" class="btn btn-primary">Agregar</button>
              </div>
          </div>
      </div>
      <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev" href="{{ url('/obtener') }}">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
      </a>
  </div>
    </div>
      </div>
        </div>
{{-- </form> --}}
@include('puntoventa.comandas.dialogDetails')
@endsection
