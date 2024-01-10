@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Comandas</p>
		</div>
		<div class="col-md-2 ">
			<a class="btn btn-outline-success btn-add-new" href="{{ route('ventastore') }}" role="button">
				<i class="fa-sharp fa-solid fa-plus"></i>
				<span>Agregar</span>
			</a>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12 py-3">
			@include('puntoventa.comandas.ventas')
		</div>
	</div>

	
</div>
@include('puntoventa.comandas.ventaDetails')
@include('puntoventa.comandas.finalizarVenta')
@endsection
