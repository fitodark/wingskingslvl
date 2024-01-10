@extends('layouts.app')

@section('content')
<div class="container example-container">
	
	<div class="row">
		<div class="col-md-10">
			<p class="h2 text-black">Catálogo de Clientes</p>
		</div>
		<div class="col-md-2">
            <a href="{{ route('clientes.create') }}" class="btn btn-outline-success btn-add-new" role="button">
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
                    <th width="20%">Nombre</th>
                    <th width="25%">Dirección</th>
                    <th width="10%">Telefono</th>
                    <th width="25%">Referencia</th>
                    <th width="15%">Acción</th>
                </tr>
                @foreach ($clients as $client)
                <tr>
                    <td>{{ $client->id }}</td>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->address }}</td>
                    <td>{{ $client->phone }}</td>
                    <td>{{ $client->reference }}</td>
                    <td>
                        <form action="{{ route('clientes.destroy',$client->id) }}" method="POST">

                            {{-- <a class="btn btn-info" href="{{ route('clientes.show',$client->id) }}">Detalles</a> --}}

                            <!-- <a class="ico-add" href="{{ route('clientes.edit',$client->id) }}"></a> -->
                            <a href="{{ route('clientes.edit',$client->id) }}">
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
            {!! $clients->links() !!}
            
        </div>
    </div>
</div>
@endsection
