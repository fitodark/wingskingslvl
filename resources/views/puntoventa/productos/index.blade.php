@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">Catalogo de Productos</div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item">


            <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="Toolbar with button groups">
              <div class="btn-group" role="group" aria-label="First group">
                <a href="{{ route('catalogos.create') }}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Agregar</a>
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
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Categoria</th>
                    <th width="280px">Acción</th>
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

                            <a class="btn btn-primary" href="{{ route('catalogos.edit',$product->id) }}">Editar</a>

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
            {!! $products->links() !!}
            </li>
        </ul>
    </div>
</div>
@endsection
