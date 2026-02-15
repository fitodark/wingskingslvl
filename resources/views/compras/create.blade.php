@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Agregar Compra</p>
        </div>
		<div class="col-md-2 ">
            <a class="btn btn-outline-secondary custom" href="{{ route('compras') }}" role="button">
				<i class="fa-sharp fa-solid fa-plus"></i>
				<span>Regresar</span>
			</a>
		</div>
	</div>

    <div class="row">
		<div class="col-md-12">
            <form action="{{ route('compras.update', [$compra->idCompra]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="text" hidden name="idCompra" id="idCompra" value="{{ $compra->idCompra }}">
                @include('compras.bodyform')
                @include('compras.bodytableform')
                @if ($compra->status == false)
                    <div class="row">
                        <div class="form-group col-md-4"></div>
                        <div class="form-group col-md-4">
                            <button type="submit" class="btn btn-primary custom">Finalizar</button>
                        </div>
                        <div class="form-group col-md-4"></div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
