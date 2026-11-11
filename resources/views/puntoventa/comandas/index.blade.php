@extends('layouts.app')

@section('content')
<div class="container wk-container">
	<div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
		<p class="h3 text-black mb-2">Comandas con corte al dia: @dateformat($corteCajaCurrent->fechaApertura)</p>
		<div class="d-flex align-items-center mb-2">
			{{-- El navegador no deja sonar nada hasta que se toca la pantalla; este botón lo habilita. --}}
			<button type="button" id="wkActivarSonido" class="btn btn-outline-warning btn-lg mr-2" style="display: none"
				title="Activar el sonido de pedidos nuevos de WhatsApp">
				<i class="fa-solid fa-volume-xmark"></i>
				<span>Activar sonido</span>
			</button>
			<form method="POST" action="{{ route('ventastore') }}">
				@csrf
				<button type="submit" class="btn btn-success btn-lg">
					<i class="fa-solid fa-plus"></i>
					<span>Nueva venta</span>
				</button>
			</form>
		</div>
	</div>

	<div class="row">
		{{-- public/js/bandeja.js reemplaza este contenido cuando cambian las ventas abiertas. --}}
		<div class="col-12 py-2" id="wkBandeja">
			@include('puntoventa.comandas.ventas')
		</div>
	</div>
</div>
{{-- Avisos de pedidos nuevos de WhatsApp --}}
<div id="wkAvisos" class="wk-avisos" aria-live="polite"></div>
@include('puntoventa.comandas.finalizarVenta')
@endsection

@push('scripts')
<script>
	window.WK.bandeja = {
		url: "{{ route('bandeja') }}",
		firma: "{{ $bandejaFirma }}",
		maxFolio: {{ (int) $bandejaMaxFolio }},
		intervalo: {{ max(2, (int) config('services.bandeja.intervalo')) }},
		websockets: {{ config('services.bandeja.websockets') ? 'true' : 'false' }},
		intervaloRespaldo: {{ max(5, (int) config('services.bandeja.intervalo_respaldo')) }}
	};
</script>
<script src="{{ asset('js/comandas.js') }}"></script>
<script src="{{ asset('js/bandeja.js') }}"></script>
@endpush
