@extends('layouts.app')

@section('content')
<div class="container wk-container">
	<div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
		<p class="h3 text-black mb-2">Comandas con corte al dia: @dateformat($corteCajaCurrent->fechaApertura)</p>
		<form method="POST" action="{{ route('ventastore') }}" class="mb-2">
			@csrf
			<button type="submit" class="btn btn-success btn-lg">
				<i class="fa-solid fa-plus"></i>
				<span>Nueva venta</span>
			</button>
		</form>
	</div>

	<div class="row">
		<div class="col-12 py-2">
			@include('puntoventa.comandas.ventas')
		</div>
	</div>
</div>
@include('puntoventa.comandas.finalizarVenta')
@endsection

@push('scripts')
<script src="{{ asset('js/comandas.js') }}"></script>
@endpush
