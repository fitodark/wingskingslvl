@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">Catalogo de Clientes</div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item">


            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="Toolbar with button groups">
              <div class="btn-group" role="group" aria-label="First group">
                <a href="{{ route('clientes.create') }}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Agregar</a>
              </div>
            </div>

            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
            @endif
            </li>

            <li class="list-group-item">
            <table class="table table-striped">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Telefono</th>
                    <th>Referencia</th>
                    <th width="280px">Acción</th>
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

                            <a class="btn btn-primary" href="{{ route('clientes.edit',$client->id) }}">Editar</a>

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
            {!! $clients->links() !!}
            </li>
        </ul>
    </div>
</div>
@endsection
