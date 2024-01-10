@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Catálogo de Productos</p>
		</div>
		<div class="col-md-2">
            <a href="{{ route('catalogos.create') }}" class="btn btn-outline-success btn-add-new" role="button">
                <i class="fa-sharp fa-solid fa-plus"></i>
				<span>Agregar</span>
            </a>
		</div>
	</div>

    <div class="row">
		<div class="col-md-12 py-3">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
            @endif
        </div>
    </div>
    <div class="row">
		<div class="col-md-12">
            
            <table class="table table-striped table-sm">
                <tr>
                    <th width="5%">#</th>
                    <th width="30%">Nombre</th>
                    <th width="30%">Descripción</th>
                    <th width="10%">Precio</th>
                    <th width="15%">Categoria</th>
                    <th width="20%">Acción</th>
                </tr>
                @foreach ($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->detail }}</td>
                    <td>@money($product->price)</td>
                    <td>@categoriaType($product->type)</td>
                    <td>
                        <form action="{{ route('catalogos.destroy',$product->id) }}" method="POST">

                            {{-- <a class="btn btn-info" href="{{ route('catalogos.show',$product->id) }}">Detalles</a> --}}

                            <!-- <a class="ico-add" href="{{ route('catalogos.edit',$product->id) }}"></a> -->
                            <a href="{{ route('catalogos.edit',$product->id) }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn">
                                <i class="ico-delete"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
            {!! $products->links() !!}
            
        </div>
    </div>
</div>
@endsection
