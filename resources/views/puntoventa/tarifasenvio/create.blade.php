@extends('layouts.app')

@section('content')
<div class="container example-container">
    <div class="wk-encabezado">
        <p class="h2 text-black">Agregar colonia</p>
        <div class="wk-encabezado-acciones">
            <a class="btn btn-outline-secondary custom" href="{{ route('tarifasenvio.index') }}" role="button">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Regresar</span>
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('tarifasenvio.store') }}" method="POST">
                @csrf
                @include('puntoventa.tarifasenvio.form')
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
