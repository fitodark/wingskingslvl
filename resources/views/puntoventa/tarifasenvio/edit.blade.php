@extends('layouts.app')

@section('content')
<div class="container example-container">
    <div class="wk-encabezado">
        <p class="h2 text-black">Editar colonia</p>
        <div class="wk-encabezado-acciones">
            <a class="btn btn-outline-secondary custom" href="{{ route('tarifasenvio.index') }}" role="button">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Regresar</span>
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('tarifasenvio.update', $tarifa->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('puntoventa.tarifasenvio.form')
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
